// JavaScript pour StyleHub - Boutique de Mode

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