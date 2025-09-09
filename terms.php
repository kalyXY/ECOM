<?php
require_once 'includes/config.php';
$pageTitle = 'Conditions d\'utilisation';
?>

<?php include 'includes/header.php'; ?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
            <li class="breadcrumb-item active">Conditions d'utilisation</li>
        </ol>
    </div>
</nav>

<!-- Header -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="text-center">
            <h1 class="fashion-title display-5 mb-3">Conditions Générales d'Utilisation</h1>
            <p class="lead">Dernière mise à jour : 9 septembre 2024</p>
        </div>
    </div>
</section>

<!-- Contenu -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Sommaire -->
            <div class="col-lg-3">
                <div class="sticky-top" style="top: 100px;">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-list me-2"></i>Sommaire</h6>
                        </div>
                        <div class="card-body p-0">
                            <nav class="nav nav-pills flex-column">
                                <a class="nav-link" href="#article1">1. Objet</a>
                                <a class="nav-link" href="#article2">2. Acceptation</a>
                                <a class="nav-link" href="#article3">3. Accès au site</a>
                                <a class="nav-link" href="#article4">4. Commandes</a>
                                <a class="nav-link" href="#article5">5. Prix et paiement</a>
                                <a class="nav-link" href="#article6">6. Livraison</a>
                                <a class="nav-link" href="#article7">7. Droit de rétractation</a>
                                <a class="nav-link" href="#article8">8. Garanties</a>
                                <a class="nav-link" href="#article9">9. Responsabilité</a>
                                <a class="nav-link" href="#article10">10. Propriété intellectuelle</a>
                                <a class="nav-link" href="#article11">11. Données personnelles</a>
                                <a class="nav-link" href="#article12">12. Droit applicable</a>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contenu principal -->
            <div class="col-lg-9">
                <div class="terms-content">
                    
                    <!-- Article 1 -->
                    <article id="article1" class="mb-5">
                        <h2 class="fashion-title h3 mb-3">1. Objet</h2>
                        <p>
                            Les présentes conditions générales d'utilisation (ci-après "CGU") régissent l'utilisation 
                            du site web StyleHub accessible à l'adresse <strong>www.stylehub.fr</strong> (ci-après le "Site").
                        </p>
                        <p>
                            Le Site est édité par la société <strong>StyleHub SAS</strong>, société par actions simplifiée 
                            au capital de 100 000 euros, immatriculée au RCS de Paris sous le numéro 123 456 789, 
                            dont le siège social est situé au 25 Avenue des Champs-Élysées, 75008 Paris.
                        </p>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Contact :</strong> Pour toute question relative aux présentes CGU, 
                            vous pouvez nous contacter à l'adresse <a href="mailto:legal@stylehub.fr">legal@stylehub.fr</a>
                        </div>
                    </article>

                    <!-- Article 2 -->
                    <article id="article2" class="mb-5">
                        <h2 class="fashion-title h3 mb-3">2. Acceptation des conditions</h2>
                        <p>
                            L'utilisation du Site implique l'acceptation pleine et entière des présentes CGU. 
                            Si vous n'acceptez pas ces conditions, nous vous invitons à ne pas utiliser le Site.
                        </p>
                        <p>
                            StyleHub se réserve le droit de modifier les présentes CGU à tout moment. 
                            Les modifications entrent en vigueur dès leur publication sur le Site.
                        </p>
                    </article>

                    <!-- Article 3 -->
                    <article id="article3" class="mb-5">
                        <h2 class="fashion-title h3 mb-3">3. Accès au site</h2>
                        <p>
                            Le Site est accessible gratuitement à tout utilisateur disposant d'un accès à Internet. 
                            Tous les frais supportés par l'utilisateur pour accéder au service sont à sa charge.
                        </p>
                        <div class="bg-light p-3 rounded">
                            <h5>Utilisation autorisée</h5>
                            <ul>
                                <li>Consultation des produits et services</li>
                                <li>Passation de commandes</li>
                                <li>Création d'un compte client</li>
                                <li>Utilisation des fonctionnalités interactives</li>
                            </ul>
                        </div>
                    </article>

                    <!-- Article 4 -->
                    <article id="article4" class="mb-5">
                        <h2 class="fashion-title h3 mb-3">4. Commandes</h2>
                        <p>
                            Les commandes peuvent être passées directement sur le Site en suivant le processus 
                            de commande en ligne. Toute commande vaut acceptation des prix et descriptions 
                            des produits disponibles à la vente.
                        </p>
                        
                        <h4 class="h5 mt-4">4.1 Processus de commande</h4>
                        <ol>
                            <li>Sélection des produits et ajout au panier</li>
                            <li>Vérification du contenu du panier</li>
                            <li>Identification ou création d'un compte</li>
                            <li>Choix de l'adresse de livraison</li>
                            <li>Choix du mode de livraison</li>
                            <li>Choix du mode de paiement</li>
                            <li>Vérification et validation de la commande</li>
                        </ol>
                    </article>

                    <!-- Article 5 -->
                    <article id="article5" class="mb-5">
                        <h2 class="fashion-title h3 mb-3">5. Prix et paiement</h2>
                        
                        <h4 class="h5">5.1 Prix</h4>
                        <p>
                            Les prix des produits sont indiqués en euros toutes taxes comprises (TTC), 
                            hors frais de livraison.
                        </p>
                        
                        <h4 class="h5 mt-4">5.2 Modes de paiement</h4>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6><i class="fas fa-credit-card me-2 text-primary"></i>Cartes bancaires</h6>
                                        <p class="small mb-0">Visa, Mastercard, American Express</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6><i class="fab fa-paypal me-2 text-primary"></i>PayPal</h6>
                                        <p class="small mb-0">Paiement sécurisé via PayPal</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>

                    <!-- Article 6 -->
                    <article id="article6" class="mb-5">
                        <h2 class="fashion-title h3 mb-3">6. Livraison</h2>
                        
                        <h4 class="h5">6.1 Zones de livraison</h4>
                        <p>
                            Nous livrons en France métropolitaine et dans plusieurs pays européens.
                        </p>
                        
                        <h4 class="h5 mt-4">6.2 Délais de livraison</h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Zone</th>
                                        <th>Mode de livraison</th>
                                        <th>Délai</th>
                                        <th>Prix</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>France</td>
                                        <td>Standard</td>
                                        <td>2-3 jours ouvrés</td>
                                        <td>Gratuit dès 50€</td>
                                    </tr>
                                    <tr>
                                        <td>France</td>
                                        <td>Express</td>
                                        <td>24h</td>
                                        <td>9,90€</td>
                                    </tr>
                                    <tr>
                                        <td>Europe</td>
                                        <td>Standard</td>
                                        <td>5-7 jours ouvrés</td>
                                        <td>12,90€</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </article>

                    <!-- Article 7 -->
                    <article id="article7" class="mb-5">
                        <h2 class="fashion-title h3 mb-3">7. Droit de rétractation</h2>
                        <p>
                            Vous disposez d'un délai de <strong>14 jours</strong> à compter de la réception 
                            de votre commande pour exercer votre droit de rétractation.
                        </p>
                        
                        <h4 class="h5 mt-4">7.1 Conditions de retour</h4>
                        <ul>
                            <li>Les articles doivent être retournés dans leur état d'origine</li>
                            <li>Les étiquettes doivent être présentes</li>
                            <li>Les articles ne doivent pas avoir été portés</li>
                        </ul>
                        
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Remboursement :</strong> Nous procédons au remboursement dans les 14 jours 
                            suivant la réception de votre retour.
                        </div>
                    </article>

                    <!-- Contact -->
                    <div class="bg-primary text-white p-4 rounded mt-5">
                        <h3 class="fashion-title h4 mb-3">
                            <i class="fas fa-question-circle me-2"></i>Questions ?
                        </h3>
                        <p class="mb-3">
                            Si vous avez des questions concernant ces conditions d'utilisation, 
                            n'hésitez pas à nous contacter.
                        </p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-envelope me-2"></i>
                                    <a href="mailto:legal@stylehub.fr" class="text-white">legal@stylehub.fr</a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-phone me-2"></i>
                                    <a href="tel:+33142869573" class="text-white">01 42 86 95 73</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.terms-content h2 {
    color: var(--primary-color);
    border-bottom: 2px solid var(--accent-color);
    padding-bottom: 10px;
}

.nav-pills .nav-link {
    color: var(--text-color);
    border-radius: 0;
    border-left: 3px solid transparent;
    padding: 8px 15px;
}

.nav-pills .nav-link:hover,
.nav-pills .nav-link.active {
    background-color: var(--fashion-pink);
    color: var(--primary-color);
    border-left-color: var(--primary-color);
}

.terms-content article {
    scroll-margin-top: 100px;
}
</style>

<?php include 'includes/footer.php'; ?>