// JavaScript pour StyleHub - Boutique de Mode

// Fonction pour ajouter/retirer des favoris
function addToWishlist(productId) {
    // Créer le formulaire pour l'envoi
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'wishlist.php';
    form.style.display = 'none';
    
    // Token CSRF
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = getCSRFToken();
    form.appendChild(csrfInput);
    
    // Action
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'add';
    form.appendChild(actionInput);
    
    // ID du produit
    const productInput = document.createElement('input');
    productInput.type = 'hidden';
    productInput.name = 'product_id';
    productInput.value = productId;
    form.appendChild(productInput);
    
    // Ajouter au DOM et soumettre
    document.body.appendChild(form);
    form.submit();
}

function removeFromWishlist(productId) {
    if (!confirm('Retirer ce produit de vos favoris ?')) {
        return;
    }
    
    // Créer le formulaire pour l'envoi
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'wishlist.php';
    form.style.display = 'none';
    
    // Token CSRF
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = getCSRFToken();
    form.appendChild(csrfInput);
    
    // Action
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'remove';
    form.appendChild(actionInput);
    
    // ID du produit
    const productInput = document.createElement('input');
    productInput.type = 'hidden';
    productInput.name = 'product_id';
    productInput.value = productId;
    form.appendChild(productInput);
    
    // Ajouter au DOM et soumettre
    document.body.appendChild(form);
    form.submit();
}

// Fonction pour obtenir le token CSRF
function getCSRFToken() {
    // Essayer de récupérer depuis un meta tag ou un champ caché existant
    const metaToken = document.querySelector('meta[name="csrf-token"]');
    if (metaToken) {
        return metaToken.getAttribute('content');
    }
    
    const hiddenToken = document.querySelector('input[name="csrf_token"]');
    if (hiddenToken) {
        return hiddenToken.value;
    }
    
    // Générer un token temporaire (pas idéal mais fonctionnel)
    return Math.random().toString(36).substring(2) + Date.now().toString(36);
}

// Fonction pour ajouter un produit au panier
function addToCart(productId, productName, productPrice, quantity = 1) {
    // Créer un formulaire pour envoyer les données
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'cart.php';
    form.style.display = 'none';
    
    // Ajouter les champs
    const fields = {
        action: 'add',
        id: productId,
        name: productName,
        price: productPrice,
        quantity: quantity
    };
    
    for (const [key, value] of Object.entries(fields)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    }
    
    document.body.appendChild(form);
    form.submit();
}

// Fonction pour mettre à jour le compteur du panier
function updateCartCount() {
    fetch('cart_count.php')
        .then(response => response.json())
        .then(data => {
            const cartCountElements = document.querySelectorAll('#cart-count');
            cartCountElements.forEach(element => {
                element.textContent = data.count;
                element.style.display = data.count > 0 ? 'inline' : 'none';
            });
        })
        .catch(error => {
            console.error('Erreur lors de la mise à jour du compteur:', error);
        });
}

// Fonction pour le bouton "Retour en haut"
function initBackToTop() {
    const backToTopButton = document.getElementById('backToTop');
    if (!backToTopButton) return;
    
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            backToTopButton.style.display = 'block';
        } else {
            backToTopButton.style.display = 'none';
        }
    });
    
    backToTopButton.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// Fonction pour la validation des formulaires Bootstrap
function initFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
}

// Fonction pour les animations au scroll
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    }, observerOptions);
    
    // Observer les cartes de produits
    document.querySelectorAll('.product-card').forEach(card => {
        observer.observe(card);
    });
}

// Fonction pour gérer les filtres de produits
function initProductFilters() {
    const searchForm = document.querySelector('form[action="products.php"]');
    if (!searchForm) return;
    
    const searchInput = searchForm.querySelector('input[name="search"]');
    const categorySelect = searchForm.querySelector('select[name="category"]');
    const sortSelect = searchForm.querySelector('select[name="sort"]');
    
    // Auto-submit sur changement de catégorie ou tri
    if (categorySelect) {
        categorySelect.addEventListener('change', () => {
            searchForm.submit();
        });
    }
    
    if (sortSelect) {
        sortSelect.addEventListener('change', () => {
            searchForm.submit();
        });
    }
}

// Fonction pour gérer la quantité dans le panier
function updateQuantity(productId, change) {
    const quantityInput = document.querySelector(`input[data-product-id="${productId}"]`);
    if (!quantityInput) return;
    
    let currentQuantity = parseInt(quantityInput.value) || 1;
    let newQuantity = currentQuantity + change;
    
    if (newQuantity < 1) newQuantity = 1;
    if (newQuantity > 99) newQuantity = 99;
    
    quantityInput.value = newQuantity;
    
    // Optionnel: mise à jour automatique du panier
    // updateCartItem(productId, newQuantity);
}

// Fonction pour confirmer la suppression
function confirmDelete(message = 'Êtes-vous sûr de vouloir supprimer cet élément ?') {
    return confirm(message);
}

// Fonction pour afficher des notifications toast stylées
function showToast(message, type = 'success') {
    // Créer l'élément toast avec style fashion
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0 shadow-lg`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    toast.style.borderRadius = '15px';
    
    const icons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-exclamation-circle',
        info: 'fas fa-info-circle',
        warning: 'fas fa-exclamation-triangle'
    };
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center">
                <i class="${icons[type] || icons.success} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    // Ajouter le toast au container
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    toastContainer.appendChild(toast);
    
    // Afficher le toast avec animation
    const bsToast = new bootstrap.Toast(toast, {
        autohide: true,
        delay: 4000
    });
    bsToast.show();
    
    // Supprimer le toast après fermeture
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

// Fonction pour gérer le chargement des images
function initImageLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

// Fonction pour gérer les favoris (localStorage)
function toggleFavorite(productId) {
    let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    const index = favorites.indexOf(productId);
    
    if (index > -1) {
        favorites.splice(index, 1);
        showToast('Produit retiré des favoris', 'info');
    } else {
        favorites.push(productId);
        showToast('Produit ajouté aux favoris', 'success');
    }
    
    localStorage.setItem('favorites', JSON.stringify(favorites));
    updateFavoriteButtons();
}

// Fonction pour mettre à jour l'affichage des boutons favoris
function updateFavoriteButtons() {
    const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    
    document.querySelectorAll('[data-favorite-id]').forEach(button => {
        const productId = parseInt(button.dataset.favoriteId);
        const icon = button.querySelector('i');
        
        if (favorites.includes(productId)) {
            icon.classList.remove('far');
            icon.classList.add('fas');
            button.classList.add('text-danger');
        } else {
            icon.classList.remove('fas');
            icon.classList.add('far');
            button.classList.remove('text-danger');
        }
    });
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser toutes les fonctionnalités
    initBackToTop();
    initFormValidation();
    initScrollAnimations();
    initProductFilters();
    initImageLoading();
    updateFavoriteButtons();
    
    // Mettre à jour le compteur du panier
    updateCartCount();
    
    // Actualiser le compteur toutes les 30 secondes
    setInterval(updateCartCount, 30000);
});

// Gestion des erreurs globales
window.addEventListener('error', function(e) {
    console.error('Erreur JavaScript:', e.error);
});

// Fonction utilitaire pour formater les prix
function formatPrice(price) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    }).format(price);
}

// Fonction utilitaire pour debounce
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
// 
Fonctions spécifiques à la mode

// Fonction pour gérer la sélection de taille
function selectSize(element, size) {
    // Retirer la sélection des autres tailles
    document.querySelectorAll('.size-option').forEach(option => {
        option.classList.remove('selected');
    });
    
    // Sélectionner la taille cliquée
    element.classList.add('selected');
    
    // Mettre à jour le champ caché si présent
    const sizeInput = document.querySelector('input[name="selected_size"]');
    if (sizeInput) {
        sizeInput.value = size;
    }
}

// Fonction pour gérer la wishlist avec localStorage
function toggleWishlist(productId) {
    let favorites = JSON.parse(localStorage.getItem('fashion_favorites') || '[]');
    const button = event.target.closest('.wishlist-btn');
    const icon = button.querySelector('i');
    
    const index = favorites.indexOf(productId);
    
    if (index > -1) {
        // Retirer des favoris
        favorites.splice(index, 1);
        icon.classList.remove('fas');
        icon.classList.add('far');
        button.classList.remove('active');
        showToast('Article retiré de vos favoris', 'info');
    } else {
        // Ajouter aux favoris
        favorites.push(productId);
        icon.classList.remove('far');
        icon.classList.add('fas');
        button.classList.add('active');
        showToast('Article ajouté à vos favoris', 'success');
    }
    
    localStorage.setItem('fashion_favorites', JSON.stringify(favorites));
    updateWishlistCount();
}

// Fonction pour mettre à jour le compteur de wishlist
function updateWishlistCount() {
    const favorites = JSON.parse(localStorage.getItem('fashion_favorites') || '[]');
    const wishlistCounters = document.querySelectorAll('.wishlist-count');
    
    wishlistCounters.forEach(counter => {
        counter.textContent = favorites.length;
        counter.style.display = favorites.length > 0 ? 'inline' : 'none';
    });
}

// Fonction pour initialiser les boutons wishlist
function initWishlistButtons() {
    const favorites = JSON.parse(localStorage.getItem('fashion_favorites') || '[]');
    
    document.querySelectorAll('.wishlist-btn').forEach(button => {
        const productId = parseInt(button.dataset.productId);
        const icon = button.querySelector('i');
        
        if (favorites.includes(productId)) {
            icon.classList.remove('far');
            icon.classList.add('fas');
            button.classList.add('active');
        }
    });
}

// Fonction pour le zoom d'image produit
function initImageZoom() {
    const productImages = document.querySelectorAll('.product-main-image');
    
    productImages.forEach(img => {
        img.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const xPercent = (x / rect.width) * 100;
            const yPercent = (y / rect.height) * 100;
            
            this.style.transformOrigin = `${xPercent}% ${yPercent}%`;
            this.style.transform = 'scale(1.5)';
        });
        
        img.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
}

// Fonction pour le carrousel d'images produit
function initProductGallery() {
    const thumbnails = document.querySelectorAll('.product-thumbnail');
    const mainImage = document.querySelector('.product-main-image');
    
    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
            // Retirer la classe active des autres thumbnails
            thumbnails.forEach(t => t.classList.remove('active'));
            
            // Ajouter la classe active au thumbnail cliqué
            this.classList.add('active');
            
            // Changer l'image principale
            if (mainImage) {
                mainImage.src = this.src;
                mainImage.alt = this.alt;
            }
        });
    });
}

// Fonction pour le filtre de couleur
function filterByColor(color) {
    const products = document.querySelectorAll('.product-card');
    
    products.forEach(product => {
        const productColor = product.dataset.color;
        
        if (!color || productColor === color) {
            product.style.display = 'block';
            product.classList.add('fade-in-up');
        } else {
            product.style.display = 'none';
        }
    });
    
    // Mettre à jour les boutons de filtre
    document.querySelectorAll('.color-filter').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.color === color);
    });
}

// Fonction pour le guide des tailles
function showSizeGuide() {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Guide des Tailles</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Taille</th>
                                    <th>Tour de poitrine (cm)</th>
                                    <th>Tour de taille (cm)</th>
                                    <th>Tour de hanches (cm)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>XS</td><td>78-82</td><td>58-62</td><td>84-88</td></tr>
                                <tr><td>S</td><td>82-86</td><td>62-66</td><td>88-92</td></tr>
                                <tr><td>M</td><td>86-90</td><td>66-70</td><td>92-96</td></tr>
                                <tr><td>L</td><td>90-94</td><td>70-74</td><td>96-100</td></tr>
                                <tr><td>XL</td><td>94-98</td><td>74-78</td><td>100-104</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    modal.addEventListener('hidden.bs.modal', () => {
        modal.remove();
    });
}

// Fonction pour l'animation de chargement des produits
function showProductSkeleton() {
    const container = document.querySelector('.products-container');
    if (!container) return;
    
    const skeleton = `
        <div class="col-md-6 col-lg-3 product-skeleton">
            <div class="card">
                <div class="card-img-top shimmer" style="height: 280px;"></div>
                <div class="card-body">
                    <div class="shimmer mb-2" style="height: 20px; width: 80%;"></div>
                    <div class="shimmer mb-2" style="height: 16px; width: 60%;"></div>
                    <div class="shimmer mb-3" style="height: 24px; width: 40%;"></div>
                    <div class="shimmer" style="height: 40px;"></div>
                </div>
            </div>
        </div>
    `;
    
    container.innerHTML = skeleton.repeat(8);
}

// Fonction pour l'effet parallax sur le hero
function initParallaxEffect() {
    const hero = document.querySelector('.hero-section');
    if (!hero) return;
    
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const rate = scrolled * -0.5;
        hero.style.transform = `translateY(${rate}px)`;
    });
}

// Initialisation spécifique à la mode
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les fonctionnalités mode
    initWishlistButtons();
    updateWishlistCount();
    initImageZoom();
    initProductGallery();
    initParallaxEffect();
    
    // Gestionnaire pour les boutons de taille
    document.querySelectorAll('.size-option').forEach(option => {
        option.addEventListener('click', function() {
            selectSize(this, this.textContent.trim());
        });
    });
    
    // Gestionnaire pour le guide des tailles
    document.querySelectorAll('.size-guide-btn').forEach(btn => {
        btn.addEventListener('click', showSizeGuide);
    });
    
    // Animation des éléments au scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-up');
            }
        });
    }, observerOptions);
    
    // Observer les éléments à animer
    document.querySelectorAll('.product-card, .category-card, .service-item, .contact-item').forEach(item => {
        observer.observe(item);
    });
});

// Fonction pour le mode sombre (optionnel)
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('dark-mode', document.body.classList.contains('dark-mode'));
}

// Charger le mode sombre si activé
if (localStorage.getItem('dark-mode') === 'true') {
    document.body.classList.add('dark-mode');
}