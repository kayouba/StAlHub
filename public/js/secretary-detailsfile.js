document.addEventListener('DOMContentLoaded', function () {
    const validateButtons = document.querySelectorAll('.validate-btn');
    const refuseButtons = document.querySelectorAll('.refuse-btn');
    const validateAllBtn = document.getElementById('validateAllBtn');
    const commentInputs = document.querySelectorAll('.comment-input');

    // 💾 Sauvegarde automatique des commentaires
    commentInputs.forEach(input => {
        let saveTimeout;
        
        input.addEventListener('input', function () {
            const documentId = this.dataset.id;
            const comment = this.value;
            const saveIndicator = this.nextElementSibling;
            
            // Debug
            console.log(`Commentaire modifié pour le document ${documentId}: "${comment}"`);
            
            // Débouncing : attendre 1 seconde après la dernière frappe
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                saveComment(documentId, comment, saveIndicator);
            }, 1000);
        });

        // Sauvegarde immédiate quand l'utilisateur quitte le champ
        input.addEventListener('blur', function () {
            const documentId = this.dataset.id;
            const comment = this.value;
            const saveIndicator = this.nextElementSibling;
            
            console.log(`Sauvegarde immédiate pour le document ${documentId}: "${comment}"`);
            
            clearTimeout(saveTimeout);
            saveComment(documentId, comment, saveIndicator);
        });
    });

    // Fonction pour sauvegarder le commentaire
    function saveComment(documentId, comment, saveIndicator) {
        console.log(`Envoi de la requête de sauvegarde pour le document ${documentId}`);
        
        fetch('/stalhub/secretary/save-comment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                document_id: documentId,
                comment: comment
            })
        })
        .then(res => {
            console.log('Réponse reçue:', res.status);
            return res.json();
        })
        .then(data => {
            console.log('Données reçues:', data);
            if (data.success) {
                // Afficher l'indicateur de sauvegarde
                if (saveIndicator) {
                    saveIndicator.style.display = 'inline';
                    setTimeout(() => {
                        saveIndicator.style.display = 'none';
                    }, 2000);
                }
                console.log('Commentaire sauvegardé avec succès');
            } else {
                console.error('Erreur lors de la sauvegarde du commentaire:', data.message || 'Erreur inconnue');
                alert('Erreur lors de la sauvegarde du commentaire');
            }
        })
        .catch(error => {
            console.error('Erreur réseau:', error);
            alert('Erreur de connexion lors de la sauvegarde');
        });
    }

    // ✅ Validation d'un seul document
    validateButtons.forEach(button => {
        button.addEventListener('click', function () {
            const row = this.closest('tr');
            const documentId = this.dataset.id;
            const commentInput = row.querySelector('.comment-input');
            const comment = commentInput ? commentInput.value : '';

            fetch('/stalhub/secretary/update-document-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    document_id: documentId,
                    status: 'validated',
                    comment: comment
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const statusCell = row.querySelector('.doc-status');
                    const statusText = statusCell.querySelector('.status-text');
                    statusCell.dataset.status = 'validée';
                    statusText.textContent = 'Validée';
                    statusText.style.color = 'green';
                } else {
                    alert("Erreur lors de la validation du document.");
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert("Erreur de connexion lors de la validation.");
            });
        });
    });

    // ❌ Refus d'un seul document
    refuseButtons.forEach(button => {
        button.addEventListener('click', function () {
            const row = this.closest('tr');
            const documentId = this.dataset.id;
            const commentInput = row.querySelector('.comment-input');
            const comment = commentInput ? commentInput.value : '';

            fetch('/stalhub/secretary/update-document-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    document_id: documentId,
                    status: 'rejected',
                    comment: comment
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const statusCell = row.querySelector('.doc-status');
                    const statusText = statusCell.querySelector('.status-text');
                    statusCell.dataset.status = 'refusée';
                    statusText.textContent = 'Refusée';
                    statusText.style.color = 'red';
                } else {
                    alert("Erreur lors du refus du document.");
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert("Erreur de connexion lors du refus.");
            });
        });
    });

    // ✅ Valider tous les documents (mise à jour UI + base de données)
    if (validateAllBtn) {
        validateAllBtn.addEventListener('click', function () {
            const allRows = document.querySelectorAll('tbody tr');
            const allDocumentIds = [];

            allRows.forEach(row => {
                const statusCell = row.querySelector('.doc-status');
                const statusText = statusCell.querySelector('.status-text');
                statusCell.dataset.status = 'validée';
                statusText.textContent = 'Validée';
                statusText.style.color = 'green';

                const documentId = row.dataset.id;
                allDocumentIds.push(documentId);
            });

            // ⚠️ Mise à jour de la base de données pour tous les documents
            fetch('/stalhub/secretary/validate-all-documents', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    document_ids: allDocumentIds
                })
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert("Une erreur est survenue lors de la validation en masse.");
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert("Erreur de connexion lors de la validation en masse.");
            });
        });
    }
});