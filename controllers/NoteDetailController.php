<?php
require_once 'BaseController.php';

class NoteDetailController extends BaseController {
    public function index() {
        // Jegyzet ID ellenőrzése
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            header('Location: ?page=projects');
            exit;
        }
        
        $noteId = (int)$_GET['id'];
        
        // Jegyzet adatainak lekérése
        $note = $this->getNote($noteId);
        
        if (!$note) {
            header('Location: ?page=projects');
            exit;
        }
        
        // Projekt adatainak lekérése (a breadcrumb-hoz)
        $project = $this->getProject($note['collection_id']);
        
        // Jegyzet fájljainak lekérése
        $files = $this->getNoteFiles($noteId);
        
        return $this->render('notes/detail', [
            'title' => $note['title'] . ' - Jegyzet',
            'note' => $note,
            'project' => $project,
            'files' => $files
        ]);
    }
    
    private function getNote($noteId) {
        $query = "SELECT n.*, c.name as collection_name, c.color as collection_color 
                 FROM notes n 
                 INNER JOIN collections c ON n.collection_id = c.id 
                 WHERE n.id = :id 
                 AND n.user_id = 1 
                 AND n.deleted_at IS NULL";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $noteId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getProject($projectId) {
        $query = "SELECT * FROM collections 
                 WHERE id = :id 
                 AND user_id = 1 
                 AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $projectId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getNoteFiles($noteId) {
        $query = "SELECT * FROM attachments 
                 WHERE note_id = :note_id 
                 ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':note_id' => $noteId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}