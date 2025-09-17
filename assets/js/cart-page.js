/**
 * JavaScript moderne pour la page panier
 * Gestion des quantités, suppression d'articles, codes promo, etc.
 */

document.addEventListener("DOMContentLoaded", function() {
    initializeCartPage();
});

function initializeCartPage() {
    setupQuantityControls();
    setupRemoveButtons();
    setupAutoUpdate();
    setupPromoCode();
    setupCartAnimations();
}

function setupQuantityControls() {
    // Boutons de quantité
    document.querySelectorAll(".quantity-btn").forEach(btn => {
        btn.addEventListener("click", function() {
            const input = this.parentElement.querySelector(".quantity-input");
            const isIncrease = this.classList.contains("quantity-increase");
            const currentValue = parseInt(input.value);
            
            if (isIncrease) {
                if (currentValue < parseInt(input.max)) {
                    input.value = currentValue + 1;
                }
            } else {
                if (currentValue > parseInt(input.min)) {
                    input.value = currentValue - 1;
                }
            }
            
            updateItemTotal(input);
            scheduleAutoUpdate();
        });
    });
    
    // Inputs de quantité
    document.querySelectorAll(".quantity-input").forEach(input => {
        input.addEventListener("change", function() {
            const value = parseInt(this.value);
            const min = parseInt(this.min);
            const max = parseInt(this.max);
            
            if (value < min) this.value = min;
            if (value > max) this.value = max;
            
            updateItemTotal(this);
            scheduleAutoUpdate();
        });
    });
}

function setupRemoveButtons() {
    document.querySelectorAll(".remove-item").forEach(btn => {
        btn.addEventListener("click", function() {
            const itemId = this.dataset.id;
            const itemName = this.dataset.name;
            
            if (confirm(`Supprimer "${itemName}" de votre panier ?`)) {
                removeItemFromCart(itemId);
            }
        });
    });
}

function removeItemFromCart(itemId) {
    const cartItem = document.querySelector(`[data-id="${itemId}"]`).closest(".cart-item");
    
    // Animation de suppression
    cartItem.style.transform = "translateX(-100%)";
    cartItem.style.opacity = "0";
    
    setTimeout(() => {
        window.location.href = `?action=remove&id=${itemId}`;
    }, 300);
}

function updateItemTotal(input) {
    const itemId = input.dataset.id;
    const price = parseFloat(input.dataset.price);
    const quantity = parseInt(input.value);
    const total = price * quantity;
    
    // Mettre à jour le total de l'item
    const totalElement = document.querySelector(`[data-id="${itemId}"].total-price`);
    if (totalElement) {
        totalElement.textContent = formatPrice(total);
        totalElement.classList.add("updated");
        setTimeout(() => totalElement.classList.remove("updated"), 1000);
    }
    
    // Mettre à jour le total général
    updateCartTotal();
}

function updateCartTotal() {
    let total = 0;
    let itemCount = 0;
    
    document.querySelectorAll(".quantity-input").forEach(input => {
        const price = parseFloat(input.dataset.price);
        const quantity = parseInt(input.value);
        total += price * quantity;
        itemCount += quantity;
    });
    
    // Mettre à jour les affichages
    const subtotalElement = document.getElementById("subtotal");
    const totalElement = document.getElementById("totalAmount");
    
    if (subtotalElement) {
        subtotalElement.textContent = formatPrice(total);
        subtotalElement.classList.add("updated");
        setTimeout(() => subtotalElement.classList.remove("updated"), 1000);
    }
    
    if (totalElement) {
        totalElement.textContent = formatPrice(total);
        totalElement.classList.add("updated");
        setTimeout(() => totalElement.classList.remove("updated"), 1000);
    }
}

let updateTimeout;
function scheduleAutoUpdate() {
    clearTimeout(updateTimeout);
    updateTimeout = setTimeout(() => {
        updateAllQuantities();
    }, 2000);
}

function setupAutoUpdate() {
    let saveIndicator = null;
    
    window.updateAllQuantities = function() {
        const form = document.getElementById("cartUpdateForm");
        if (!form) return;
        
        // Afficher un indicateur de sauvegarde
        if (!saveIndicator) {
            saveIndicator = document.createElement("div");
            saveIndicator.className = "save-indicator position-fixed top-0 start-50 translate-middle-x bg-primary text-white px-3 py-2 rounded-bottom";
            saveIndicator.innerHTML = `<i class="fas fa-sync fa-spin me-2"></i>Mise à jour...`;
            document.body.appendChild(saveIndicator);
        }
        
        // Soumettre le formulaire en AJAX
        const formData = new FormData(form);
        
        fetch(form.action, {
            method: "POST",
            body: formData
        })
        .then(response => {
            if (response.ok) {
                saveIndicator.innerHTML = `<i class="fas fa-check me-2"></i>Sauvegardé`;
                saveIndicator.classList.remove("bg-primary");
                saveIndicator.classList.add("bg-success");
                
                setTimeout(() => {
                    if (saveIndicator) {
                        saveIndicator.remove();
                        saveIndicator = null;
                    }
                }, 2000);
            }
        })
        .catch(error => {
            saveIndicator.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>Erreur`;
            saveIndicator.classList.remove("bg-primary");
            saveIndicator.classList.add("bg-danger");
        });
    };
}

function setupPromoCode() {
    window.togglePromoCode = function() {
        const form = document.getElementById("promoForm");
        const toggle = document.querySelector(".promo-toggle i.fa-chevron-down");
        
        if (form.style.display === "none") {
            form.style.display = "block";
            form.classList.add("animate-fade-in-up");
            toggle.style.transform = "rotate(180deg)";
        } else {
            form.style.display = "none";
            toggle.style.transform = "rotate(0deg)";
        }
    };
    
    window.applyPromoCode = function() {
        const codeInput = document.getElementById("promoCode");
        const code = codeInput.value.trim();
        
        if (!code) {
            showNotification("Veuillez saisir un code promo", "warning");
            return;
        }
        
        const button = event.target;
        const originalText = button.textContent;
        
        button.innerHTML = `<i class="fas fa-spinner fa-spin"></i>`;
        button.disabled = true;
        
        setTimeout(() => {
            // Simuler une réponse
            const validCodes = ["WELCOME10", "SAVE20", "FIRST15"];
            
            if (validCodes.includes(code.toUpperCase())) {
                showNotification("Code promo appliqué avec succès !", "success");
                button.innerHTML = `<i class="fas fa-check"></i>`;
                button.classList.remove("btn-outline-secondary");
                button.classList.add("btn-success");
                
                addPromoDiscount(code);
            } else {
                showNotification("Code promo invalide", "error");
                button.textContent = originalText;
                button.disabled = false;
            }
        }, 1500);
    };
}

function addPromoDiscount(code) {
    const costBreakdown = document.querySelector(".cost-breakdown");
    const discountAmount = 10;
    
    const discountItem = document.createElement("div");
    discountItem.className = "cost-item text-success";
    discountItem.innerHTML = `
        <span>Code promo ${code}</span>
        <span class="cost-value">-${discountAmount},00 €</span>
    `;
    
    costBreakdown.appendChild(discountItem);
    
    // Mettre à jour le total
    const currentTotal = parseFloat(document.getElementById("totalAmount").textContent.replace(",", "."));
    const newTotal = currentTotal - discountAmount;
    document.getElementById("totalAmount").textContent = formatPrice(newTotal);
}

function setupCartAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add("animate-fade-in-up");
            }
        });
    });
    
    document.querySelectorAll(".cart-item").forEach(item => {
        observer.observe(item);
    });
    
    document.querySelectorAll(".checkout-btn").forEach(btn => {
        btn.addEventListener("mouseenter", function() {
            this.classList.add("animate-pulse");
        });
        
        btn.addEventListener("mouseleave", function() {
            this.classList.remove("animate-pulse");
        });
    });
}

function formatPrice(price) {
    return new Intl.NumberFormat("fr-FR", {
        style: "currency",
        currency: "EUR"
    }).format(price);
}

function showNotification(message, type) {
    if (window.modernEcommerce && window.modernEcommerce.showNotification) {
        window.modernEcommerce.showNotification(message, type);
    } else {
        alert(message);
    }
}