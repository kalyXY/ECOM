/**
 * StyleHub - JavaScript moderne pour e-commerce mode
 * Inspiré d'Alibaba/AliExpress avec fonctionnalités avancées
 */

class StyleHubApp {
    constructor() {
        this.cart = JSON.parse(localStorage.getItem('stylehub_cart') || '{}');
        this.wishlist = JSON.parse(localStorage.getItem('stylehub_wishlist') || '[]');
        this.init();
    }

    init() {
        this.initEventListeners();
        this.updateCartCounter();
        this.initLazyLoading();
        this.initSearchSuggestions();
        // Quick view may be optional in some pages
        if (typeof this.initProductQuickView === 'function') {
            this.initProductQuickView();
        }
        this.initNotifications();
    }

    // Gestion des événements
    initEventListeners() {
        // Panier
        document.addEventListener('click', (e) => {
            if (e.target.closest('.btn-add-to-cart')) {
                e.preventDefault();
                this.handleAddToCart(e.target.closest('.btn-add-to-cart'));
            }
        });

        // Wishlist
        document.addEventListener('click', (e) => {
            if (e.target.closest('.wishlist-btn')) {
                e.preventDefault();
                this.handleWishlist(e.target.closest('.wishlist-btn'));
            }
        });

        // Quick view
        document.addEventListener('click', (e) => {
            if (e.target.closest('.btn-quick-view')) {
                e.preventDefault();
                this.showQuickView(e.target.closest('.btn-quick-view'));
            }
        });

        // Filtres produits
        const filterInputs = document.querySelectorAll('.product-filter');
        filterInputs.forEach(input => {
            input.addEventListener('change', () => this.applyFilters());
        });

        // Recherche instantanée
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('input', debounce((e) => {
                this.handleInstantSearch(e.target.value);
            }, 300));
        }
    }

    // Gestion du panier
    handleAddToCart(button) {
        const productId = button.dataset.productId;
        const productName = button.dataset.productName;
        const productPrice = parseFloat(button.dataset.productPrice);
        const quantity = 1;

        if (!this.cart[productId]) {
            this.cart[productId] = {
                id: productId,
                name: productName,
                price: productPrice,
                quantity: 0
            };
        }

        this.cart[productId].quantity += quantity;
        
        // Sauvegarde
        localStorage.setItem('stylehub_cart', JSON.stringify(this.cart));
        
        // Animation du bouton
        this.animateAddToCart(button);
        
        // Notification
        this.showNotification(`${productName} ajouté au panier`, 'success');
        
        // Mise à jour du compteur
        this.updateCartCounter();
    }

    // Animation d'ajout au panier
    animateAddToCart(button) {
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check me-1"></i>Ajouté !';
        button.classList.add('btn-success');
        button.disabled = true;

        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('btn-success');
            button.disabled = false;
        }, 2000);
    }

    // Gestion de la wishlist
    handleWishlist(button) {
        const productId = button.dataset.productId;
        const icon = button.querySelector('i');
        
        if (this.wishlist.includes(productId)) {
            // Retirer de la wishlist
            this.wishlist = this.wishlist.filter(id => id !== productId);
            icon.classList.remove('fas');
            icon.classList.add('far');
            button.classList.remove('active');
            this.showNotification('Retiré des favoris', 'info');
        } else {
            // Ajouter à la wishlist
            this.wishlist.push(productId);
            icon.classList.remove('far');
            icon.classList.add('fas');
            button.classList.add('active');
            this.showNotification('Ajouté aux favoris', 'success');
        }
        
        localStorage.setItem('stylehub_wishlist', JSON.stringify(this.wishlist));
    }

    // Mise à jour du compteur de panier
    updateCartCounter() {
        const totalItems = Object.values(this.cart).reduce((sum, item) => sum + item.quantity, 0);
        const counter = document.getElementById('cart-count');
        
        if (counter) {
            counter.textContent = totalItems;
            counter.style.display = totalItems > 0 ? 'block' : 'none';
        }
    }

    // Lazy loading des images
    initLazyLoading() {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });

        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }

    // Suggestions de recherche
    initSearchSuggestions() {
        const searchInput = document.querySelector('input[name="search"]');
        if (!searchInput) return;

        const suggestionsContainer = document.createElement('div');
        suggestionsContainer.className = 'search-suggestions';
        suggestionsContainer.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e8e8e8;
            border-top: none;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            z-index: 1000;
            display: none;
        `;
        
        searchInput.parentElement.style.position = 'relative';
        searchInput.parentElement.appendChild(suggestionsContainer);
    }

    // Recherche instantanée optimisée
    async handleInstantSearch(query) {
        if (query.length < 2) {
            this.hideSearchSuggestions();
            return;
        }

        // Afficher un loader
        this.showSearchLoader();

        try {
            const response = await fetch(`api/search.php?q=${encodeURIComponent(query)}&limit=8`);
            const data = await response.json();
            this.displaySearchSuggestions(data.suggestions);
        } catch (error) {
            console.error('Erreur recherche:', error);
            this.hideSearchSuggestions();
        }
    }

    // Affichage des suggestions amélioré
    displaySearchSuggestions(suggestions) {
        const container = document.querySelector('.search-suggestions');
        if (!container) return;

        if (suggestions.length === 0) {
            container.innerHTML = `
                <div class="suggestion-empty">
                    <i class="fas fa-search text-muted"></i>
                    <span>Aucun résultat trouvé</span>
                </div>
            `;
            container.style.display = 'block';
            return;
        }

        container.innerHTML = suggestions.map(item => `
            <div class="suggestion-item" onclick="location.href='product.php?id=${item.id}'">
                <img src="${item.image}" alt="${item.name}" class="suggestion-image" loading="lazy">
                <div class="suggestion-content">
                    <div class="suggestion-name">${item.name}</div>
                    <div class="suggestion-meta">
                        ${item.brand ? `<span class="suggestion-brand">${item.brand}</span>` : ''}
                        <span class="suggestion-price">${item.price}</span>
                    </div>
                </div>
                <i class="fas fa-arrow-right suggestion-arrow"></i>
            </div>
        `).join('');

        container.style.display = 'block';
    }

    // Afficher le loader de recherche
    showSearchLoader() {
        const container = document.querySelector('.search-suggestions');
        if (!container) return;

        container.innerHTML = `
            <div class="suggestion-loader">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Recherche...</span>
                </div>
                <span class="ms-2">Recherche en cours...</span>
            </div>
        `;
        container.style.display = 'block';
    }

    // Masquer les suggestions
    hideSearchSuggestions() {
        const container = document.querySelector('.search-suggestions');
        if (container) {
            container.style.display = 'none';
        }
    }

    // Quick View modal
    showQuickView(button) {
        const productId = button.dataset.productId;
        // Implémenter modal de vue rapide
        this.showNotification('Vue rapide - Fonctionnalité à venir', 'info');
    }

    // Filtres produits
    applyFilters() {
        const filters = {};
        document.querySelectorAll('.product-filter:checked').forEach(input => {
            if (!filters[input.name]) filters[input.name] = [];
            filters[input.name].push(input.value);
        });

        // Construire l'URL avec les filtres
        const params = new URLSearchParams();
        Object.keys(filters).forEach(key => {
            filters[key].forEach(value => params.append(key, value));
        });

        // Rediriger avec les filtres
        window.location.href = `products.php?${params.toString()}`;
    }

    // Système de notifications
    initNotifications() {
        if (!document.querySelector('#notification-container')) {
            const container = document.createElement('div');
            container.id = 'notification-container';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                pointer-events: none;
            `;
            document.body.appendChild(container);
        }
    }

    showNotification(message, type = 'info') {
        const container = document.getElementById('notification-container');
        const notification = document.createElement('div');
        
        const colors = {
            success: '#52c41a',
            error: '#ff4d4f',
            warning: '#faad14',
            info: '#1890ff'
        };

        notification.style.cssText = `
            background: ${colors[type] || colors.info};
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateX(300px);
            transition: transform 0.3s ease;
            pointer-events: auto;
            font-size: 14px;
            max-width: 300px;
        `;

        notification.textContent = message;
        container.appendChild(notification);

        // Animation d'entrée
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);

        // Suppression automatique
        setTimeout(() => {
            notification.style.transform = 'translateX(300px)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }

    // Comparaison de produits
    addToCompare(productId) {
        let compareList = JSON.parse(localStorage.getItem('stylehub_compare') || '[]');
        
        if (compareList.length >= 4) {
            this.showNotification('Maximum 4 produits en comparaison', 'warning');
            return;
        }

        if (!compareList.includes(productId)) {
            compareList.push(productId);
            localStorage.setItem('stylehub_compare', JSON.stringify(compareList));
            this.showNotification('Produit ajouté à la comparaison', 'success');
        }
    }

    // Partage social
    shareProduct(productId, platform) {
        const url = encodeURIComponent(window.location.href);
        const text = encodeURIComponent('Découvrez ce produit sur StyleHub');
        
        const shareUrls = {
            facebook: `https://www.facebook.com/sharer/sharer.php?u=${url}`,
            twitter: `https://twitter.com/intent/tweet?url=${url}&text=${text}`,
            pinterest: `https://pinterest.com/pin/create/button/?url=${url}&description=${text}`,
            whatsapp: `https://wa.me/?text=${text}%20${url}`
        };

        if (shareUrls[platform]) {
            window.open(shareUrls[platform], '_blank', 'width=600,height=400');
        }
    }
}

// Fonction utilitaire debounce
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Fonction globale pour l'ajout au panier (compatibilité)
function addToCart(productId, productName, productPrice) {
    const button = event.target.closest('button');
    button.dataset.productId = productId;
    button.dataset.productName = productName;
    button.dataset.productPrice = productPrice;
    
    window.styleHubApp.handleAddToCart(button);
}

// Fonction globale pour la wishlist (compatibilité)
function toggleWishlist(productId) {
    const button = event.target.closest('button');
    button.dataset.productId = productId;
    
    window.styleHubApp.handleWishlist(button);
}

// Initialisation de l'application
document.addEventListener('DOMContentLoaded', () => {
    window.styleHubApp = new StyleHubApp();
    
    // Animation des cartes produits au scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });

    // Observer les cartes produits
    document.querySelectorAll('.product-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
});

// Fonction pour exporter les données (admin)
function exportData() {
    window.styleHubApp.showNotification('Export en cours...', 'info');
    // Implémenter la logique d'export
}