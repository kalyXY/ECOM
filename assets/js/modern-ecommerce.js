/**
 * StyleHub - Modern E-commerce JavaScript
 * Fonctionnalités modernes et animations pour une expérience utilisateur optimale
 */

class ModernEcommerce {
    constructor() {
        this.init();
    }

    init() {
        this.setupScrollAnimations();
        this.setupNavbarEffects();
        this.setupProductInteractions();
        this.setupWishlistFunctionality();
        this.setupCartFunctionality();
        this.setupBackToTop();
        this.setupNotifications();
    }

    // Animations au scroll
    setupScrollAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                }
            });
        }, observerOptions);

        // Observer tous les éléments avec la classe scroll-reveal
        document.querySelectorAll('.scroll-reveal').forEach(el => {
            observer.observe(el);
        });

        // Ajouter automatiquement la classe scroll-reveal aux éléments appropriés
        document.querySelectorAll('.product-card, .category-card, .service-item, section').forEach(el => {
            el.classList.add('scroll-reveal');
            observer.observe(el);
        });
    }

    // Effets de la navbar
    setupNavbarEffects() {
        const navbar = document.querySelector('.navbar');
        let lastScrollY = window.scrollY;

        window.addEventListener('scroll', () => {
            const currentScrollY = window.scrollY;
            
            if (currentScrollY > 100) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }

            // Auto-hide navbar on scroll down (mobile)
            if (window.innerWidth <= 768) {
                if (currentScrollY > lastScrollY && currentScrollY > 100) {
                    navbar.style.transform = 'translateY(-100%)';
                } else {
                    navbar.style.transform = 'translateY(0)';
                }
            }

            lastScrollY = currentScrollY;
        });
    }

    // Interactions des produits
    setupProductInteractions() {
        const productCards = document.querySelectorAll('.product-card');
        
        productCards.forEach(card => {
            let hoverTimeout;
            
            card.addEventListener('mouseenter', () => {
                clearTimeout(hoverTimeout);
                hoverTimeout = setTimeout(() => {
                    card.classList.add('animate-scale-in');
                }, 100);
            });
            
            card.addEventListener('mouseleave', () => {
                clearTimeout(hoverTimeout);
                card.classList.remove('animate-scale-in');
            });
        });
    }

    // Fonctionnalité wishlist
    setupWishlistFunctionality() {
        window.toggleWishlist = async (productId) => {
            try {
                const response = await fetch('api/wishlist_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `action=toggle&product_id=${productId}&csrf_token=${window.CSRF_TOKEN || ''}`
                });

                const result = await response.json();
                
                if (result.success) {
                    this.updateWishlistUI(productId, result.in_wishlist);
                    this.updateWishlistCount(result.count);
                    this.showNotification(
                        result.in_wishlist ? 'Produit ajouté aux favoris' : 'Produit retiré des favoris',
                        'success'
                    );
                } else {
                    throw new Error(result.message || 'Erreur');
                }
            } catch (error) {
                this.showNotification('Erreur lors de la mise à jour des favoris', 'error');
            }
        };
    }

    updateWishlistUI(productId, inWishlist) {
        const wishlistBtns = document.querySelectorAll(`[onclick*="toggleWishlist(${productId})"]`);
        wishlistBtns.forEach(btn => {
            const icon = btn.querySelector('i');
            if (inWishlist) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                btn.classList.add('active');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                btn.classList.remove('active');
            }
            btn.classList.add('animate-pulse');
            setTimeout(() => btn.classList.remove('animate-pulse'), 600);
        });
    }

    updateWishlistCount(count) {
        const counter = document.getElementById('wishlist-header-count');
        if (counter) {
            counter.textContent = count;
            counter.style.display = count > 0 ? 'inline' : 'none';
            counter.classList.add('animate-pulse');
            setTimeout(() => counter.classList.remove('animate-pulse'), 600);
        }
    }

    // Fonctionnalité panier
    setupCartFunctionality() {
        // Intercepter les formulaires d'ajout au panier
        document.addEventListener('submit', async (e) => {
            if (e.target.matches('form[action="cart.php"]') && e.target.querySelector('input[name="action"][value="add"]')) {
                e.preventDefault();
                await this.addToCart(e.target);
            }
        });
    }

    async addToCart(form) {
        try {
            const formData = new FormData(form);
            const button = form.querySelector('button[type="submit"]');
            const originalText = button.innerHTML;
            
            // Show loading state
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Ajout...';
            button.disabled = true;
            
            const response = await fetch('cart.php', {
                method: 'POST',
                body: formData
            });
            
            if (response.ok) {
                // Success animation
                button.innerHTML = '<i class="fas fa-check me-1"></i>Ajouté !';
                button.classList.add('btn-success');
                
                // Update cart count
                this.updateCartCount();
                this.showNotification('Produit ajouté au panier', 'success');
                
                // Reset button after delay
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('btn-success');
                    button.disabled = false;
                }, 2000);
            } else {
                throw new Error('Erreur lors de l\'ajout au panier');
            }
        } catch (error) {
            this.showNotification('Erreur lors de l\'ajout au panier', 'error');
            // Reset button
            const button = form.querySelector('button[type="submit"]');
            button.innerHTML = '<i class="fas fa-shopping-cart me-1"></i>Ajouter au panier';
            button.disabled = false;
        }
    }

    async updateCartCount() {
        try {
            const response = await fetch('cart_count.php');
            const data = await response.json();
            const counter = document.getElementById('cart-count');
            if (counter && data.count !== undefined) {
                counter.textContent = data.count;
                counter.style.display = data.count > 0 ? 'inline' : 'none';
                counter.classList.add('animate-pulse');
                setTimeout(() => counter.classList.remove('animate-pulse'), 600);
            }
        } catch (error) {
            console.warn('Erreur lors de la mise à jour du compteur panier:', error);
        }
    }

    // Bouton retour en haut
    setupBackToTop() {
        const backToTopBtn = document.getElementById('backToTop');
        if (!backToTopBtn) return;
        
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTopBtn.style.display = 'block';
                backToTopBtn.classList.add('animate-fade-in-up');
            } else {
                backToTopBtn.style.display = 'none';
                backToTopBtn.classList.remove('animate-fade-in-up');
            }
        });
        
        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // Système de notifications
    setupNotifications() {
        // Créer le container de notifications
        if (!document.getElementById('notification-container')) {
            const container = document.createElement('div');
            container.id = 'notification-container';
            container.className = 'position-fixed top-0 end-0 p-3';
            container.style.zIndex = '1060';
            document.body.appendChild(container);
        }
    }

    showNotification(message, type = 'info', duration = 4000) {
        const container = document.getElementById('notification-container');
        const notification = document.createElement('div');
        
        const typeClasses = {
            success: 'alert-success',
            error: 'alert-danger',
            warning: 'alert-warning',
            info: 'alert-info'
        };
        
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };
        
        notification.className = `alert ${typeClasses[type]} alert-dismissible fade show animate-fade-in-right`;
        notification.innerHTML = `
            <i class="${icons[type]} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        container.appendChild(notification);
        
        // Auto remove
        setTimeout(() => {
            if (notification.parentNode) {
                notification.classList.add('animate-fade-out-right');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }
        }, duration);
    }

    // Utilitaires
    formatPrice(price) {
        return new Intl.NumberFormat('fr-FR', {
            style: 'currency',
            currency: 'EUR'
        }).format(price);
    }
}

// Initialiser quand le DOM est prêt
document.addEventListener('DOMContentLoaded', () => {
    window.modernEcommerce = new ModernEcommerce();
});

// Styles CSS supplémentaires pour les animations
const additionalStyles = `
    .animate-fade-out-right {
        animation: fadeOutRight 0.3s ease-in-out forwards;
    }
    
    @keyframes fadeOutRight {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(30px);
        }
    }
`;

// Injecter les styles supplémentaires
const styleSheet = document.createElement('style');
styleSheet.textContent = additionalStyles;
document.head.appendChild(styleSheet);