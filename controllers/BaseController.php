<?php
require_once 'Database.php';

class BaseController {
    protected $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    protected function render($view, $data = []) {
        extract($data);
        ob_start();
        require __DIR__ . '/../views/' . $view . '.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layouts/layout.php';
    }
}