document.addEventListener('DOMContentLoaded', function () {
    const validateAllBtn = document.getElementById('validateAllBtn');
    const commentInputs = document.querySelectorAll('.comment-input');

    // 💾 Sauvegarde automatique des commentaires
    commentInputs.forEach(input => {
        let saveTimeout;

        input.addEventListener('input', function () {
            const documentId = this.dataset.id;
            const comment = this.value;
            const saveIndicator = this.nextElementSibling;

            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                saveComment(documentId, comment, saveIndicator);
            }, 1000);
        });

        input.addEventListener('blur', function () {
            const documentId = this.dataset.id;
            const comment = this.value;
            const saveIndicator = this.nextElementSibling;

            clearTimeout(saveTimeout);
            saveComment(documentId, comment, saveIndicator);
        });
    });

    function saveComment(documentId, comment, saveIndicator) {
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
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (saveIndicator) {
                    saveIndicator.style.display = 'inline';
                    setTimeout(() => {
                        saveIndicator.style.display = 'none';
                    }, 2000);
                }
            } else {
                alert('Erreur lors de la sauvegarde du commentaire');
            }
        })
        .catch(error => {
            console.error('Erreur réseau:', error);
            alert('Erreur de connexion lors de la sauvegarde');
        });
    }

    // 🎯 Gestion des boutons Valider / Refuser / Annuler
    document.addEventListener('click', function (e) {
        // ✅ VALIDER
        if (e.target.classList.contains('validate-btn')) {
            const button = e.target;
            const row = button.closest('tr');
            const documentId = button.dataset.id;
            const commentInput = row.querySelector('.comment-input');
            const comment = commentInput ? commentInput.value : '';

            fetch('/stalhub/secretary/update-document-status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
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
                    statusCell.dataset.status = 'validé';
                    statusText.textContent = 'Validé';
                    statusText.style.color = 'green';

                    const actionsCell = row.querySelector('td:nth-child(4)'); // Colonne Actions
                    actionsCell.innerHTML = `
                        <button class="btn-action cancel-btn" data-id="${documentId}">↩️ Annuler la validation</button>
                        <div class="message-container"></div>
                    `;
                } else {
                    alert("Erreur lors de la validation.");
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert("Erreur de connexion lors de la validation.");
            });
        }

        // ❌ REFUSER
        else if (e.target.classList.contains('refuse-btn')) {
            const button = e.target;
            const row = button.closest('tr');
            const documentId = button.dataset.id;
            const commentInput = row.querySelector('.comment-input');
            const comment = commentInput ? commentInput.value : '';

            fetch('/stalhub/secretary/update-document-status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
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
                    statusCell.dataset.status = 'refusé';
                    statusText.textContent = 'Refusé';
                    statusText.style.color = 'red';

                    const actionsCell = row.querySelector('td:nth-child(4)'); // Colonne Actions
                    actionsCell.innerHTML = `
                        <button class="btn-action cancel-btn" data-id="${documentId}">↩️ Annuler la validation</button>
                        <div class="message-container"></div>
                    `;
                } else {
                    alert("Erreur lors du refus.");
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert("Erreur de connexion lors du refus.");
            });
        }

        // ↩️ ANNULER
        else if (e.target.classList.contains('cancel-btn')) {
            const button = e.target;
            const row = button.closest('tr');
            const documentId = button.dataset.id;
            const commentInput = row.querySelector('.comment-input');
            const comment = commentInput ? commentInput.value : '';

            fetch('/stalhub/secretary/update-document-status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    document_id: documentId,
                    status: 'submitted',
                    comment: comment
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const statusCell = row.querySelector('.doc-status');
                    const statusText = statusCell.querySelector('.status-text');
                    statusCell.dataset.status = 'soumis';
                    statusText.textContent = 'Soumis';
                    statusText.style.color = 'orange';

                    const actionsCell = row.querySelector('td:nth-child(4)'); // Colonne Actions
                    actionsCell.innerHTML = `
                        <button class="btn-action validate-btn" data-id="${documentId}">✅ Valider</button>
                        <button class="btn-action refuse-btn" data-id="${documentId}">❌ Refuser</button>
                        <div class="message-container"></div>
                    `;
                } else {
                    alert("Erreur lors de l'annulation.");
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert("Erreur de connexion lors de l'annulation.");
            });
        }
    });

    // ✅ Valider tous les documents
    if (validateAllBtn) {
        validateAllBtn.addEventListener('click', function () {
            const allRows = document.querySelectorAll('tbody tr');
            const allDocumentIds = [];

            allRows.forEach(row => {
                const statusCell = row.querySelector('.doc-status');
                const statusText = statusCell.querySelector('.status-text');
                statusCell.dataset.status = 'validé';
                statusText.textContent = 'Validé';
                statusText.style.color = 'green';

                const documentId = row.querySelector('[data-id]').dataset.id;
                const actionsCell = row.querySelector('td:nth-child(4)'); // Colonne Actions
                actionsCell.innerHTML = `
                    <button class="btn-action cancel-btn" data-id="${documentId}">↩️ Annuler la validation</button>
                    <div class="message-container"></div>
                `;

                allDocumentIds.push(documentId);
            });

            fetch('/stalhub/secretary/validate-all-documents', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ document_ids: allDocumentIds })
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    alert("Erreur lors de la validation en masse.");
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert("Erreur de connexion.");
            });
        });
    }
    
});

