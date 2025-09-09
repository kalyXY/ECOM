    <!-- Footer -->
    <footer class="bg-dark text-light py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-tshirt me-2"></i>
                        <?php echo htmlspecialchars($siteSettings['site_name']); ?>
                    </h5>
                    <p class="text-light-emphasis">
                        <?php echo htmlspecialchars($siteSettings['site_description']); ?>
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-light" title="Facebook">
                            <i class="fab fa-facebook-f fa-lg"></i>
                        </a>
                        <a href="#" class="text-light" title="Twitter">
                            <i class="fab fa-twitter fa-lg"></i>
                        </a>
                        <a href="#" class="text-light" title="Instagram">
                            <i class="fab fa-instagram fa-lg"></i>
                        </a>
                        <a href="#" class="text-light" title="LinkedIn">
                            <i class="fab fa-linkedin-in fa-lg"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Navigation</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="index.php" class="text-light-emphasis text-decoration-none">
                                <i class="fas fa-home me-1"></i>Accueil
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="products.php" class="text-light-emphasis text-decoration-none">
                                <i class="fas fa-tshirt me-1"></i>Collections
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="contact.php" class="text-light-emphasis text-decoration-none">
                                <i class="fas fa-envelope me-1"></i>Contact
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="cart.php" class="text-light-emphasis text-decoration-none">
                                <i class="fas fa-shopping-cart me-1"></i>Panier
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Informations</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="about.php" class="text-light-emphasis text-decoration-none">
                                <i class="fas fa-info-circle me-1"></i>À propos
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="careers.php" class="text-light-emphasis text-decoration-none">
                                <i class="fas fa-briefcase me-1"></i>Carrières
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="terms.php" class="text-light-emphasis text-decoration-none">
                                <i class="fas fa-file-contract me-1"></i>Conditions d'utilisation
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="privacy.php" class="text-light-emphasis text-decoration-none">
                                <i class="fas fa-shield-alt me-1"></i>Politique de confidentialité
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="text-light-emphasis text-decoration-none">
                                <i class="fas fa-truck me-1"></i>Livraison & Retours
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="text-light-emphasis text-decoration-none">
                                <i class="fas fa-ruler me-1"></i>Guide des tailles
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="col-lg-3 mb-4">
                    <h6 class="fw-bold mb-3">Contact</h6>
                    <div class="text-light-emphasis">
                        <div class="mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <?php echo htmlspecialchars($siteSettings['site_address']); ?>
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-phone me-2"></i>
                            <a href="tel:<?php echo htmlspecialchars($siteSettings['site_phone']); ?>" class="text-light-emphasis text-decoration-none">
                                <?php echo htmlspecialchars($siteSettings['site_phone']); ?>
                            </a>
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            <a href="mailto:<?php echo htmlspecialchars($siteSettings['site_email']); ?>" class="text-light-emphasis text-decoration-none">
                                <?php echo htmlspecialchars($siteSettings['site_email']); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-light-emphasis">
                        &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($siteSettings['site_name']); ?>. 
                        Tous droits réservés.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="d-flex justify-content-md-end gap-3">
                        <img src="https://via.placeholder.com/40x25/007bff/ffffff?text=VISA" alt="Visa" class="rounded">
                        <img src="https://via.placeholder.com/40x25/ff6b35/ffffff?text=MC" alt="Mastercard" class="rounded">
                        <img src="https://via.placeholder.com/40x25/00457c/ffffff?text=PP" alt="PayPal" class="rounded">
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bouton retour en haut -->
    <button id="backToTop" class="btn btn-primary position-fixed bottom-0 end-0 m-3" style="display: none; z-index: 1000;">
        <i class="fas fa-arrow-up"></i>
    </button>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>
    
    <!-- Modern E-commerce JS -->
    <script src="assets/js/modern-ecommerce.js"></script>
    
    <!-- Scripts spécifiques à la page -->
    <?php if (isset($pageScripts)): ?>
        <?php echo $pageScripts; ?>
    <?php endif; ?>
</body>
</html>