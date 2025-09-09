<?php
require_once 'includes/config.php';
$pageTitle = 'Carrières';
?>

<?php include 'includes/header.php'; ?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
            <li class="breadcrumb-item active">Carrières</li>
        </ol>
    </div>
</nav>

<!-- Hero Section -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="fashion-title display-4 mb-4">Rejoignez Notre Équipe</h1>
                <p class="lead mb-4">
                    Chez StyleHub, nous croyons que les meilleures idées naissent de la diversité et de la passion. 
                    Rejoignez une équipe dynamique qui révolutionne l'industrie de la mode.
                </p>
                <a href="#jobs" class="btn btn-light btn-lg">
                    <i class="fas fa-briefcase me-2"></i>Voir les Offres
                </a>
            </div>
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                     alt="Équipe StyleHub" class="img-fluid rounded shadow-lg">
            </div>
        </div>
    </div>
</section>

<!-- Pourquoi nous rejoindre -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fashion-title mb-3">Pourquoi Choisir StyleHub ?</h2>
            <div class="fashion-divider"></div>
            <p class="text-muted">Découvrez ce qui fait de StyleHub un employeur d'exception</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100 text-center p-4">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-rocket fa-2x"></i>
                    </div>
                    <h4 class="fashion-title">Innovation</h4>
                    <p class="text-muted">
                        Travaillez avec les dernières technologies et participez à l'innovation 
                        dans le secteur de la mode en ligne.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100 text-center p-4">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                    <h4 class="fashion-title">Équipe</h4>
                    <p class="text-muted">
                        Rejoignez une équipe passionnée, diverse et collaborative où chaque voix compte 
                        et chaque idée est valorisée.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100 text-center p-4">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                    <h4 class="fashion-title">Croissance</h4>
                    <p class="text-muted">
                        Évoluez dans une entreprise en pleine croissance avec de nombreuses opportunités 
                        de développement professionnel.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100 text-center p-4">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-balance-scale fa-2x"></i>
                    </div>
                    <h4 class="fashion-title">Équilibre</h4>
                    <p class="text-muted">
                        Profitez d'un excellent équilibre vie professionnelle/vie privée avec 
                        télétravail flexible et horaires adaptés.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100 text-center p-4">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-graduation-cap fa-2x"></i>
                    </div>
                    <h4 class="fashion-title">Formation</h4>
                    <p class="text-muted">
                        Bénéficiez de formations continues, de conférences et d'un budget 
                        développement personnel pour faire évoluer vos compétences.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100 text-center p-4">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-gift fa-2x"></i>
                    </div>
                    <h4 class="fashion-title">Avantages</h4>
                    <p class="text-muted">
                        Profitez d'avantages exclusifs : remises employés, mutuelle premium, 
                        tickets restaurant et bien plus encore.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Nos Valeurs -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fashion-title mb-3">Nos Valeurs d'Entreprise</h2>
            <div class="fashion-divider"></div>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                        <i class="fas fa-heart fa-3x"></i>
                    </div>
                    <h4 class="fashion-title">Passion</h4>
                    <p class="text-muted">
                        Nous sommes passionnés par ce que nous faisons et nous nous efforçons 
                        d'exceller dans chaque projet.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                        <i class="fas fa-handshake fa-3x"></i>
                    </div>
                    <h4 class="fashion-title">Respect</h4>
                    <p class="text-muted">
                        Le respect mutuel est la base de toutes nos interactions, 
                        que ce soit avec nos collègues ou nos clients.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                        <i class="fas fa-lightbulb fa-3x"></i>
                    </div>
                    <h4 class="fashion-title">Créativité</h4>
                    <p class="text-muted">
                        Nous encourageons la créativité et l'innovation pour repousser 
                        les limites de ce qui est possible.
                    </p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                        <i class="fas fa-trophy fa-3x"></i>
                    </div>
                    <h4 class="fashion-title">Excellence</h4>
                    <p class="text-muted">
                        Nous visons l'excellence dans tout ce que nous faisons, 
                        de nos produits à notre service client.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Offres d'emploi -->
<section class="py-5" id="jobs">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fashion-title mb-3">Offres d'Emploi</h2>
            <div class="fashion-divider"></div>
            <p class="text-muted">Découvrez nos opportunités actuelles</p>
        </div>
        
        <div class="row g-4">
            <!-- Développeur Full-Stack -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h4 class="fashion-title">Développeur Full-Stack</h4>
                                <p class="text-primary mb-0">
                                    <i class="fas fa-map-marker-alt me-1"></i>Paris / Remote
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-clock me-1"></i>CDI
                                </p>
                            </div>
                            <span class="badge bg-success">Nouveau</span>
                        </div>
                        
                        <p class="text-muted mb-3">
                            Rejoignez notre équipe technique pour développer et maintenir notre plateforme e-commerce. 
                            Vous travaillerez sur des technologies modernes (PHP, JavaScript, MySQL) dans un environnement agile.
                        </p>
                        
                        <div class="mb-3">
                            <h6>Compétences requises :</h6>
                            <div class="d-flex flex-wrap gap-1">
                                <span class="badge bg-light text-dark">PHP</span>
                                <span class="badge bg-light text-dark">JavaScript</span>
                                <span class="badge bg-light text-dark">MySQL</span>
                                <span class="badge bg-light text-dark">Bootstrap</span>
                                <span class="badge bg-light text-dark">Git</span>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                <i class="fas fa-euro-sign me-1"></i>45K - 60K €
                            </div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#applyModal" data-job="Développeur Full-Stack">
                                <i class="fas fa-paper-plane me-1"></i>Postuler
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- UX/UI Designer -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h4 class="fashion-title">UX/UI Designer</h4>
                                <p class="text-primary mb-0">
                                    <i class="fas fa-map-marker-alt me-1"></i>Paris / Remote
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-clock me-1"></i>CDI
                                </p>
                            </div>
                            <span class="badge bg-primary">Urgent</span>
                        </div>
                        
                        <p class="text-muted mb-3">
                            Créez des expériences utilisateur exceptionnelles pour notre plateforme mode. 
                            Vous concevrez des interfaces intuitives et esthétiques qui reflètent notre identité de marque.
                        </p>
                        
                        <div class="mb-3">
                            <h6>Compétences requises :</h6>
                            <div class="d-flex flex-wrap gap-1">
                                <span class="badge bg-light text-dark">Figma</span>
                                <span class="badge bg-light text-dark">Adobe XD</span>
                                <span class="badge bg-light text-dark">Photoshop</span>
                                <span class="badge bg-light text-dark">HTML/CSS</span>
                                <span class="badge bg-light text-dark">Prototyping</span>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                <i class="fas fa-euro-sign me-1"></i>40K - 55K €
                            </div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#applyModal" data-job="UX/UI Designer">
                                <i class="fas fa-paper-plane me-1"></i>Postuler
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Marketing Manager -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h4 class="fashion-title">Marketing Manager</h4>
                                <p class="text-primary mb-0">
                                    <i class="fas fa-map-marker-alt me-1"></i>Paris
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-clock me-1"></i>CDI
                                </p>
                            </div>
                        </div>
                        
                        <p class="text-muted mb-3">
                            Développez et exécutez notre stratégie marketing digital. Vous piloterez les campagnes 
                            publicitaires, les réseaux sociaux et les partenariats influenceurs.
                        </p>
                        
                        <div class="mb-3">
                            <h6>Compétences requises :</h6>
                            <div class="d-flex flex-wrap gap-1">
                                <span class="badge bg-light text-dark">Google Ads</span>
                                <span class="badge bg-light text-dark">Facebook Ads</span>
                                <span class="badge bg-light text-dark">SEO/SEA</span>
                                <span class="badge bg-light text-dark">Analytics</span>
                                <span class="badge bg-light text-dark">Social Media</span>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                <i class="fas fa-euro-sign me-1"></i>50K - 65K €
                            </div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#applyModal" data-job="Marketing Manager">
                                <i class="fas fa-paper-plane me-1"></i>Postuler
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Customer Success Manager -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h4 class="fashion-title">Customer Success Manager</h4>
                                <p class="text-primary mb-0">
                                    <i class="fas fa-map-marker-alt me-1"></i>Paris / Remote
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-clock me-1"></i>CDI
                                </p>
                            </div>
                        </div>
                        
                        <p class="text-muted mb-3">
                            Assurez la satisfaction de nos clients en gérant leur parcours post-achat. 
                            Vous développerez des stratégies de fidélisation et optimiserez l'expérience client.
                        </p>
                        
                        <div class="mb-3">
                            <h6>Compétences requises :</h6>
                            <div class="d-flex flex-wrap gap-1">
                                <span class="badge bg-light text-dark">CRM</span>
                                <span class="badge bg-light text-dark">Communication</span>
                                <span class="badge bg-light text-dark">Analyse</span>
                                <span class="badge bg-light text-dark">Empathie</span>
                                <span class="badge bg-light text-dark">Anglais</span>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                <i class="fas fa-euro-sign me-1"></i>35K - 45K €
                            </div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#applyModal" data-job="Customer Success Manager">
                                <i class="fas fa-paper-plane me-1"></i>Postuler
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-5">
            <p class="text-muted mb-3">Vous ne trouvez pas le poste qui vous correspond ?</p>
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#spontaneousModal">
                <i class="fas fa-envelope me-2"></i>Candidature Spontanée
            </button>
        </div>
    </div>
</section>

<!-- Modal de candidature -->
<div class="modal fade" id="applyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Postuler pour : <span id="jobTitle"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="applicationForm" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="firstName" class="form-label">Prénom *</label>
                            <input type="text" class="form-control" id="firstName" required>
                        </div>
                        <div class="col-md-6">
                            <label for="lastName" class="form-label">Nom *</label>
                            <input type="text" class="form-control" id="lastName" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="phone">
                        </div>
                        <div class="col-12">
                            <label for="experience" class="form-label">Expérience pertinente *</label>
                            <textarea class="form-control" id="experience" rows="4" required 
                                      placeholder="Décrivez votre expérience en lien avec ce poste..."></textarea>
                        </div>
                        <div class="col-12">
                            <label for="motivation" class="form-label">Lettre de motivation *</label>
                            <textarea class="form-control" id="motivation" rows="4" required 
                                      placeholder="Expliquez pourquoi vous souhaitez rejoindre StyleHub..."></textarea>
                        </div>
                        <div class="col-12">
                            <label for="cv" class="form-label">CV (PDF uniquement) *</label>
                            <input type="file" class="form-control" id="cv" accept=".pdf" required>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="consent" required>
                                <label class="form-check-label" for="consent">
                                    J'accepte que mes données personnelles soient traitées dans le cadre de ma candidature *
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="applicationForm" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-1"></i>Envoyer ma Candidature
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal candidature spontanée -->
<div class="modal fade" id="spontaneousModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Candidature Spontanée</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-4">
                    Vous êtes passionné par la mode et souhaitez rejoindre notre équipe ? 
                    Envoyez-nous votre candidature spontanée !
                </p>
                <form id="spontaneousForm" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="spFirstName" class="form-label">Prénom *</label>
                            <input type="text" class="form-control" id="spFirstName" required>
                        </div>
                        <div class="col-md-6">
                            <label for="spLastName" class="form-label">Nom *</label>
                            <input type="text" class="form-control" id="spLastName" required>
                        </div>
                        <div class="col-md-6">
                            <label for="spEmail" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="spEmail" required>
                        </div>
                        <div class="col-md-6">
                            <label for="spPhone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="spPhone">
                        </div>
                        <div class="col-12">
                            <label for="spDomain" class="form-label">Domaine d'expertise *</label>
                            <select class="form-select" id="spDomain" required>
                                <option value="">Choisissez un domaine</option>
                                <option value="tech">Développement / Technique</option>
                                <option value="design">Design / UX-UI</option>
                                <option value="marketing">Marketing / Communication</option>
                                <option value="sales">Vente / Commercial</option>
                                <option value="customer">Service Client</option>
                                <option value="logistics">Logistique</option>
                                <option value="finance">Finance / Comptabilité</option>
                                <option value="hr">Ressources Humaines</option>
                                <option value="other">Autre</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="spMessage" class="form-label">Message *</label>
                            <textarea class="form-control" id="spMessage" rows="5" required 
                                      placeholder="Présentez-vous et expliquez pourquoi vous souhaitez rejoindre StyleHub..."></textarea>
                        </div>
                        <div class="col-12">
                            <label for="spCv" class="form-label">CV (PDF uniquement) *</label>
                            <input type="file" class="form-control" id="spCv" accept=".pdf" required>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="spConsent" required>
                                <label class="form-check-label" for="spConsent">
                                    J'accepte que mes données personnelles soient traitées dans le cadre de ma candidature *
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="spontaneousForm" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-1"></i>Envoyer ma Candidature
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Gestion des modals de candidature
document.addEventListener('DOMContentLoaded', function() {
    // Modal de candidature pour un poste spécifique
    const applyModal = document.getElementById('applyModal');
    applyModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const jobTitle = button.getAttribute('data-job');
        document.getElementById('jobTitle').textContent = jobTitle;
    });
    
    // Validation des formulaires
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            event.stopPropagation();
            
            if (form.checkValidity()) {
                // Simuler l'envoi
                showToast('Candidature envoyée avec succès ! Nous vous recontacterons bientôt.', 'success');
                bootstrap.Modal.getInstance(form.closest('.modal')).hide();
                form.reset();
                form.classList.remove('was-validated');
            } else {
                form.classList.add('was-validated');
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>