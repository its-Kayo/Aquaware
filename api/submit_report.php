<?php
require_once 'config/database.php';
require_once 'auth/auth_check.php';

class ReportController {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    public function submitReport($userId, $latitude, $longitude, $address, $issueType, $description, $photoPath = null) {
        // Check for duplicate reports within 100m radius in last 24 hours
        $duplicateCheck = $this->checkDuplicateReports($latitude, $longitude);
        
        if ($duplicateCheck) {
            return ['success' => false, 'message' => 'Similar report already exists nearby'];
        }
        
        $stmt = $this->db->prepare("INSERT INTO outage_reports (user_id, latitude, longitude, address, issue_type, description, photo_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iddssss", $userId, $latitude, $longitude, $address, $issueType, $description, $photoPath);
        
        if ($stmt->execute()) {
            $reportId = $this->db->insert_id;
            
            // Trigger notifications for nearby users
            $this->notifyNearbyUsers($latitude, $longitude, $issueType);
            
            return ['success' => true, 'report_id' => $reportId];
        }
        
        return ['success' => false, 'message' => 'Failed to submit report'];
    }
    
    private function checkDuplicateReports($lat, $lng) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM outage_reports 
            WHERE (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) < 0.1 
            AND reported_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $stmt->bind_param("ddd", $lat, $lng, $lat);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        return $result['count'] > 0;
    }
}
?>