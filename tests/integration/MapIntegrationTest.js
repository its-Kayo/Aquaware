describe('Map Integration Tests', function() {
    let map;
    
    beforeEach(function() {
        // Setup DOM
        document.body.innerHTML = '<div id="map"></div>';
        
        // Mock Google Maps API
        global.google = {
            maps: {
                Map: jest.fn(),
                Marker: jest.fn(),
                InfoWindow: jest.fn()
            }
        };
        
        map = new AquaAwareMap('map', 'test-api-key');
    });
    
    test('should load and display reports on map', async function() {
        // Mock fetch response
        global.fetch = jest.fn().mockResolvedValue({
            json: () => Promise.resolve([
                {
                    report_id: 1,
                    latitude: '17.1175',
                    longitude: '-61.8456',
                    issue_type: 'no_water',
                    severity: 'high',
                    address: 'Test Address'
                }
            ])
        });
        
        await map.loadReports();
        
        expect(fetch).toHaveBeenCalledWith('api/get_reports.php');
        expect(map.markers).toHaveLength(1);
    });
    
    test('should handle offline report submission', function() {
        const offlineManager = new OfflineManager();
        
        // Simulate offline state
        Object.defineProperty(navigator, 'onLine', { value: false });
        
        const reportData = {
            latitude: 17.1175,
            longitude: -61.8456,
            issueType: 'no_water',
            description: 'Test offline report'
        };
        
        const result = offlineManager.saveReportOffline(reportData);
        
        expect(result.success).toBe(true);
        expect(result.offline).toBe(true);
        expect(offlineManager.pendingReports).toHaveLength(1);
    });
});