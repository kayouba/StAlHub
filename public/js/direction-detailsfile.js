// Fonction pour sauvegarder un commentaire
function saveComment(documentId) {
   const textarea = document.getElementById(`comment-${documentId}`);
   const comment = textarea.value;


   fetch('/stalhub/direction/save-comment', {
   	method: 'POST',
   	headers: {
       	'Content-Type': 'application/json',
   	},
   	body: JSON.stringify({
       	document_id: documentId,
       	comment: comment
   	})
   })
   .then(response => response.json())
   .then(data => {
   	if (data.success) {
       	showNotification('Commentaire sauvegardé avec succès', 'success');
   	} else {
       	showNotification('Erreur: ' + data.message, 'error');
   	}
   })
   .catch(error => {
   	showNotification('Erreur de communication', 'error');
   });
}


// Fonction pour afficher les notifications
function showNotification(message, type = 'info') {
    const notification = document.getElementById('notification');
    if (!notification) {
        console.error('Élément de notification manquant');
        return;
    }
    
    // Nettoyer les classes existantes
    notification.className = 'notification';
    notification.textContent = message;
    notification.classList.add(type);
    notification.style.display = 'block';
    
    // Animation d'entrée
    notification.style.opacity = '0';
    notification.style.transform = 'translateY(-20px)';
    
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateY(0)';
    }, 10);
    
    // Masquer automatiquement après 4 secondes
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-20px)';
        setTimeout(() => {
            notification.style.display = 'none';
        }, 300);
    }, 4000);
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




// Variable globale pour stocker l'ID de la demande
let currentRequestId = null;

// Fonction pour signer ou refuser un document
function signDocument(documentId, action) {
    const actionText = action === 'sign' ? 'signer' : 'refuser';
    const confirmText = `Êtes-vous sûr de vouloir ${actionText} ce document ?`;
    
    if (confirm(confirmText)) {
        // Afficher un indicateur de chargement
        const button = document.querySelector(`[data-document-id="${documentId}"]`);
        if (button) {
            button.disabled = true;
            button.textContent = 'Traitement...';
        }

        fetch('/stalhub/direction/document/sign', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                document_id: documentId,
                action: action
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                // Recharger la page après un délai
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showNotification('Erreur: ' + data.message, 'error');
                // Réactiver le bouton en cas d'erreur
                if (button) {
                    button.disabled = false;
                    button.textContent = action === 'sign' ? '✅ Signer' : '❌ Refuser';
                }
            }
        })
        .catch(error => {
            console.error('Erreur complète:', error);
            showNotification('Erreur de communication avec le serveur', 'error');
            // Réactiver le bouton en cas d'erreur
            if (button) {
                button.disabled = false;
                button.textContent = action === 'sign' ? '✅ Signer' : '❌ Refuser';
            }
        });
    }
}

// Fonction pour valider définitivement un document
function validateDocument(documentId) {
    if (confirm('Êtes-vous sûr de vouloir valider définitivement ce document ?')) {
        const button = document.querySelector(`[onclick="validateDocument(${documentId})"]`);
        if (button) {
            button.disabled = true;
            button.textContent = 'Validation...';
        }

        fetch('/stalhub/direction/document/validate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                document_id: documentId
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showNotification('Erreur: ' + data.message, 'error');
                if (button) {
                    button.disabled = false;
                    button.textContent = '✅ Valider définitivement';
                }
            }
        })
        .catch(error => {
            console.error('Erreur complète:', error);
            showNotification('Erreur de communication avec le serveur', 'error');
            if (button) {
                button.disabled = false;
                button.textContent = '✅ Valider définitivement';
            }
        });
    }
}

function signAllDocuments() {
    if (confirm('Êtes-vous sûr de vouloir signer toutes les pièces jointes ?')) {
        const requestId = getCurrentRequestId();
        const button = document.getElementById('signAllBtn');
        
        if (button) {
            button.disabled = true;
            button.textContent = 'Signature en cours...';
        }
        
        fetch('/stalhub/direction/documents/sign-all', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                request_id: requestId
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showNotification('Erreur: ' + data.message, 'error');
                if (button) {
                    button.disabled = false;
                    button.textContent = '✅ Signer toutes les pièces jointes';
                }
            }
        })
        .catch(error => {
            console.error('Erreur complète:', error);
            showNotification('Erreur de communication avec le serveur', 'error');
            if (button) {
                button.disabled = false;
                button.textContent = '✅ Signer toutes les pièces jointes';
            }
        });
    }
}


function validateAllDocuments() {
    if (confirm('Êtes-vous sûr de vouloir valider définitivement toutes les pièces jointes ?')) {
        const requestId = getCurrentRequestId();
        const button = document.getElementById('validateAllBtn');
        
        if (button) {
            button.disabled = true;
            button.textContent = 'Validation en cours...';
        }
        
        fetch('/stalhub/direction/documents/validate-all', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                request_id: requestId
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showNotification('Erreur: ' + data.message, 'error');
                if (button) {
                    button.disabled = false;
                    button.textContent = '🔒 Valider toutes les pièces jointes';
                }
            }
        })
        .catch(error => {
            console.error('Erreur complète:', error);
            showNotification('Erreur de communication avec le serveur', 'error');
            if (button) {
                button.disabled = false;
                button.textContent = '🔒 Valider toutes les pièces jointes';
            }
        });
    }
}


// Fonction pour finaliser le dossier
function finalizeDossier(requestId) {
    if (confirm('Êtes-vous sûr de vouloir finaliser ce dossier ? Cette action est irréversible.')) {
        fetch('/stalhub/direction/dossier/finalize', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                request_id: requestId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                // Recharger la page pour mettre à jour l'interface
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showNotification('Erreur: ' + data.message, 'error');
            }
        })
        .catch(error => {
            showNotification('Erreur de communication', 'error');
            console.error('Erreur:', error);
        });
    }
}

// Fonction pour récupérer l'ID de la demande depuis l'URL
function getCurrentRequestId() {
    const urlParams = new URLSearchParams(window.location.search);
    return parseInt(urlParams.get('id')) || 0;
}

// Fonction pour mettre à jour les boutons globaux
function updateGlobalButtons() {
    // Cette fonction peut être utilisée pour mettre à jour l'état des boutons
    // selon l'état des documents
    console.log('Mise à jour des boutons globaux');
}

// Fonction pour fermer la modal
function closeModal() {
    const modal = document.getElementById('confirmModal');
    if (modal) {
        modal.style.display = 'none';
    }
}
