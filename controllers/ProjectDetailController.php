<?php
require_once 'BaseController.php';

class ProjectDetailController extends BaseController {
    public function index() {
        // Projekt műveletek kezelése
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action'])) {
                session_start();
                switch ($_POST['action']) {
                    case 'update_project':
                        $this->updateProject();
                        break;
                    case 'add_subproject':
                        $this->addSubproject();
                        break;
                    case 'add_note':
                        $this->addNote();
                        break;
                    case 'delete_note':
                        $this->deleteNote();
                        break;
                    case 'upload_file':
                        $this->uploadFile();
                        break;
                    case 'delete_file':
                        $this->deleteFile();
                        break;
                }
                
                // Átirányítás vissza a projekt oldalra
                if (isset($_POST['project_id'])) {
                    header('Location: ?page=project_detail&id=' . $_POST['project_id']);
                    exit;
                }
            }
        }
        
        // Fájl letöltés kezelése
        if (isset($_GET['download_file'])) {
            $this->downloadFile($_GET['download_file']);
            exit;
        }
        
        // Projekt ID ellenőrzése
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            header('Location: ?page=projects');
            exit;
        }
        
        $projectId = (int)$_GET['id'];
        
        // Projekt adatainak lekérése
        $project = $this->getProject($projectId);
        
        if (!$project) {
            header('Location: ?page=projects');
            exit;
        }
        
        // Projekt jegyzeteinek lekérése
        $notes = $this->getProjectNotes($projectId);
        
        // Projekt almappáinak lekérése
        $subprojects = $this->getSubprojects($projectId);
        
        // Projekt fájljainak lekérése (jelenleg csak jegyzetekhez kapcsolódó fájlok)
        $files = $this->getProjectFiles($projectId);
        
        return $this->render('projects/detail', [
            'title' => $project['name'] . ' - Projekt',
            'project' => $project,
            'notes' => $notes,
            'subprojects' => $subprojects,
            'files' => $files
        ]);
    }
    
    private function getProject($projectId) {
        $query = "SELECT * FROM collections 
                 WHERE id = :id 
                 AND user_id = 1 
                 AND type = 'project' 
                 AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $projectId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getProjectNotes($projectId) {
        $query = "SELECT * FROM notes 
                 WHERE collection_id = :project_id 
                 AND user_id = 1 
                 AND deleted_at IS NULL 
                 ORDER BY is_pinned DESC, updated_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':project_id' => $projectId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getSubprojects($projectId) {
        $query = "SELECT * FROM collections 
                 WHERE parent_id = :parent_id 
                 AND user_id = 1 
                 AND type = 'project' 
                 AND deleted_at IS NULL 
                 ORDER BY sort_order, name";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':parent_id' => $projectId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getProjectFiles($projectId) {
        // Jelenlegi séma szerint - csak a jegyzetekhez tartozó fájlokat jelenítjük meg
        $query = "SELECT a.* FROM attachments a
                 INNER JOIN notes n ON a.note_id = n.id
                 WHERE n.collection_id = :project_id 
                 AND n.deleted_at IS NULL
                 ORDER BY a.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':project_id' => $projectId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function updateProject() {
        try {
            $projectId = (int)$_POST['project_id'];
            $name = trim($_POST['project_name']);
            $description = trim($_POST['project_description'] ?? '');
            $color = $_POST['project_color'] ?? '#3498db';
            
            // Validáció
            if (empty($name)) {
                throw new Exception('A projekt neve kötelező!');
            }
            
            $query = "UPDATE collections 
                     SET name = :name, description = :description, color = :color, updated_at = NOW()
                     WHERE id = :id AND user_id = 1";
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':color' => $color,
                ':id' => $projectId
            ]);
            
            if ($result) {
                $_SESSION['success_message'] = 'Projekt sikeresen frissítve!';
            }
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Hiba: ' . $e->getMessage();
            error_log('Project update error: ' . $e->getMessage());
        }
    }
    
    private function addSubproject() {
        try {
            $parentId = (int)$_POST['project_id'];
            $name = trim($_POST['subproject_name']);
            $description = trim($_POST['subproject_description'] ?? '');
            $color = $_POST['subproject_color'] ?? '#3498db';
            
            // Validáció
            if (empty($name)) {
                throw new Exception('Az almappa neve kötelező!');
            }
            
            $query = "INSERT INTO collections (user_id, parent_id, name, description, type, color) 
                     VALUES (1, :parent_id, :name, :description, 'project', :color)";
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                ':parent_id' => $parentId,
                ':name' => $name,
                ':description' => $description,
                ':color' => $color
            ]);
            
            if ($result) {
                $_SESSION['success_message'] = 'Almappa sikeresen létrehozva!';
            }
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Hiba: ' . $e->getMessage();
            error_log('Subproject add error: ' . $e->getMessage());
        }
    }
    
    private function addNote() {
        try {
            $projectId = (int)$_POST['project_id'];
            $title = trim($_POST['note_title']);
            $content = trim($_POST['note_content'] ?? '');
            
            // Validáció
            if (empty($title)) {
                throw new Exception('A jegyzet címe kötelező!');
            }
            
            // Excerpt generálása
            $excerpt = mb_substr(strip_tags($content), 0, 200);
            
            $query = "INSERT INTO notes (collection_id, user_id, title, content, excerpt) 
                     VALUES (:collection_id, 1, :title, :content, :excerpt)";
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                ':collection_id' => $projectId,
                ':title' => $title,
                ':content' => $content,
                ':excerpt' => $excerpt
            ]);
            
            if ($result) {
                $_SESSION['success_message'] = 'Jegyzet sikeresen létrehozva!';
            }
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Hiba: ' . $e->getMessage();
            error_log('Note add error: ' . $e->getMessage());
        }
    }
    
    private function deleteNote() {
        try {
            $noteId = (int)$_POST['note_id'];
            $projectId = (int)$_POST['project_id'];
            
            if (empty($noteId)) {
                throw new Exception('Érvénytelen jegyzet azonosító!');
            }
            
            // Soft delete - beállítjuk a deleted_at mezőt
            $query = "UPDATE notes SET deleted_at = NOW() WHERE id = :id AND user_id = 1";
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([':id' => $noteId]);
            
            if ($result && $stmt->rowCount() > 0) {
                $_SESSION['success_message'] = 'Jegyzet sikeresen törölve!';
            } else {
                throw new Exception('Jegyzet nem található vagy nincs jogosultság a törléshez!');
            }
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Hiba: ' . $e->getMessage();
            error_log('Note delete error: ' . $e->getMessage());
        }
    }
    
    private function uploadFile() {
        try {
            $projectId = (int)$_POST['project_id'];
            
            if (!isset($_FILES['project_file']) || $_FILES['project_file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Fájlfeltöltési hiba!');
            }
            
            $file = $_FILES['project_file'];
            $originalFilename = basename($file['name']);
            $fileSize = $file['size'];
            $mimeType = $file['type'];
            
            // Biztonságos fájlnév generálása
            $fileExtension = pathinfo($originalFilename, PATHINFO_EXTENSION);
            $safeFilename = uniqid() . '_' . md5($originalFilename) . '.' . $fileExtension;
            $uploadPath = __DIR__ . '/../uploads/' . $safeFilename;
            
            // Upload mappa ellenőrzése
            if (!is_dir(__DIR__ . '/../uploads')) {
                mkdir(__DIR__ . '/../uploads', 0755, true);
            }
            
            // Fájl mozgatása
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                throw new Exception('A fájl mentése sikertelen!');
            }
            
            // Adatbázisba mentés - MÓDOSÍTOTT: most már csak note_id-t tárolunk
            $query = "INSERT INTO attachments (note_id, filename, original_filename, mime_type, file_size, file_path, is_image) 
                     VALUES (:note_id, :filename, :original_filename, :mime_type, :file_size, :file_path, :is_image)";
            
            $isImage = strpos($mimeType, 'image/') === 0;
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                ':note_id' => null, // Ha nem jegyzethez kapcsolódik
                ':filename' => $safeFilename,
                ':original_filename' => $originalFilename,
                ':mime_type' => $mimeType,
                ':file_size' => $fileSize,
                ':file_path' => '/uploads/' . $safeFilename,
                ':is_image' => $isImage ? 1 : 0
            ]);
            
            if ($result) {
                $_SESSION['success_message'] = 'Fájl sikeresen feltöltve!';
            }
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Hiba: ' . $e->getMessage();
            error_log('File upload error: ' . $e->getMessage());
        }
    }
    
    private function deleteFile() {
        try {
            $fileId = (int)$_POST['file_id'];
            $filePath = $_POST['file_path'] ?? '';
            
            if (empty($fileId)) {
                throw new Exception('Érvénytelen fájl azonosító!');
            }
            
            // Fájl törlése a szerverről
            if (!empty($filePath) && file_exists(__DIR__ . '/..' . $filePath)) {
                unlink(__DIR__ . '/..' . $filePath);
            }
            
            // Adatbázisból törlés
            $query = "DELETE FROM attachments WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([':id' => $fileId]);
            
            if ($result && $stmt->rowCount() > 0) {
                $_SESSION['success_message'] = 'Fájl sikeresen törölve!';
            } else {
                throw new Exception('Fájl nem található!');
            }
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Hiba: ' . $e->getMessage();
            error_log('File delete error: ' . $e->getMessage());
        }
    }
    
    private function downloadFile($fileId) {
        try {
            $fileId = (int)$fileId;
            
            $query = "SELECT * FROM attachments WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $fileId]);
            $file = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$file) {
                throw new Exception('Fájl nem található!');
            }
            
            $filePath = __DIR__ . '/..' . $file['file_path'];
            
            if (!file_exists($filePath)) {
                throw new Exception('A fájl nem érhető el!');
            }
            
            // Fájl letöltés header-ek
            header('Content-Description: File Transfer');
            header('Content-Type: ' . $file['mime_type']);
            header('Content-Disposition: attachment; filename="' . $file['original_filename'] . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            
            readfile($filePath);
            exit;
            
        } catch (Exception $e) {
            error_log('File download error: ' . $e->getMessage());
            header('Location: ?page=project_detail&id=' . $_GET['id'] . '&error=' . urlencode($e->getMessage()));
            exit;
        }
    }
}