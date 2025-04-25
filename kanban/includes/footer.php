        </div> <!-- Fermeture du container-fluid -->

        <!-- Pied de page amélioré -->
        <footer class="mt-auto py-3 bg-light border-top">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                        <small class="text-muted">
                            &copy; <?= date('Y') ?> Kanban Board - Tous droits réservés
                        </small>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <small class="text-muted d-block d-md-inline-block me-md-2 mb-2 mb-md-0">
                            Version 1.0.0
                        </small>
                        <small class="text-muted d-block d-md-inline-block">
                            <i class="fas fa-circle text-xs"></i> Dernière mise à jour : <?= date('d/m/Y H:i') ?>
                        </small>
                    </div>
                </div>
            </div>
        </footer>
    </div> <!-- Fermeture du wrapper principal -->

    <!-- Chargement optimisé des scripts -->
    <!-- jQuery avec fallback -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" 
            integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" 
            crossorigin="anonymous"></script>
    <script>window.jQuery || document.write('<script src="assets/js/jquery-3.6.0.min.js"><\/script>')</script>
    
    <!-- jQuery UI -->
    <script src="https://code.jquery.com/ui/1.13.0/jquery-ui.min.js" 
            integrity="sha256-hlKLmzaRlE8SCJC1Kw8zoUbU8BxA+8kR3gseuKfMjxA=" 
            crossorigin="anonymous"></script>
    
    <!-- Bootstrap Bundle avec Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" 
            integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" 
            crossorigin="anonymous"></script>
    
    <!-- SweetAlert2 optimisé -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.16.1/sweetalert2.min.js" integrity="sha512-LGHBR+kJ5jZSIzhhdfytPoEHzgaYuTRifq9g5l6ja6/k9NAOsAi5dQh4zQF6JIRB8cAYxTRedERUF+97/KuivQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
   <!--  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.8/dist/sweetalert2.all.min.js" 
            integrity="sha256-2BOnlpD8ZRyu6Z6nV6YQ8q8H7YF3nmRtZlT4Oqjp3oU=" 
            crossorigin="anonymous"></script> -->
	
    <!-- Font awesome -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" integrity="sha512-fD9DI5bZwQxOi7MhYWnnNPlvXdp/2Pj3XSTRrFs5FQa4mizyGLnJcN6tuvUS6LbmgN1ut+XGSABKvjN0H6Aoow==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
	
    <!-- Script personnalisé avec préchargement -->
    <link rel="preload" href="assets/js/script.js" as="script">
    <script src="assets/js/script.js" defer></script>
    
    <!-- Initialisation des composants -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    trigger: 'hover focus'
                });
            });
            
            // Vérification des dépendances
            if (!window.jQuery || !window.bootstrap || !window.Swal) {
                console.error('Erreur de chargement des dépendances');
                document.body.innerHTML = '<div class="alert alert-danger m-3">Erreur de chargement des ressources. Veuillez rafraîchir la page.</div>';
            }
        });
    </script>
</body>
</html>