<?php
require_once 'includes/config.php';
$pageTitle = 'À propos de nous';
?>

<?php include 'includes/header.php'; ?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
            <li class="breadcrumb-item active">À propos</li>
        </ol>
    </div>
</nav>

<!-- Hero Section -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="fashion-title display-4 mb-4">Notre Histoire</h1>
                <p class="lead mb-4">
                    Depuis 2020, StyleHub révolutionne la mode en ligne en proposant des collections uniques 
                    qui allient élégance, qualité et accessibilité.
                </p>
                <div class="d-flex gap-3">
                    <div class="text-center">
                        <div class="h3 mb-0">50K+</div>
                        <small>Clients satisfaits</small>
                    </div>
                    <div class="text-center">
                        <div class="h3 mb-0">1000+</div>
                        <small>Produits</small>
                    </div>
                    <div class="text-center">
                        <div class="h3 mb-0">15</div>
                        <small>Pays</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                     alt="Notre équipe" class="img-fluid rounded shadow-lg">
            </div>
        </div>
    </div>
</section>

<!-- Notre Mission -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fashion-title mb-3">Notre Mission</h2>
            <div class="fashion-divider"></div>
            <p class="text-muted">Rendre la mode accessible à tous, partout dans le monde</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-heart fa-2x"></i>
                    </div>
                    <h4 class="fashion-title">Passion</h4>
                    <p class="text-muted">
                        Nous sommes passionnés par la mode et nous nous efforçons de partager cette passion 
                        avec nos clients à travers chaque produit que nous proposons.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-gem fa-2x"></i>
                    </div>
                    <h4 class="fashion-title">Qualité</h4>
                    <p class="text-muted">
                        Chaque article est soigneusement sélectionné pour sa qualité exceptionnelle. 
                        Nous travaillons uniquement avec des marques et des créateurs de confiance.
                    </p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-globe fa-2x"></i>
                    </div>
                    <h4 class="fashion-title">Accessibilité</h4>
                    <p class="text-muted">
                        Nous croyons que la mode doit être accessible à tous. C'est pourquoi nous proposons 
                        des prix justes et une livraison dans le monde entier.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Notre Équipe -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fashion-title mb-3">Notre Équipe</h2>
            <div class="fashion-divider"></div>
            <p class="text-muted">Les visages derrière StyleHub</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <img src="https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" 
                         class="card-img-top" alt="Sophie Martin" style="height: 300px; object-fit: cover;">
                    <div class="card-body text-center">
                        <h5 class="fashion-title">Sophie Martin</h5>
                        <p class="text-primary mb-2">Fondatrice & CEO</p>
                        <p class="text-muted small">
                            Passionnée de mode depuis toujours, Sophie a créé StyleHub pour démocratiser 
                            l'accès à la mode de qualité.
                        </p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="#" class="btn btn-outline-primary btn-sm rounded-circle">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="#" class="btn btn-outline-primary btn-sm rounded-circle">
                                <i class="fab fa-twitter"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" 
                         class="card-img-top" alt="Thomas Dubois" style="height: 300px; object-fit: cover;">
                    <div class="card-body text-center">
                        <h5 class="fashion-title">Thomas Dubois</h5>
                        <p class="text-primary mb-2">Directeur Technique</p>
                        <p class="text-muted small">
                            Expert en e-commerce, Thomas supervise le développement de notre plateforme 
                            pour offrir la meilleure expérience utilisateur.
                        </p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="#" class="btn btn-outline-primary btn-sm rounded-circle">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="#" class="btn btn-outline-primary btn-sm rounded-circle">
                                <i class="fab fa-github"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" 
                         class="card-img-top" alt="Emma Leroy" style="height: 300px; object-fit: cover;">
                    <div class="card-body text-center">
                        <h5 class="fashion-title">Emma Leroy</h5>
                        <p class="text-primary mb-2">Directrice Artistique</p>
                        <p class="text-muted small">
                            Styliste de formation, Emma sélectionne avec soin chaque pièce de nos collections 
                            pour créer des looks tendance et intemporels.
                        </p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="#" class="btn btn-outline-primary btn-sm rounded-circle">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="btn btn-outline-primary btn-sm rounded-circle">
                                <i class="fab fa-pinterest"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" 
                         class="card-img-top" alt="Lucas Moreau" style="height: 300px; object-fit: cover;">
                    <div class="card-body text-center">
                        <h5 class="fashion-title">Lucas Moreau</h5>
                        <p class="text-primary mb-2">Responsable Logistique</p>
                        <p class="text-muted small">
                            Lucas s'assure que vos commandes arrivent rapidement et en parfait état. 
                            Il supervise notre réseau logistique international.
                        </p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="#" class="btn btn-outline-primary btn-sm rounded-circle">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="#" class="btn btn-outline-primary btn-sm rounded-circle">
                                <i class="fab fa-twitter"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Nos Valeurs -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fashion-title mb-3">Nos Valeurs</h2>
            <div class="fashion-divider"></div>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="d-flex align-items-start">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px; flex-shrink: 0;">
                        <i class="fas fa-leaf fa-lg"></i>
                    </div>
                    <div>
                        <h4 class="fashion-title">Durabilité</h4>
                        <p class="text-muted">
                            Nous nous engageons pour une mode plus responsable en privilégiant les matières 
                            durables et les processus de production éthiques. Notre objectif est de réduire 
                            notre impact environnemental tout en proposant des produits de qualité.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="d-flex align-items-start">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px; flex-shrink: 0;">
                        <i class="fas fa-handshake fa-lg"></i>
                    </div>
                    <div>
                        <h4 class="fashion-title">Éthique</h4>
                        <p class="text-muted">
                            Nous travaillons exclusivement avec des partenaires qui respectent les droits 
                            des travailleurs et les normes sociales. Chaque produit est fabriqué dans des 
                            conditions de travail équitables et respectueuses.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="d-flex align-items-start">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px; flex-shrink: 0;">
                        <i class="fas fa-users fa-lg"></i>
                    </div>
                    <div>
                        <h4 class="fashion-title">Inclusivité</h4>
                        <p class="text-muted">
                            La mode est pour tous, sans exception. Nous proposons une large gamme de tailles 
                            et de styles pour que chacun puisse exprimer sa personnalité et se sentir bien 
                            dans ses vêtements, peu importe son âge, sa morphologie ou son style.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="d-flex align-items-start">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px; flex-shrink: 0;">
                        <i class="fas fa-lightbulb fa-lg"></i>
                    </div>
                    <div>
                        <h4 class="fashion-title">Innovation</h4>
                        <p class="text-muted">
                            Nous investissons constamment dans les nouvelles technologies pour améliorer 
                            votre expérience d'achat. De la réalité augmentée pour essayer virtuellement 
                            nos produits aux algorithmes de recommandation personnalisée.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Timeline -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fashion-title mb-3">Notre Parcours</h2>
            <div class="fashion-divider"></div>
        </div>
        
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h5 class="fashion-title">2020 - Les Débuts</h5>
                            <p class="text-muted">
                                Création de StyleHub avec une vision simple : rendre la mode accessible à tous. 
                                Lancement avec 50 produits soigneusement sélectionnés.
                            </p>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h5 class="fashion-title">2021 - Expansion</h5>
                            <p class="text-muted">
                                Ouverture à l'international et partenariat avec 20 marques émergentes. 
                                Lancement de notre première collection exclusive.
                            </p>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h5 class="fashion-title">2022 - Innovation</h5>
                            <p class="text-muted">
                                Lancement de notre application mobile et intégration de la réalité augmentée 
                                pour l'essayage virtuel. 10 000 clients satisfaits.
                            </p>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h5 class="fashion-title">2023 - Durabilité</h5>
                            <p class="text-muted">
                                Lancement de notre programme de mode durable avec des matières recyclées 
                                et des emballages éco-responsables.
                            </p>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-marker bg-accent"></div>
                        <div class="timeline-content">
                            <h5 class="fashion-title">2024 - Aujourd'hui</h5>
                            <p class="text-muted">
                                Plus de 50 000 clients dans 15 pays, 1000+ produits en catalogue et une 
                                équipe passionnée de 25 personnes. L'aventure continue !
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="fashion-title mb-3">Rejoignez l'Aventure StyleHub</h2>
        <p class="lead mb-4">
            Découvrez nos dernières collections et bénéficiez d'offres exclusives
        </p>
        <div class="d-flex justify-content-center gap-3">
            <a href="products.php" class="btn btn-light btn-lg">
                <i class="fas fa-tshirt me-2"></i>Découvrir nos Collections
            </a>
            <a href="contact.php" class="btn btn-outline-light btn-lg">
                <i class="fas fa-envelope me-2"></i>Nous Contacter
            </a>
        </div>
    </div>
</section>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--primary-color);
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 0 0 3px var(--primary-color);
}

.timeline-content {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
</style>

<?php include 'includes/footer.php'; ?>