<?php
require_once 'BaseController.php';

class NotesController extends BaseController {
    public function index() {
        // Jegyzetek és gyűjtemények lekérése
        $query = "SELECT c.id, c.name, c.color, COUNT(n.id) as note_count 
                  FROM collections c 
                  LEFT JOIN notes n ON c.id = n.collection_id 
                  WHERE c.user_id = 1 AND c.type = 'note' AND c.deleted_at IS NULL 
                  GROUP BY c.id 
                  ORDER BY c.sort_order";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $collections = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Legutóbbi jegyzetek
        $query = "SELECT n.*, c.name as collection_name, c.color 
                  FROM notes n 
                  JOIN collections c ON n.collection_id = c.id 
                  WHERE n.user_id = 1 AND n.deleted_at IS NULL 
                  ORDER BY n.updated_at DESC 
                  LIMIT 10";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $recent_notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $this->render('notes/index', [
            'title' => 'Jegyzetek',
            'collections' => $collections,
            'recent_notes' => $recent_notes
        ]);
    }
}