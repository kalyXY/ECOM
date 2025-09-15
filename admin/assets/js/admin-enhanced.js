/**
 * JavaScript Admin Ultra-Moderne - StyleHub E-Commerce
 * Interactions et animations premium pour le back office
 */

class AdminUI {
    constructor() {
        this.init();
        this.setupEventListeners();
        this.setupAnimations();
        this.setupTheme();
    }

    init() {
        // Initialisation des composants
        this.sidebar = document.getElementById('adminSidebar');
        this.sidebarToggle = document.getElementById('sidebarToggle');
        this.adminContent = document.querySelector('.admin-content');
        
        // Variables d'état
        this.sidebarCollapsed = false;
        this.currentTheme = localStorage.getItem('admin-theme') || 'light';
        
        console.log('🚀 AdminUI initialized');
    }

    setupEventListeners() {
        // Toggle sidebar
        if (this.sidebarToggle) {
            this.sidebarToggle.addEventListener('click', () => this.toggleSidebar());
        }

        // Navigation active
        this.setupActiveNavigation();

        // Animations au scroll
        this.setupScrollAnimations();

        // Tooltips
        this.setupTooltips();

        // Modals
        this.setupModals();

        // Tables interactives
        this.setupInteractiveTables();

        // Forms améliorés
        this.setupEnhancedForms();

        // Notifications
        this.setupNotifications();

        // Recherche en temps réel
        this.setupLiveSearch();

        // Gestion responsive
        this.setupResponsive();
    }

    toggleSidebar() {
        if (window.innerWidth <= 1024) {
            // Mode mobile - show/hide
            this.sidebar.classList.toggle('show');
        } else {
            // Mode desktop - collapse/expand
            this.sidebar.classList.toggle('collapsed');
            this.sidebarCollapsed = !this.sidebarCollapsed;
            
            // Animation du contenu
            if (this.sidebarCollapsed) {
                this.adminContent.style.marginLeft = '80px';
            } else {
                this.adminContent.style.marginLeft = '280px';
            }
        }

        // Animation de l'icône
        const icon = this.sidebarToggle.querySelector('i');
        icon.style.transform = 'rotate(180deg)';
        setTimeout(() => {
            icon.style.transform = 'rotate(0deg)';
        }, 300);
    }

    setupActiveNavigation() {
        const navLinks = document.querySelectorAll('.nav-link');
        const currentPath = window.location.pathname.split('/').pop();

        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href === currentPath || (currentPath === '' && href === 'index.php')) {
                link.classList.add('active');
                
                // Animation d'apparition
                this.animateNavItem(link);
            }
        });
    }

    animateNavItem(item) {
        item.style.transform = 'translateX(-20px)';
        item.style.opacity = '0';
        
        setTimeout(() => {
            item.style.transition = 'all 0.5s ease';
            item.style.transform = 'translateX(0)';
            item.style.opacity = '1';
        }, 100);
    }

    setupScrollAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, observerOptions);

        // Observer les éléments à animer
        const animateElements = document.querySelectorAll('.card, .stat-card, .table-container');
        animateElements.forEach(el => {
            el.classList.add('animate-on-scroll');
            observer.observe(el);
        });
    }

    setupTooltips() {
        const tooltipTriggers = document.querySelectorAll('[data-tooltip]');
        
        tooltipTriggers.forEach(trigger => {
            const tooltip = this.createTooltip(trigger.dataset.tooltip);
            
            trigger.addEventListener('mouseenter', (e) => {
                this.showTooltip(tooltip, e.target);
            });
            
            trigger.addEventListener('mouseleave', () => {
                this.hideTooltip(tooltip);
            });
        });
    }

    createTooltip(text) {
        const tooltip = document.createElement('div');
        tooltip.className = 'admin-tooltip';
        tooltip.textContent = text;
        tooltip.style.cssText = `
            position: absolute;
            background: rgba(15, 23, 42, 0.9);
            color: white;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 500;
            pointer-events: none;
            z-index: 10000;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        `;
        document.body.appendChild(tooltip);
        return tooltip;
    }

    showTooltip(tooltip, target) {
        const rect = target.getBoundingClientRect();
        tooltip.style.left = `${rect.left + rect.width / 2 - tooltip.offsetWidth / 2}px`;
        tooltip.style.top = `${rect.top - tooltip.offsetHeight - 10}px`;
        tooltip.style.opacity = '1';
        tooltip.style.transform = 'translateY(0)';
    }

    hideTooltip(tooltip) {
        tooltip.style.opacity = '0';
        tooltip.style.transform = 'translateY(10px)';
    }

    setupModals() {
        const modalTriggers = document.querySelectorAll('[data-modal]');
        
        modalTriggers.forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                const modalId = trigger.dataset.modal;
                this.showModal(modalId);
            });
        });

        // Fermeture des modals
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-backdrop')) {
                this.hideModal();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.hideModal();
            }
        });
    }

    showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        modal.style.display = 'flex';
        modal.style.opacity = '0';
        modal.style.transform = 'scale(0.9)';
        
        setTimeout(() => {
            modal.style.transition = 'all 0.3s ease';
            modal.style.opacity = '1';
            modal.style.transform = 'scale(1)';
        }, 10);

        document.body.style.overflow = 'hidden';
    }

    hideModal() {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.style.opacity = '0';
            modal.style.transform = 'scale(0.9)';
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        });
        
        document.body.style.overflow = '';
    }

    setupInteractiveTables() {
        const tables = document.querySelectorAll('.table');
        
        tables.forEach(table => {
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                row.addEventListener('click', (e) => {
                    if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON') return;
                    
                    // Animation de sélection
                    rows.forEach(r => r.classList.remove('selected'));
                    row.classList.add('selected');
                    
                    // Effet de pulsation
                    row.style.animation = 'pulse 0.3s ease';
                    setTimeout(() => {
                        row.style.animation = '';
                    }, 300);
                });
            });
        });
    }

    setupEnhancedForms() {
        const formGroups = document.querySelectorAll('.form-group');
        
        formGroups.forEach(group => {
            const input = group.querySelector('.form-control');
            const label = group.querySelector('.form-label');
            
            if (!input || !label) return;

            // Animation du label
            input.addEventListener('focus', () => {
                label.style.transform = 'translateY(-2px)';
                label.style.color = 'var(--primary-color)';
                group.classList.add('focused');
            });

            input.addEventListener('blur', () => {
                if (!input.value) {
                    label.style.transform = 'translateY(0)';
                    label.style.color = '';
                    group.classList.remove('focused');
                }
            });

            // Validation en temps réel
            input.addEventListener('input', () => {
                this.validateField(input);
            });
        });
    }

    validateField(input) {
        const isValid = input.checkValidity();
        const group = input.closest('.form-group');
        
        group.classList.remove('is-valid', 'is-invalid');
        
        if (input.value) {
            group.classList.add(isValid ? 'is-valid' : 'is-invalid');
        }
    }

    setupNotifications() {
        // Créer le conteneur de notifications
        this.createNotificationContainer();
        
        // Écouter les événements de notification
        window.addEventListener('showNotification', (e) => {
            this.showNotification(e.detail);
        });
    }

    createNotificationContainer() {
        if (document.getElementById('notification-container')) return;
        
        const container = document.createElement('div');
        container.id = 'notification-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-width: 400px;
        `;
        document.body.appendChild(container);
    }

    showNotification({ type = 'info', title, message, duration = 5000 }) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        const colors = {
            success: '#10b981',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#3b82f6'
        };

        notification.style.cssText = `
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-left: 4px solid ${colors[type]};
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            transform: translateX(400px);
            transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            cursor: pointer;
        `;

        notification.innerHTML = `
            <div style="display: flex; align-items: flex-start; gap: 12px;">
                <div style="color: ${colors[type]}; font-size: 20px;">
                    ${this.getNotificationIcon(type)}
                </div>
                <div style="flex: 1;">
                    ${title ? `<div style="font-weight: 600; color: #1e293b; margin-bottom: 4px;">${title}</div>` : ''}
                    <div style="color: #64748b; font-size: 14px;">${message}</div>
                </div>
                <button style="background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 18px;">×</button>
            </div>
        `;

        const container = document.getElementById('notification-container');
        container.appendChild(notification);

        // Animation d'entrée
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);

        // Fermeture automatique
        const closeBtn = notification.querySelector('button');
        const autoClose = setTimeout(() => {
            this.hideNotification(notification);
        }, duration);

        // Fermeture manuelle
        [notification, closeBtn].forEach(el => {
            el.addEventListener('click', () => {
                clearTimeout(autoClose);
                this.hideNotification(notification);
            });
        });
    }

    hideNotification(notification) {
        notification.style.transform = 'translateX(400px)';
        notification.style.opacity = '0';
        setTimeout(() => {
            notification.remove();
        }, 500);
    }

    getNotificationIcon(type) {
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        return icons[type] || icons.info;
    }

    setupLiveSearch() {
        const searchInputs = document.querySelectorAll('[data-live-search]');
        
        searchInputs.forEach(input => {
            let searchTimeout;
            
            input.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                
                searchTimeout = setTimeout(() => {
                    const query = e.target.value.toLowerCase();
                    const targetSelector = input.dataset.liveSearch;
                    const targets = document.querySelectorAll(targetSelector);
                    
                    targets.forEach(target => {
                        const text = target.textContent.toLowerCase();
                        const shouldShow = text.includes(query);
                        
                        target.style.display = shouldShow ? '' : 'none';
                        
                        // Animation de filtrage
                        if (shouldShow) {
                            target.style.animation = 'fadeIn 0.3s ease';
                        }
                    });
                }, 300);
            });
        });
    }

    setupResponsive() {
        const mediaQuery = window.matchMedia('(max-width: 1024px)');
        
        const handleResponsive = (e) => {
            if (e.matches) {
                // Mode mobile
                this.sidebar.classList.remove('collapsed');
                this.adminContent.style.marginLeft = '0';
            } else {
                // Mode desktop
                this.sidebar.classList.remove('show');
                if (!this.sidebarCollapsed) {
                    this.adminContent.style.marginLeft = '280px';
                }
            }
        };

        mediaQuery.addListener(handleResponsive);
        handleResponsive(mediaQuery);

        // Fermer la sidebar en cliquant à l'extérieur (mobile)
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 1024 && 
                !this.sidebar.contains(e.target) && 
                !this.sidebarToggle.contains(e.target) &&
                this.sidebar.classList.contains('show')) {
                this.sidebar.classList.remove('show');
            }
        });
    }

    setupAnimations() {
        // Ajouter les styles CSS pour les animations
        const style = document.createElement('style');
        style.textContent = `
            .animate-on-scroll {
                opacity: 0;
                transform: translateY(30px);
                transition: all 0.6s ease;
            }
            
            .animate-in {
                opacity: 1;
                transform: translateY(0);
            }
            
            .table tbody tr.selected {
                background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(102, 126, 234, 0.05) 100%);
                border-left: 4px solid var(--primary-color);
            }
            
            .form-group.focused .form-label {
                font-weight: 600;
            }
            
            .form-group.is-valid .form-control {
                border-color: var(--success-color);
                box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
            }
            
            .form-group.is-invalid .form-control {
                border-color: var(--danger-color);
                box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
            }
            
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            
            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.02); }
            }
        `;
        document.head.appendChild(style);
    }

    setupTheme() {
        // Appliquer le thème sauvegardé
        document.documentElement.setAttribute('data-theme', this.currentTheme);
        
        // Créer le bouton de toggle de thème
        this.createThemeToggle();
    }

    createThemeToggle() {
        const toggle = document.createElement('button');
        toggle.className = 'theme-toggle';
        toggle.innerHTML = this.currentTheme === 'dark' ? '☀️' : '🌙';
        toggle.title = 'Changer de thème';
        
        toggle.addEventListener('click', () => {
            this.toggleTheme();
        });
        
        document.body.appendChild(toggle);
    }

    toggleTheme() {
        this.currentTheme = this.currentTheme === 'light' ? 'dark' : 'light';
        document.documentElement.setAttribute('data-theme', this.currentTheme);
        localStorage.setItem('admin-theme', this.currentTheme);
        
        const toggle = document.querySelector('.theme-toggle');
        toggle.innerHTML = this.currentTheme === 'dark' ? '☀️' : '🌙';
        
        // Animation de transition
        document.body.style.transition = 'all 0.3s ease';
        setTimeout(() => {
            document.body.style.transition = '';
        }, 300);
    }

    // Méthodes utilitaires
    showLoader(element) {
        element.classList.add('loading-shimmer');
    }

    hideLoader(element) {
        element.classList.remove('loading-shimmer');
    }

    animateCounter(element, target, duration = 2000) {
        const start = 0;
        const increment = target / (duration / 16);
        let current = start;
        
        const timer = setInterval(() => {
            current += increment;
            element.textContent = Math.floor(current);
            
            if (current >= target) {
                element.textContent = target;
                clearInterval(timer);
            }
        }, 16);
    }

    // API publique pour les notifications
    static notify(type, message, title = null, duration = 5000) {
        window.dispatchEvent(new CustomEvent('showNotification', {
            detail: { type, message, title, duration }
        }));
    }
}

// Fonctions utilitaires globales
window.AdminNotify = {
    success: (message, title) => AdminUI.notify('success', message, title),
    error: (message, title) => AdminUI.notify('error', message, title),
    warning: (message, title) => AdminUI.notify('warning', message, title),
    info: (message, title) => AdminUI.notify('info', message, title)
};

// Initialisation automatique
document.addEventListener('DOMContentLoaded', () => {
    window.adminUI = new AdminUI();
    
    // Animer les compteurs au chargement
    const statValues = document.querySelectorAll('.stat-value');
    statValues.forEach(stat => {
        const value = parseInt(stat.textContent);
        if (value > 0) {
            stat.textContent = '0';
            setTimeout(() => {
                window.adminUI.animateCounter(stat, value);
            }, 500);
        }
    });
    
    console.log('🎉 Admin UI fully loaded and enhanced!');
});

// Export pour utilisation modulaire
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AdminUI;
}