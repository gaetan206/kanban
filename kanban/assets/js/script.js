$(document).ready(function() {
    // Initialisation du drag and drop
    $('.kanban-column').sortable({
        connectWith: '.kanban-column',
        placeholder: 'card-placeholder',
        tolerance: 'pointer',
        cursor: 'move',
        opacity: 0.7,
        update: function(event, ui) {
            if (!ui.sender && ui.item) {
                updateTaskPosition(ui.item);
            }
        },
        receive: function(event, ui) {
            updateTaskPosition(ui.item);
        }
    }).disableSelection();

    // Fonction pour mettre à jour la position d'une tâche
    function updateTaskPosition(taskElement) {
        const taskId = taskElement.data('task-id');
        const newStatus = taskElement.closest('.kanban-column').data('status');
        const newPosition = taskElement.index() + 1;

        $.ajax({
            url: 'update_task_position.php',
            method: 'POST',
            data: {
                task_id: taskId,
                status: newStatus,
                position: newPosition,
                csrf_token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (!response.success) {
                    showAlert('Erreur', response.message || 'Erreur lors du déplacement', 'danger');
                }
            },
            error: function(xhr) {
                showAlert('Erreur', 'Problème de connexion au serveur', 'danger');
                console.error(xhr.responseText);
            }
        });
    }

    // Fonction pour afficher des alertes stylisées
    function showAlert(title, message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3" role="alert">
                <strong>${title}</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('body').append(alertHtml);
        setTimeout(() => $('.alert').alert('close'), 5000);
    }

    // Gestion de l'ajout de tâche
    $('.add-task').click(function() {
        const status = $(this).data('status');
        resetTaskForm();
        $('#taskStatus').val(status);
        $('#taskModalLabel').text('Ajouter une tâche');
        updateModalHeaderColor(status);
        $('#taskModal').modal('show');
    });

    // Gestion de la modification de tâche
    $(document).on('click', '.edit-task', function() {
        const taskId = $(this).data('id');
        
        $('#taskModal .modal-body').addClass('loading');
        $('#taskModal').modal('show');
        
        $.ajax({
            url: 'get_task.php?id=' + taskId,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#taskModal .modal-body').removeClass('loading');
                
                if (response.success && response.task) {
                    fillTaskForm(response.task);
                    $('#taskModalLabel').text('Modifier la tâche');
                    updateModalHeaderColor(response.task.status);
                } else {
                    showAlert('Erreur', response.message || 'Tâche non trouvée', 'danger');
                    $('#taskModal').modal('hide');
                }
            },
            error: function(xhr) {
                $('#taskModal .modal-body').removeClass('loading');
                showAlert('Erreur', 'Erreur lors du chargement', 'danger');
                console.error(xhr.responseText);
            }
        });
    });

    // Soumission du formulaire (CREATE/UPDATE)
    $('#taskForm').submit(function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $submitBtn = $form.find('[type="submit"]');
        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        const formData = $form.serializeArray();
        formData.push({
            name: 'csrf_token',
            value: $('meta[name="csrf-token"]').attr('content')
        });
        
        const taskId = $('#taskId').val();
        const url = taskId ? 'update_task.php' : 'create_task.php';
        
        $.ajax({
            url: url,
            method: 'POST',
            data: $.param(formData),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#taskModal').modal('hide');
                    showAlert('Succès', taskId ? 'Tâche mise à jour' : 'Tâche créée', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    let errorMsg = response.message || 'Erreur lors de l\'opération';
                    if (response.errors) {
                        errorMsg += '\n' + Object.values(response.errors).join('\n');
                    }
                    showAlert('Erreur', errorMsg, 'danger');
                }
            },
            error: function(xhr) {
                showAlert('Erreur', 'Erreur de connexion au serveur', 'danger');
                console.error(xhr.responseText);
            },
            complete: function() {
                $submitBtn.prop('disabled', false).html(taskId ? 'Mettre à jour' : 'Créer');
            }
        });
    });

    // Suppression de tâche
    $(document).on('click', '.delete-task', function() {
        const taskId = $(this).data('id');
        
        if (confirm('Êtes-vous sûr de vouloir supprimer cette tâche ?')) {
            $.ajax({
                url: 'delete_task.php',
                method: 'POST',
                data: {
                    id: taskId,
                    csrf_token: $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $(`.task-card[data-task-id="${taskId}"]`).fadeOut(300, function() {
                            $(this).remove();
                        });
                        showAlert('Succès', 'Tâche supprimée', 'success');
                    } else {
                        showAlert('Erreur', response.message || 'Échec de la suppression', 'danger');
                    }
                },
                error: function(xhr) {
                    showAlert('Erreur', 'Erreur lors de la suppression', 'danger');
                    console.error(xhr.responseText);
                }
            });
        }
    });

    // Fonctions utilitaires
    function fillTaskForm(task) {
        $('#taskId').val(task.id);
        $('#taskTitle').val(task.title);
        $('#taskDescription').val(task.description);
        $('#taskStatus').val(task.status);
        if (task.due_date) {
            $('#taskDueDate').val(task.due_date.split(' ')[0]);
        }
    }

    function resetTaskForm() {
        $('#taskId').val('');
        $('#taskForm')[0].reset();
    }

    function updateModalHeaderColor(status) {
        const colors = {
            'todo': '#FF9F40',
            'inprogress': '#2E86DE',
            'blocked': '#EE5253',
            'done': '#10AC84'
        };
        $('#taskModal .modal-header').css('background-color', colors[status] || '#f8f9fa');
    }

    $('#taskStatus').change(function() {
        updateModalHeaderColor($(this).val());
    });
});