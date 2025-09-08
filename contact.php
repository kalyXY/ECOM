<?php
require_once 'includes/config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $content = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $subject === '' || $content === '') {
        $error = 'Veuillez remplir tous les champs.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide.";
    } else {
        try {
            $stmt = $pdo->prepare('CREATE TABLE IF NOT EXISTS messages (
                id INT AUTO_INCREMENT PRIMARY KEY, 
                name VARCHAR(100) NOT NULL, 
                email VARCHAR(150) NOT NULL, 
                subject VARCHAR(200) NOT NULL,
                message TEXT NOT NULL, 
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
            $stmt->execute();
            
            $ins = $pdo->prepare('INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)');
            $ins->execute([$name, $email, $subject, $content]);
            $message = 'Votre message a été envoyé avec succès. Notre équipe vous répondra dans les plus brefs délais.';
        } catch (Throwable $e) {
            $error = "Impossible d'envoyer votre message pour le moment. Veuillez réessayer plus tard.";
        }
    }
}

$pageTitle = 'Contactez-nous';
?>

<?php include 'includes/header.php'; ?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
            <li class="breadcrumb-item active">Contact</li>
        </ol>
    </div>
</nav>

<!-- Contact Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="fashion-title mb-3">Contactez-nous</h1>
            <div class="fashion-divider"></div>
            <p class="text-muted">Notre équipe est à votre écoute pour répondre à toutes vos questions</p>
        </div>

        <div class="row g-5">
            <!-- Informations de contact -->
            <div class="col-lg-4">
                <div class="contact-info">
                    <h3 class="fashion-title mb-4">Nos Coordonnées</h3>
                    
                    <div class="contact-item mb-4">
                        <div class="d-flex align-items-start">
                            <div class="contact-icon me-3">
                                <i class="fas fa-map-marker-alt fa-lg text-primary"></i>
                            </div>
                            <div>
                                <h5>Adresse</h5>
                                <p class="text-muted mb-0">25 Avenue des Champs-Élysées<br>75008 Paris, France</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="contact-item mb-4">
                        <div class="d-flex align-items-start">
                            <div class="contact-icon me-3">
                                <i class="fas fa-phone fa-lg text-primary"></i>
                            </div>
                            <div>
                                <h5>Téléphone</h5>
                                <p class="text-muted mb-0">
                                    <a href="tel:+33142869573" class="text-decoration-none">01 42 86 95 73</a>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="contact-item mb-4">
                        <div class="d-flex align-items-start">
                            <div class="contact-icon me-3">
                                <i class="fas fa-envelope fa-lg text-primary"></i>
                            </div>
                            <div>
                                <h5>Email</h5>
                                <p class="text-muted mb-0">
                                    <a href="mailto:contact@stylehub.fr" class="text-decoration-none">contact@stylehub.fr</a>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="contact-item mb-4">
                        <div class="d-flex align-items-start">
                            <div class="contact-icon me-3">
                                <i class="fas fa-clock fa-lg text-primary"></i>
                            </div>
                            <div>
                                <h5>Horaires</h5>
                                <p class="text-muted mb-0">
                                    Lun - Ven: 9h00 - 19h00<br>
                                    Sam: 10h00 - 18h00<br>
                                    Dim: Fermé
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Réseaux sociaux -->
                    <div class="social-links mt-4">
                        <h5 class="mb-3">Suivez-nous</h5>
                        <div class="d-flex gap-3">
                            <a href="#" class="btn btn-outline-primary btn-sm rounded-circle" style="width: 40px; height: 40px;">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="btn btn-outline-primary btn-sm rounded-circle" style="width: 40px; height: 40px;">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="btn btn-outline-primary btn-sm rounded-circle" style="width: 40px; height: 40px;">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="btn btn-outline-primary btn-sm rounded-circle" style="width: 40px; height: 40px;">
                                <i class="fab fa-pinterest"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Formulaire de contact -->
            <div class="col-lg-8">
                <div class="contact-form">
                    <h3 class="fashion-title mb-4">Envoyez-nous un message</h3>
                    
                    <?php if ($message): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="needs-validation" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Nom complet *</label>
                                <input type="text" id="name" name="name" class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                                <div class="invalid-feedback">
                                    Veuillez saisir votre nom complet.
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="email" class="form-label">Adresse email *</label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                <div class="invalid-feedback">
                                    Veuillez saisir une adresse email valide.
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <label for="subject" class="form-label">Sujet *</label>
                                <select id="subject" name="subject" class="form-select" required>
                                    <option value="">Choisissez un sujet</option>
                                    <option value="Commande" <?php echo ($_POST['subject'] ?? '') == 'Commande' ? 'selected' : ''; ?>>Question sur une commande</option>
                                    <option value="Produit" <?php echo ($_POST['subject'] ?? '') == 'Produit' ? 'selected' : ''; ?>>Information produit</option>
                                    <option value="Livraison" <?php echo ($_POST['subject'] ?? '') == 'Livraison' ? 'selected' : ''; ?>>Livraison & Retours</option>
                                    <option value="Taille" <?php echo ($_POST['subject'] ?? '') == 'Taille' ? 'selected' : ''; ?>>Guide des tailles</option>
                                    <option value="Partenariat" <?php echo ($_POST['subject'] ?? '') == 'Partenariat' ? 'selected' : ''; ?>>Partenariat</option>
                                    <option value="Autre" <?php echo ($_POST['subject'] ?? '') == 'Autre' ? 'selected' : ''; ?>>Autre</option>
                                </select>
                                <div class="invalid-feedback">
                                    Veuillez choisir un sujet.
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <label for="message" class="form-label">Message *</label>
                                <textarea id="message" name="message" class="form-control" rows="6" 
                                          placeholder="Décrivez votre demande en détail..." required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                                <div class="invalid-feedback">
                                    Veuillez écrire votre message.
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Envoyer le Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fashion-title mb-3">Questions Fréquentes</h2>
            <div class="fashion-divider"></div>
        </div>
        
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                <i class="fas fa-shipping-fast me-2"></i>Quels sont vos délais de livraison ?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Nous livrons en 2-3 jours ouvrés en France métropolitaine. La livraison est gratuite dès 50€ d'achat.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                <i class="fas fa-undo me-2"></i>Comment effectuer un retour ?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Vous avez 30 jours pour retourner vos articles. Les retours sont gratuits et peuvent être effectués en boutique ou par colis.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                <i class="fas fa-ruler me-2"></i>Comment choisir ma taille ?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Consultez notre guide des tailles disponible sur chaque fiche produit. En cas de doute, n'hésitez pas à nous contacter.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                <i class="fas fa-credit-card me-2"></i>Quels moyens de paiement acceptez-vous ?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Nous acceptons les cartes bancaires (Visa, Mastercard), PayPal et les virements bancaires. Tous les paiements sont sécurisés.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
// Validation du formulaire
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>