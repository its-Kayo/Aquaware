<?php
use PHPUnit\Framework\TestCase;

class ReportControllerTest extends TestCase {
    private $reportController;
    private $mockDatabase;
    
    protected function setUp(): void {
        $this->mockDatabase = $this->createMock(mysqli::class);
        $this->reportController = new ReportController($this->mockDatabase);
    }
    
    public function testSubmitValidReport() {
        $userId = 1;
        $latitude = 17.1175;
        $longitude = -61.8456;
        $address = "St. John's, Antigua";
        $issueType = "no_water";
        $description = "No water since morning";
        
        $result = $this->reportController->submitReport(
            $userId, $latitude, $longitude, $address, $issueType, $description
        );
        
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
    }
    
    public function testDuplicateReportPrevention() {
        // Test that duplicate reports within 100m are prevented
        $userId = 1;
        $latitude = 17.1175;
        $longitude = -61.8456;
        
        // Submit first report
        $result1 = $this->reportController->submitReport($userId, $latitude, $longitude, "Address 1", "no_water", "Description 1");
        
        // Attempt duplicate report
        $result2 = $this->reportController->submitReport($userId, $latitude + 0.0001, $longitude + 0.0001, "Address 2", "no_water", "Description 2");
        
        $this->assertFalse($result2['success']);
        $this->assertStringContains('Similar report already exists', $result2['message']);
    }
}
?>