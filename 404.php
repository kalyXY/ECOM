<?php
require_once 'includes/config.php';
$pageTitle = 'Page non trouvée';
http_response_code(404);
?>

<?php include 'includes/header.php'; ?>

<!-- 404 Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <!-- 404 Illustration -->
                <div class="mb-5">
                    <div class="display-1 text-primary mb-3" style="font-size: 8rem; font-weight: 700;">
                        404
                    </div>
                    <h1 class="fashion-title h2 mb-3">Oups ! Page non trouvée</h1>
                    <p class="lead text-muted mb-4">
                        La page que vous recherchez semble avoir disparu de notre dressing. 
                        Mais ne vous inquiétez pas, nous avons plein d'autres merveilles à vous montrer !
                    </p>
                </div>

                <!-- Fashion illustration -->
                <div class="mb-5">
                    <i class="fas fa-tshirt text-muted" style="font-size: 6rem; opacity: 0.3;"></i>
                </div>

                <!-- Actions -->
                <div class="d-flex flex-column flex-md-row gap-3 justify-content-center mb-5">
                    <a href="index.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-home me-2"></i>Retour à l'Accueil
                    </a>
                    <a href="products.php" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-tshirt me-2"></i>Découvrir nos Collections
                    </a>
                </div>

                <!-- Search -->
                <div class="mb-5">
                    <h4 class="fashion-title mb-3">Ou recherchez ce que vous cherchez</h4>
                    <form method="GET" action="products.php" class="d-flex justify-content-center">
                        <div class="input-group" style="max-width: 400px;">
                            <input type="search" name="search" class="form-control form-control-lg" 
                                   placeholder="Rechercher un article...">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Popular links -->
                <div class="row g-3">
                    <div class="col-12">
                        <h5 class="fashion-title mb-3">Pages populaires</h5>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="products.php?gender=femme" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-female me-1"></i>Femme
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="products.php?gender=homme" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-male me-1"></i>Homme
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="products.php?category=accessoires" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-gem me-1"></i>Accessoires
                        </a>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="contact.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-envelope me-1"></i>Contact
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Help Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h3 class="fashion-title mb-4">Besoin d'aide ?</h3>
                <p class="text-muted mb-4">
                    Notre équipe est là pour vous aider à trouver ce que vous cherchez
                </p>
                
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center p-4">
                                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                    <i class="fas fa-headset fa-lg"></i>
                                </div>
                                <h5 class="fashion-title">Support Client</h5>
                                <p class="text-muted small mb-3">
                                    Notre équipe est disponible pour répondre à toutes vos questions
                                </p>
                                <a href="contact.php" class="btn btn-outline-primary btn-sm">
                                    Nous contacter
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center p-4">
                                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                    <i class="fas fa-question-circle fa-lg"></i>
                                </div>
                                <h5 class="fashion-title">FAQ</h5>
                                <p class="text-muted small mb-3">
                                    Consultez nos questions fréquemment posées
                                </p>
                                <a href="contact.php#faq" class="btn btn-outline-primary btn-sm">
                                    Voir la FAQ
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center p-4">
                                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                    <i class="fas fa-ruler fa-lg"></i>
                                </div>
                                <h5 class="fashion-title">Guide des Tailles</h5>
                                <p class="text-muted small mb-3">
                                    Trouvez la taille parfaite avec notre guide
                                </p>
                                <button class="btn btn-outline-primary btn-sm size-guide-btn">
                                    Voir le guide
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.display-1 {
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

@media (max-width: 768px) {
    .display-1 {
        font-size: 5rem !important;
    }
}
</style>

<?php include 'includes/footer.php'; ?>