// Fonction pour sauvegarder un commentaire
function saveComment(documentId) {
    const commentInput = document.getElementById(`comment-${documentId}`);
    const comment = commentInput.value.trim();
    
    // TODO: Remplacer par un vrai appel AJAX vers le serveur
    console.log(`Sauvegarde du commentaire pour le document ${documentId}: ${comment}`);
    
    // Simuler la sauvegarde
    showNotification('Commentaire sauvegardé avec succès', 'success');
}

// Fonction principale pour signer un document
function signDocument(documentId, action) {
    const actionText = action === 'sign' ? 'signer' : 'refuser';
    const confirmText = action === 'sign' ? 'Signer le document' : 'Refuser le document';
    const message = `Êtes-vous sûr de vouloir ${actionText} ce document ?`;
    
    showModal(confirmText, message, () => {
        performDocumentAction(documentId, action);
    });
}

// Fonction pour effectuer l'action sur un document
function performDocumentAction(documentId, action) {
    showLoadingState(documentId, true);
    
    // TODO: Remplacer par un vrai appel AJAX
    setTimeout(() => {
        console.log(`Action ${action} effectuée sur le document ${documentId}`);
        
        // Mettre à jour l'interface
        updateDocumentUI(documentId, action);
        showLoadingState(documentId, false);
        
        // Afficher une notification
        const actionText = action === 'sign' ? 'signé' : 
                          action === 'refuse' ? 'refusé' : 'validé';
        showNotification(`Document ${actionText} avec succès`, 'success');
        
        // Mettre à jour les boutons globaux
        updateGlobalButtons();
        
    }, 1000); // Simulation d'une requête réseau
}

// Fonctions pour les actions globales
function signAllDocuments() {
    const pendingCards = document.querySelectorAll('.document-card');
    const pendingDocuments = [];
    
    pendingCards.forEach(card => {
        const statusElement = card.querySelector('.status-pending');
        if (statusElement && statusElement.textContent.trim() === 'En cours') {
            const documentId = card.getAttribute('data-document-id');
            pendingDocuments.push(documentId);
        }
    });
    
    if (pendingDocuments.length === 0) {
        showNotification('Aucun document en attente de signature', 'error');
        return;
    }

    showModal(
        'Signer tous les documents',
        `Êtes-vous sûr de vouloir signer tous les documents en attente (${pendingDocuments.length} document(s)) ?`,
        () => {
            pendingDocuments.forEach(documentId => {
                performDocumentAction(documentId, 'sign');
            });
        }
    );
}

function validateAllDocuments() {
    const signedCards = document.querySelectorAll('.document-card');
    const signedDocuments = [];
    
    signedCards.forEach(card => {
        const statusElement = card.querySelector('.status-signed');
        if (statusElement && statusElement.textContent.trim() === 'Accepté') {
            const documentId = card.getAttribute('data-document-id');
            signedDocuments.push(documentId);
        }
    });
    
    if (signedDocuments.length === 0) {
        showNotification('Aucun document signé à valider', 'error');
        return;
    }

    showModal(
        'Valider tous les documents',
        `Êtes-vous sûr de vouloir valider définitivement tous les documents signés (${signedDocuments.length} document(s)) ?`,
        () => {
            signedDocuments.forEach(documentId => {
                validateDocument(documentId);
            });
        }
    );
}

function validateDocument(documentId) {
    performDocumentAction(documentId, 'validate');
}

function finalizeDossier(requestId) {
    showModal(
        'Finaliser le dossier',
        'Êtes-vous sûr de vouloir finaliser ce dossier ? Cette action est irréversible.',
        () => {
            // TODO: Remplacer par un vrai appel AJAX
            console.log(`Finalisation du dossier ${requestId}`);
            showNotification('Dossier finalisé avec succès', 'success');
            
            // Désactiver tous les boutons après finalisation
            const allButtons = document.querySelectorAll('.btn:not(.btn-view)');
            allButtons.forEach(btn => {
                btn.disabled = true;
                btn.classList.add('disabled');
            });
        }
    );
}

// Mise à jour de la fonction updateDocumentUI pour les cartes
function updateDocumentUI(documentId, action) {
    const documentCard = document.querySelector(`.document-card[data-document-id="${documentId}"]`);
    if (!documentCard) return;

    const statusElement = documentCard.querySelector('.document-status span');
    const actionsSection = documentCard.querySelector('.document-actions');

    if (action === 'sign') {
        statusElement.className = 'status-signed';
        statusElement.textContent = 'Accepté';
        
        // Remplacer les boutons d'action
        const actionButtons = actionsSection.querySelector('.action-buttons');
        if (actionButtons) {
            actionButtons.innerHTML = `
                <div class="validation-section">
                    <button class="btn btn-validate" 
                            onclick="validateDocument(${documentId})"
                            data-document-id="${documentId}">
                        ✅ Valider définitivement
                    </button>
                </div>
            `;
        }
    } else if (action === 'refuse') {
        statusElement.className = 'status-refused';
        statusElement.textContent = 'Refusé';
        
        const actionButtons = actionsSection.querySelector('.action-buttons');
        if (actionButtons) {
            actionButtons.innerHTML = `
                <div class="status-refused">
                    <span class="badge badge-danger">❌ Refusé</span>
                </div>
            `;
        }
    } else if (action === 'validate') {
        statusElement.className = 'status-validated';
        statusElement.textContent = 'Validé';
        
        const validationSection = actionsSection.querySelector('.validation-section');
        if (validationSection) {
            validationSection.innerHTML = `
                <div class="status-final">
                    <span class="badge badge-success">✅ Validé définitivement</span>
                </div>
            `;
        }
    }
}

// Mise à jour de showLoadingState pour les cartes
function showLoadingState(documentId, isLoading) {
    const documentCard = document.querySelector(`.document-card[data-document-id="${documentId}"]`);
    if (!documentCard) return;

    if (isLoading) {
        documentCard.setAttribute('data-loading', 'true');
    } else {
        documentCard.removeAttribute('data-loading');
    }

    const buttons = documentCard.querySelectorAll('.btn:not(.btn-view):not(.btn-save-comment)');
    
    buttons.forEach(btn => {
        btn.disabled = isLoading;
        if (isLoading) {
            btn.dataset.originalText = btn.innerHTML;
            btn.innerHTML = '⏳ En cours...';
        } else if (btn.dataset.originalText) {
            btn.innerHTML = btn.dataset.originalText;
            delete btn.dataset.originalText;
        }
    });
}

// Fonction pour mettre à jour les boutons globaux
function updateGlobalButtons() {
    const pendingCards = document.querySelectorAll('.document-card .status-pending');
    const signedCards = document.querySelectorAll('.document-card .status-signed');
    const validatedCards = document.querySelectorAll('.document-card .status-validated');
    const allCards = document.querySelectorAll('.document-card');
    
    const signAllBtn = document.getElementById('signAllBtn');
    const validateAllBtn = document.getElementById('validateAllBtn');
    const finalizeBtn = document.getElementById('finalizeBtn');
    
    // Gérer le bouton "Signer tout"
    if (signAllBtn) {
        signAllBtn.style.display = pendingCards.length > 0 ? 'inline-block' : 'none';
    }
    
    // Gérer le bouton "Valider tout"
    if (validateAllBtn) {
        validateAllBtn.style.display = signedCards.length > 0 ? 'inline-block' : 'none';
    }
    
    // Gérer le bouton "Finaliser"
    if (finalizeBtn) {
        const allValidated = allCards.length > 0 && validatedCards.length === allCards.length;
        finalizeBtn.style.display = allValidated ? 'inline-block' : 'none';
    }
}

// Fonction pour afficher la modal centrée
function showModal(title, message, onConfirm) {
    const modal = document.getElementById('confirmModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const confirmButton = document.getElementById('confirmButton');
    
    if (!modal || !modalTitle || !modalMessage || !confirmButton) {
        console.error('Éléments de modal manquants');
        return;
    }
    
    modalTitle.textContent = title;
    modalMessage.textContent = message;
    
    // Supprimer les anciens event listeners
    const newConfirmButton = confirmButton.cloneNode(true);
    confirmButton.parentNode.replaceChild(newConfirmButton, confirmButton);
    
    // Ajouter le nouvel event listener
    newConfirmButton.addEventListener('click', () => {
        closeModal();
        if (typeof onConfirm === 'function') {
            onConfirm();
        }
    });
    
    // Afficher la modal avec le bon style
    modal.style.display = 'flex'; // Utiliser flex au lieu de block
    modal.style.alignItems = 'center';
    modal.style.justifyContent = 'center';
    
    // Empêcher le scroll du body quand la modal est ouverte
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const modal = document.getElementById('confirmModal');
    if (modal) {
        modal.style.display = 'none';
        // Restaurer le scroll du body
        document.body.style.overflow = '';
    }
}

// Alternative : Fonction encore plus robuste qui crée la modal si elle n'existe pas
function showModalRobust(title, message, onConfirm) {
    // Vérifier si la modal existe, sinon la créer
    let modal = document.getElementById('confirmModal');
    
    if (!modal) {
        // Créer la modal dynamiquement
        modal = document.createElement('div');
        modal.id = 'confirmModal';
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content">
                <h3 id="modalTitle"></h3>
                <p id="modalMessage"></p>
                <div class="modal-actions">
                    <button id="cancelButton" class="btn btn-cancel">Annuler</button>
                    <button id="confirmButton" class="btn btn-confirm">Confirmer</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        
        // Ajouter l'événement pour fermer en cliquant à l'extérieur
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });
        
        // Ajouter l'événement pour le bouton annuler
        const cancelButton = modal.querySelector('#cancelButton');
        cancelButton.addEventListener('click', closeModal);
    }
    
    const modalTitle = modal.querySelector('#modalTitle');
    const modalMessage = modal.querySelector('#modalMessage');
    const confirmButton = modal.querySelector('#confirmButton');
    
    modalTitle.textContent = title;
    modalMessage.textContent = message;
    
    // Supprimer les anciens event listeners du bouton confirmer
    const newConfirmButton = confirmButton.cloneNode(true);
    confirmButton.parentNode.replaceChild(newConfirmButton, confirmButton);
    
    // Ajouter le nouvel event listener
    newConfirmButton.addEventListener('click', () => {
        closeModal();
        if (typeof onConfirm === 'function') {
            onConfirm();
        }
    });
    
    // Afficher la modal centrée
    modal.style.display = 'flex';
    modal.style.alignItems = 'center';
    modal.style.justifyContent = 'center';
    
    // Empêcher le scroll du body
    document.body.style.overflow = 'hidden';
}

// Fonction pour afficher les notifications
function showNotification(message, type = 'info') {
    const notification = document.getElementById('notification');
    if (!notification) {
        console.error('Élément de notification manquant');
        return;
    }
    
    notification.textContent = message;
    notification.className = `notification ${type}`;
    notification.style.display = 'block';
    
    // Masquer automatiquement après 3 secondes
    setTimeout(() => {
        notification.style.display = 'none';
    }, 3000);
}

// Fermer la modal si on clique à l'extérieur
document.addEventListener('click', (event) => {
    const modal = document.getElementById('confirmModal');
    if (modal && event.target === modal) {
        closeModal();
    }
});

// Fermer la modal avec la touche Échap
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeModal();
    }
});

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    console.log('Direction details file script loaded');
    updateGlobalButtons();
});

