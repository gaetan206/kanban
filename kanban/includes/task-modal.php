<div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content kanban-modal-content">
            <!-- En-tête avec couleur dynamique -->
            <div class="modal-header kanban-modal-header">
                <h5 class="modal-title fw-bold" id="taskModalLabel">Nouvelle Tâche</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- Corps du formulaire -->
            <div class="modal-body p-4">
                <form id="taskForm" class="needs-validation" novalidate>
                    <input type="hidden" id="taskId" name="id">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <!-- Champ Titre -->
                    <div class="mb-4">
                        <label for="taskTitle" class="form-label fw-bold">
                            Titre <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control kanban-input" 
                               id="taskTitle" name="title" required
                               placeholder="Nom de la tâche" maxlength="255">
                        <div class="invalid-feedback">
                            Un titre est obligatoire (max 255 caractères)
                        </div>
                    </div>
                    
                    <!-- Champ Description -->
                    <div class="mb-4">
                        <label for="taskDescription" class="form-label fw-bold">Description</label>
                        <textarea class="form-control kanban-textarea" 
                                  id="taskDescription" name="description" 
                                  rows="4" placeholder="Détails de la tâche..."
                                  maxlength="2000"></textarea>
                        <div class="text-end small text-muted">
                            <span id="descriptionCounter">0</span>/2000
                        </div>
                    </div>
                    
                    <!-- Sélecteur de Statut -->
                    <div class="mb-4">
                        <label for="taskStatus" class="form-label fw-bold">
                            Statut <span class="text-danger">*</span>
                        </label>
                        <select class="form-select kanban-select" 
                                id="taskStatus" name="status" required>
                            <option value="todo">À faire</option>
                            <option value="inprogress">En cours</option>
                            <option value="blocked">Bloqué</option>
                            <option value="done">Terminé</option>
                        </select>
                    </div>
                    
                    <!-- Date limite -->
                    <div class="mb-4">
                        <label for="taskDueDate" class="form-label fw-bold">
                            <i class="fas fa-calendar-alt me-2"></i>Date limite
                        </label>
                        <input type="date" class="form-control kanban-input" 
                               id="taskDueDate" name="due_date">
                    </div>
                    
                    <!-- Boutons d'action -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button type="button" class="btn btn-outline-danger" id="deleteTaskBtn" style="display: none;">
                            <i class="fas fa-trash me-2"></i>Supprimer
                        </button>
                        <div>
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                                Annuler
                            </button>
                            <button type="submit" id="submitBtn" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Enregistrer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* Styles spécifiques à la modal */
    .kanban-modal-content {
        border-radius: 15px;
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }
    
    .kanban-modal-header {
        border-radius: 15px 15px 0 0 !important;
        background-color: #6c757d; /* Couleur par défaut */
        color: white;
        border-bottom: none;
        padding: 1.2rem 1.5rem;
    }
    
    .kanban-input, .kanban-select {
        border-radius: 8px;
        padding: 10px 15px;
        border: 1px solid #dee2e6;
        transition: all 0.3s ease;
    }
    
    .kanban-input:focus, .kanban-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    .kanban-textarea {
        border-radius: 10px;
        min-height: 120px;
        resize: vertical;
    }
</style>

<script>
$(document).ready(function() {
    // Compteur de caractères
    $('#taskDescription').on('input', function() {
        const count = $(this).val().length;
        $('#descriptionCounter').text(count);
        $(this).toggleClass('is-invalid', count > 2000);
    });

    // Gestion du formulaire
    $('#taskForm').on('submit', function(e) {
        e.preventDefault();
        
        if (!this.checkValidity()) {
            e.stopPropagation();
            $(this).addClass('was-validated');
            return;
        }

        const formData = $(this).serialize();
        const isEdit = $('#taskId').val() !== '';
        const url = isEdit ? 'api/update_task.php' : 'api/create_task.php';

        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement...');

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#taskModal').modal('hide');
                    showToast('Succès', isEdit ? 'Tâche mise à jour' : 'Tâche créée', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Erreur', response.message || 'Action échouée', 'error');
                }
            },
            error: function(xhr) {
                showToast('Erreur', 'Erreur serveur', 'error');
                console.error(xhr.responseText);
            },
            complete: function() {
                $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save me-2"></i>Enregistrer');
            }
        });
    });

    // Fonction pour afficher les toasts
    function showToast(title, message, type) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
        Toast.fire({ icon: type, title: title, text: message });
    }
});
</script>