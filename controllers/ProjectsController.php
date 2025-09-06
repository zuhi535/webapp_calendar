<?php
require_once 'BaseController.php';

class ProjectsController extends BaseController {
    public function index() {
        // Projekt műveletek kezelése
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'add_project':
                        $this->addProject();
                        break;
                    case 'delete_project':
                        $this->deleteProject();
                        break;
                }
                
                // Átirányítás hogy ne maradjon POST adat a böngészőben
                header('Location: ?page=projects');
                exit;
            }
        }
        
        // Projekt törlés GET paraméterrel
        if (isset($_GET['delete_project'])) {
            $this->deleteProject($_GET['delete_project']);
            header('Location: ?page=projects');
            exit;
        }
        
        // Projektek és gyűjtemények lekérése
        $projects = $this->getProjects();
        $recentNotes = $this->getRecentNotes();
        
        return $this->render('projects/index', [
            'title' => 'Projektek',
            'projects' => $projects,
            'recentNotes' => $recentNotes
        ]);
    }
    
    private function addProject() {
        try {
            session_start();
            
            $name = trim($_POST['project_name']);
            $description = trim($_POST['project_description'] ?? '');
            $type = $_POST['project_type'] ?? 'project';
            $color = $_POST['project_color'] ?? '#3498db';
            
            // Validáció
            if (empty($name)) {
                throw new Exception('A projekt neve kötelező!');
            }
            
            $query = "INSERT INTO collections (user_id, name, description, type, color) 
                     VALUES (1, :name, :description, :type, :color)";
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':type' => $type,
                ':color' => $color
            ]);
            
            if ($result) {
                $_SESSION['success_message'] = 'Projekt sikeresen létrehozva!';
            }
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Hiba: ' . $e->getMessage();
            error_log('Project add error: ' . $e->getMessage());
        }
    }
    
    private function deleteProject($projectId = null) {
        try {
            session_start();
            
            $projectId = $projectId ?: $_POST['project_id'];
            
            if (empty($projectId)) {
                throw new Exception('Érvénytelen projekt azonosító!');
            }
            
            // Soft delete - beállítjuk a deleted_at mezőt
            $query = "UPDATE collections SET deleted_at = NOW() WHERE id = :id AND user_id = 1";
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([':id' => $projectId]);
            
            if ($result && $stmt->rowCount() > 0) {
                $_SESSION['success_message'] = 'Projekt sikeresen törölve!';
            } else {
                throw new Exception('Projekt nem található vagy nincs jogosultság a törléshez!');
            }
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Hiba: ' . $e->getMessage();
            error_log('Project delete error: ' . $e->getMessage());
        }
    }
    
    private function getProjects() {
        $query = "SELECT c.*, 
                 COUNT(DISTINCT child.id) as subproject_count,
                 COUNT(DISTINCT n.id) as note_count
                 FROM collections c 
                 LEFT JOIN collections child ON c.id = child.parent_id AND child.deleted_at IS NULL
                 LEFT JOIN notes n ON c.id = n.collection_id AND n.deleted_at IS NULL
                 WHERE c.user_id = 1 
                 AND c.type = 'project' 
                 AND c.parent_id IS NULL 
                 AND c.deleted_at IS NULL 
                 GROUP BY c.id 
                 ORDER BY c.sort_order, c.name";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getRecentNotes() {
        $query = "SELECT n.*, c.name as collection_name, c.color 
                 FROM notes n 
                 JOIN collections c ON n.collection_id = c.id 
                 WHERE n.user_id = 1 
                 AND n.deleted_at IS NULL 
                 ORDER BY n.updated_at DESC 
                 LIMIT 10";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}