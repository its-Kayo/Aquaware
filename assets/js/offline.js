class OfflineManager {
    constructor() {
        this.isOnline = navigator.onLine;
        this.pendingReports = JSON.parse(localStorage.getItem('pendingReports')) || [];
        this.setupEventListeners();
    }
    
    setupEventListeners() {
        window.addEventListener('online', () => {
            this.isOnline = true;
            this.syncPendingReports();
            this.showNotification('Connection restored. Syncing data...', 'success');
        });
        
        window.addEventListener('offline', () => {
            this.isOnline = false;
            this.showNotification('You are offline. Reports will be saved locally.', 'warning');
        });
    }
    
    saveReportOffline(reportData) {
        reportData.timestamp = Date.now();
        reportData.id = 'offline_' + reportData.timestamp;
        
        this.pendingReports.push(reportData);
        localStorage.setItem('pendingReports', JSON.stringify(this.pendingReports));
        
        this.showNotification('Report saved offline. Will sync when connection is restored.', 'info');
        return { success: true, offline: true, id: reportData.id };
    }
    
    async syncPendingReports() {
        if (this.pendingReports.length === 0) return;
        
        const synced = [];
        
        for (const report of this.pendingReports) {
            try {
                const response = await fetch('api/submit_report.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(report)
                });
                
                if (response.ok) {
                    synced.push(report.id);
                }
            } catch (error) {
                console.error('Failed to sync report:', error);
            }
        }
        
        // Remove synced reports
        this.pendingReports = this.pendingReports.filter(report => !synced.includes(report.id));
        localStorage.setItem('pendingReports', JSON.stringify(this.pendingReports));
        
        if (synced.length > 0) {
            this.showNotification(`Synced ${synced.length} offline reports`, 'success');
        }
    }
    
    showNotification(message, type) {
        // Implementation for showing user notifications
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification('AquaAware', { body: message });
        }
    }
}