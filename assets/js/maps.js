class AquaAwareMap {
    constructor(containerId, apiKey) {
        this.map = null;
        this.markers = [];
        this.containerId = containerId;
        this.apiKey = apiKey;
        this.init();
    }
    
    init() {
        // Initialize Google Map
        this.map = new google.maps.Map(document.getElementById(this.containerId), {
            zoom: 12,
            center: { lat: 17.1175, lng: -61.8456 }, // Antigua coordinates
            styles: this.getMapStyles()
        });
        
        // Load existing reports
        this.loadReports();
        
        // Set up real-time updates
        setInterval(() => this.loadReports(), 30000); // Update every 30 seconds
        
        // Add click listener for new reports
        this.map.addListener('click', (event) => {
            if (document.getElementById('reportMode').checked) {
                this.showReportDialog(event.latLng);
            }
        });
    }
    
    async loadReports() {
        try {
            const response = await fetch('api/get_reports.php');
            const reports = await response.json();
            
            this.clearMarkers();
            
            reports.forEach(report => {
                this.addReportMarker(report);
            });
        } catch (error) {
            console.error('Error loading reports:', error);
        }
    }
    
    addReportMarker(report) {
        const marker = new google.maps.Marker({
            position: { lat: parseFloat(report.latitude), lng: parseFloat(report.longitude) },
            map: this.map,
            title: report.issue_type.replace('_', ' ').toUpperCase(),
            icon: this.getMarkerIcon(report.issue_type, report.severity)
        });
        
        const infoWindow = new google.maps.InfoWindow({
            content: this.createInfoWindowContent(report)
        });
        
        marker.addListener('click', () => {
            infoWindow.open(this.map, marker);
        });
        
        this.markers.push(marker);
    }
    
    getMarkerIcon(issueType, severity) {
        const colors = {
            'low': '#4CAF50',      // Green
            'medium': '#FF9800',   // Orange  
            'high': '#F44336'      // Red
        };
        
        return {
            url: `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(`
                <svg width="24" height="24" viewBox="0 0 24 24" fill="${colors[severity]}" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L13.09 8.26L18 7L16.74 12.74L21 14L14.74 16.26L16 22L10.26 20.74L9 15L3.26 16.26L2 10L7.26 8.74L6 3L12 2Z"/>
                </svg>
            `)}`,
            scaledSize: new google.maps.Size(24, 24)
        };
    }
    
    createInfoWindowContent(report) {
        return `
            <div class="info-window">
                <h4>${report.issue_type.replace('_', ' ').toUpperCase()}</h4>
                <p><strong>Address:</strong> ${report.address}</p>
                <p><strong>Severity:</strong> ${report.severity}</p>
                <p><strong>Reported:</strong> ${new Date(report.reported_at).toLocaleString()}</p>
                ${report.description ? `<p><strong>Details:</strong> ${report.description}</p>` : ''}
                <p><strong>Status:</strong> <span class="status-${report.status}">${report.status}</span></p>
            </div>
        `;
    }
}

// Initialize map when page loads
document.addEventListener('DOMContentLoaded', function() {
    const map = new AquaAwareMap('map', 'YOUR_GOOGLE_MAPS_API_KEY');
});