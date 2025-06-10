<?php
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;

class EndToEndTest extends PHPUnit\Framework\TestCase {
    private $driver;
    
    protected function setUp(): void {
        $this->driver = RemoteWebDriver::create('http://localhost:4444/wd/hub', [
            'browserName' => 'chrome'
        ]);
    }
    
    public function testCompleteReportingWorkflow() {
        // Navigate to application
        $this->driver->get('http://localhost/aquaware');
        
        // Login
        $this->driver->findElement(WebDriverBy::id('loginBtn'))->click();
        $this->driver->findElement(WebDriverBy::name('username'))->sendKeys('testuser');
        $this->driver->findElement(WebDriverBy::name('password'))->sendKeys('password123');
        $this->driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
        
        // Navigate to report form
        $this->driver->findElement(WebDriverBy::linkText('Report Issue'))->click();
        
        // Fill out report form
        $this->driver->findElement(WebDriverBy::id('issueType'))->sendKeys('no_water');
        $this->driver->findElement(WebDriverBy::id('location'))->sendKeys('St. John\'s, Antigua');
        $this->driver->findElement(WebDriverBy::id('description'))->sendKeys('No water since morning');
        
        // Submit report
        $this->driver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();
        
        // Verify success message
        $successMessage = $this->driver->findElement(WebDriverBy::className('success-message'));
        $this->assertStringContains('Report submitted successfully', $successMessage->getText());
        
        // Verify report appears on map
        $this->driver->findElement(WebDriverBy::linkText('Map'))->click();
        
        // Wait for map to load and check for marker
        $this->driver->wait(10)->until(function($driver) {
            return count($driver->findElements(WebDriverBy::className('map-marker'))) > 0;
        });
        
        $markers = $this->driver->findElements(WebDriverBy::className('map-marker'));
        $this->assertGreaterThan(0, count($markers));
    }
    
    protected function tearDown(): void {
        $this->driver->quit();
    }
}
?>