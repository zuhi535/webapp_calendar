<?php
require_once 'BaseController.php';

class CalendarController extends BaseController {
    public function index() {
        // Esemény műveletek kezelése
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'add_event':
                        $this->addEvent();
                        break;
                    case 'delete_event':
                        $this->deleteEvent();
                        break;
                }
                
                // Átirányítás hogy ne maradjon POST adat a böngészőben
                $redirectParams = $this->getRedirectParams();
                header('Location: ?page=calendar' . $redirectParams);
                exit;
            }
        }
        
        // Esemény törlés GET paraméterrel
        if (isset($_GET['delete_event'])) {
            $this->deleteEvent($_GET['delete_event']);
            $redirectParams = $this->getRedirectParams();
            header('Location: ?page=calendar' . $redirectParams);
            exit;
        }
        
        // Aktuális dátum és lapozási paraméterek
        $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
        $month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
        
        // Naptári események lekérése
        $events = $this->getCalendarEvents($year, $month);
        
        // Naptár adatok előkészítése
        $calendarData = $this->generateCalendarData($year, $month);
        
        return $this->render('calendar/index', [
            'title' => 'Naptár',
            'events' => $events,
            'calendar' => $calendarData,
            'currentYear' => $year,
            'currentMonth' => $month
        ]);
    }
    
    private function addEvent() {
        try {
            $title = trim($_POST['event_title']);
            $date = $_POST['event_date'];
            $startTime = $_POST['event_start_time'] ?? '09:00';
            $endTime = $_POST['event_end_time'] ?? '10:00';
            $allDay = isset($_POST['event_all_day']) ? 1 : 0;
            $color = $_POST['event_color'] ?? '#3498db';
            $description = trim($_POST['event_description'] ?? '');
            
            // Validáció
            if (empty($title)) {
                throw new Exception('Az esemény neve kötelező!');
            }
            
            if (empty($date) || !strtotime($date)) {
                throw new Exception('Érvénytelen dátum!');
            }
            
            $startDatetime = $allDay ? $date . ' 00:00:00' : $date . ' ' . $startTime . ':00';
            $endDatetime = $allDay ? $date . ' 23:59:59' : ($endTime ? $date . ' ' . $endTime . ':00' : null);
            
            $query = "INSERT INTO calendar_events (user_id, title, description, start_datetime, end_datetime, is_all_day, color) 
                     VALUES (1, :title, :description, :start_datetime, :end_datetime, :is_all_day, :color)";
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':start_datetime' => $startDatetime,
                ':end_datetime' => $endDatetime,
                ':is_all_day' => $allDay,
                ':color' => $color
            ]);
            
            if ($result) {
                session_start();
                $_SESSION['success_message'] = 'Esemény sikeresen hozzáadva!';
            }
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Hiba: ' . $e->getMessage();
            error_log('Calendar event error: ' . $e->getMessage());
        }
    }
    
    private function deleteEvent($eventId = null) {
        try {
            $eventId = $eventId ?: $_POST['event_id'];
            
            if (empty($eventId)) {
                throw new Exception('Érvénytelen esemény azonosító!');
            }
            
            // Soft delete - beállítjuk a deleted_at mezőt
            $query = "UPDATE calendar_events SET deleted_at = NOW() WHERE id = :id AND user_id = 1";
            
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([':id' => $eventId]);
            
            if ($result && $stmt->rowCount() > 0) {
                session_start();
                $_SESSION['success_message'] = 'Esemény sikeresen törölve!';
            } else {
                throw new Exception('Esemény nem található vagy nincs jogosultság a törléshez!');
            }
            
        } catch (Exception $e) {
            session_start();
            $_SESSION['error_message'] = 'Hiba: ' . $e->getMessage();
            error_log('Calendar event delete error: ' . $e->getMessage());
        }
    }
    
    private function getRedirectParams() {
        $params = '';
        if (isset($_GET['year'])) {
            $params .= '&year=' . $_GET['year'];
        }
        if (isset($_GET['month'])) {
            $params .= '&month=' . $_GET['month'];
        }
        return $params;
    }
    
    private function getCalendarEvents($year, $month) {
        $firstDay = date('Y-m-01', strtotime("$year-$month-01"));
        $lastDay = date('Y-m-t', strtotime("$year-$month-01"));
        
        $query = "SELECT * FROM calendar_events 
                 WHERE user_id = 1 
                 AND deleted_at IS NULL 
                 AND start_datetime BETWEEN :firstDay AND :lastDay 
                 ORDER BY start_datetime";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute(['firstDay' => $firstDay . ' 00:00:00', 'lastDay' => $lastDay . ' 23:59:59']);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function generateCalendarData($year, $month) {
        $firstDay = mktime(0, 0, 0, $month, 1, $year);
        $daysInMonth = date('t', $firstDay);
        $firstDayOfWeek = date('w', $firstDay);
        
        // Magyar hét kezdete: hétfő (1) helyett vasárnap (0)
        $firstDayOfWeek = $firstDayOfWeek == 0 ? 6 : $firstDayOfWeek - 1;
        
        $weeks = [];
        $currentDay = 1;
        
        for ($i = 0; $i < 6; $i++) {
            $week = [];
            
            for ($j = 0; $j < 7; $j++) {
                if (($i === 0 && $j < $firstDayOfWeek) || $currentDay > $daysInMonth) {
                    $week[] = null; // Üres nap
                } else {
                    $week[] = $currentDay;
                    $currentDay++;
                }
            }
            
            $weeks[] = $week;
            
            if ($currentDay > $daysInMonth) {
                break;
            }
        }
        
        return [
            'weeks' => $weeks,
            'monthName' => $this->getHungarianMonthName($month),
            'year' => $year,
            'month' => $month,
            'prev' => $this->getPrevMonth($year, $month),
            'next' => $this->getNextMonth($year, $month)
        ];
    }
    
    private function getHungarianMonthName($month) {
        $months = [
            1 => 'január', 2 => 'február', 3 => 'március', 4 => 'április',
            5 => 'május', 6 => 'június', 7 => 'július', 8 => 'augusztus',
            9 => 'szeptember', 10 => 'október', 11 => 'november', 12 => 'december'
        ];
        
        return $months[$month] ?? 'ismeretlen';
    }
    
    private function getPrevMonth($year, $month) {
        if ($month == 1) {
            return ['year' => $year - 1, 'month' => 12];
        }
        return ['year' => $year, 'month' => $month - 1];
    }
    
    private function getNextMonth($year, $month) {
        if ($month == 12) {
            return ['year' => $year + 1, 'month' => 1];
        }
        return ['year' => $year, 'month' => $month + 1];
    }
}