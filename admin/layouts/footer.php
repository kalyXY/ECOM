    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Admin JS -->
    <script src="assets/js/admin.js"></script>
    
    <!-- Enhanced Admin JS -->
    <script src="assets/js/admin-enhanced.js"></script>
    
    <!-- Initialize AOS -->
    <script>
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });
    </script>
    
    <!-- Système de notifications temps réel -->
    <script>
    // Notifications en temps réel pour l'admin
    class AdminNotifications {
        constructor() {
            this.eventSource = null;
            this.init();
        }
        
        init() {
            this.createNotificationContainer();
            this.connectToSSE();
            this.bindEvents();
        }
        
        createNotificationContainer() {
            if (!document.getElementById('admin-notifications')) {
                const container = document.createElement('div');
                container.id = 'admin-notifications';
                container.className = 'position-fixed top-0 end-0 p-3';
                container.style.zIndex = '9999';
                document.body.appendChild(container);
            }
        }
        
        connectToSSE() {
            if (this.eventSource) {
                this.eventSource.close();
            }
            
            this.eventSource = new EventSource('../api/notifications.php');
            
            this.eventSource.onmessage = (event) => {
                try {
                    const data = JSON.parse(event.data);
                    this.handleNotification(data);
                } catch (e) {
                    console.error('Error parsing notification:', e);
                }
            };
            
            this.eventSource.onerror = () => {
                console.log('SSE connection error, reconnecting in 10s...');
                setTimeout(() => this.connectToSSE(), 10000);
            };
        }
        
        handleNotification(data) {
            switch (data.type) {
                case 'low_stock':
                    this.showNotification(
                        'Stock faible',
                        data.message,
                        'warning',
                        () => window.location.href = 'products.php?filter=low_stock'
                    );
                    break;
                    
                case 'new_orders':
                    this.showNotification(
                        'Nouvelles commandes',
                        data.message,
                        'success',
                        () => window.location.href = 'orders.php'
                    );
                    break;
                    
                case 'stats_update':
                    this.updateDashboardStats(data.stats);
                    break;
            }
        }
        
        showNotification(title, message, type = 'info', onClick = null) {
            const container = document.getElementById('admin-notifications');
            const notification = document.createElement('div');
            
            const bgClass = {
                'success': 'bg-success',
                'warning': 'bg-warning',
                'error': 'bg-danger',
                'info': 'bg-info'
            }[type] || 'bg-info';
            
            notification.className = `toast align-items-center text-white ${bgClass} border-0 mb-2`;
            notification.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <strong>${title}</strong><br>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            
            if (onClick) {
                notification.style.cursor = 'pointer';
                notification.addEventListener('click', onClick);
            }
            
            container.appendChild(notification);
            
            const toast = new bootstrap.Toast(notification, { delay: 5000 });
            toast.show();
            
            notification.addEventListener('hidden.bs.toast', () => {
                notification.remove();
            });
        }
        
        updateDashboardStats(stats) {
            Object.keys(stats).forEach(key => {
                const element = document.querySelector(`[data-stat="${key}"]`);
                if (element) {
                    if (key === 'today_revenue') {
                        element.textContent = new Intl.NumberFormat('fr-FR', {
                            style: 'currency',
                            currency: 'EUR'
                        }).format(stats[key]);
                    } else {
                        element.textContent = stats[key].toLocaleString('fr-FR');
                    }
                }
            });
        }
        
        bindEvents() {
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden && (!this.eventSource || this.eventSource.readyState === EventSource.CLOSED)) {
                    this.connectToSSE();
                }
            });
        }
        
        disconnect() {
            if (this.eventSource) {
                this.eventSource.close();
            }
        }
    }
    
    // Initialiser les notifications
    let adminNotifications;
    document.addEventListener('DOMContentLoaded', () => {
        adminNotifications = new AdminNotifications();
    });
    
    window.addEventListener('beforeunload', () => {
        if (adminNotifications) {
            adminNotifications.disconnect();
        }
    });
    </script>
    
    <!-- Scripts spécifiques à la page -->
    <?php if (isset($pageScripts)): ?>
        <?php echo $pageScripts; ?>
    <?php endif; ?>
</body>
</html>