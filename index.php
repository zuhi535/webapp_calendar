<?php
// Hibajelzés bekapcsolása (fejlesztési környezetben)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/controllers/BaseController.php';

$page = $_GET['page'] ?? 'calendar';

switch ($page) {
    case 'projects':
        require_once __DIR__ . '/controllers/ProjectsController.php';
        $controller = new ProjectsController();
        echo $controller->index();
        break;
    case 'project_detail':
        require_once __DIR__ . '/controllers/ProjectDetailController.php';
        $controller = new ProjectDetailController();
        echo $controller->index();
        break;
    case 'note_detail':
        require_once __DIR__ . '/controllers/NoteDetailController.php';
        $controller = new NoteDetailController();
        echo $controller->index();
        break;
    case 'notes':
        require_once __DIR__ . '/controllers/NotesController.php';
        $controller = new NotesController();
        echo $controller->index();
        break;
    case 'calendar':
    default:
        require_once __DIR__ . '/controllers/CalendarController.php';
        $controller = new CalendarController();
        echo $controller->index();
        break;
}