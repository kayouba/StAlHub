// Filtres pour le tableau
const filters = {
  formation: document.getElementById("filter-formation"),
  etat: document.getElementById("filter-etat"),
  search: document.getElementById("search")
};

const rows = document.querySelectorAll("#table-body tr");

function filterTable() {
  const formationVal = filters.formation.value.toLowerCase();
  const etatVal = filters.etat.value.toLowerCase();
  const searchVal = filters.search.value.toLowerCase();

  rows.forEach(row => {
    const formation = row.children[1].textContent.toLowerCase();
    const etat = row.children[6].textContent.toLowerCase(); // colonne État
    const fullText = row.textContent.toLowerCase();

    const matchFormation = !formationVal || formation.includes(formationVal);
    const matchEtat = !etatVal || etat.includes(etatVal);
    const matchSearch = !searchVal || fullText.includes(searchVal);

    row.style.display = (matchFormation && matchEtat && matchSearch) ? "" : "none";
  });
}

Object.values(filters).forEach(el => el.addEventListener("input", filterTable));

// -----------------------------
// Gestion du popup d'upload
// -----------------------------

// function openPopup(demandeId) {
//   const popup = document.getElementById('upload-popup');
//   const sendBtn = document.getElementById('send-convention');
//   const fileInput = document.getElementById('convention-file');

//   popup.style.display = 'flex';
//   popup.setAttribute('data-demande-id', demandeId);
//   fileInput.value = '';

//   sendBtn.disabled = false;
//   sendBtn.textContent = "Envoyer";
// }

// function closePopup() {
//   document.getElementById('upload-popup').style.display = 'none';
// }

// document.addEventListener('DOMContentLoaded', () => {
//   const closeBtn = document.getElementById('close-popup');
//   const sendBtn = document.getElementById('send-convention');
//   const fileInput = document.getElementById('convention-file');
//   const popup = document.getElementById('upload-popup');

//   // Attacher le gestionnaire aux boutons d'upload
//   document.querySelectorAll('.upload-btn').forEach(btn => {
//     btn.addEventListener('click', () => {
//       const demandeId = btn.getAttribute('data-id');
//       openPopup(demandeId);
//     });
//   });

//   // Fermer le popup
//   closeBtn.addEventListener('click', closePopup);

//   popup.addEventListener('click', (e) => {
//     if (e.target === popup) {
//       closePopup();
//     }
//   });

//   // Validation du fichier
//   fileInput.addEventListener('change', () => {
//     const file = fileInput.files[0];
//     if (file) {
//       if (file.size > 10 * 1024 * 1024) {
//         alert('Le fichier est trop volumineux. Taille maximum : 10MB');
//         fileInput.value = '';
//         return;
//       }

//       const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
//       if (!allowedTypes.includes(file.type)) {
//         alert('Type de fichier non autorisé. Veuillez sélectionner un fichier PDF ou Word.');
//         fileInput.value = '';
//         return;
//       }
//     }
//   });

  
// });

// function updateRowStatus(demandeId, newStatus) {
//   const rows = document.querySelectorAll('#table-body tr');
//   rows.forEach(row => {
//     const uploadBtn = row.querySelector('.upload-btn');
//     if (uploadBtn && uploadBtn.getAttribute('data-id') === demandeId) {
//       const statusCell = row.children[6];
//       statusCell.textContent = newStatus;
//       // Appliquer la classe CSS pour "Convention envoyée" (vert)
//       statusCell.className = 'convention-sent';
      
//       // Optionnellement, masquer ou désactiver le bouton d'upload
//       uploadBtn.style.opacity = '0.5';
//       uploadBtn.style.pointerEvents = 'none';
//       uploadBtn.title = 'Convention déjà envoyée';
//     }
//   });
// }

// // JavaScript pour gérer l'upload de la convention
// document.addEventListener('DOMContentLoaded', function() {
//     const form = document.getElementById('conventionForm');
//     const fileInput = document.getElementById('conventionFile');
//     const submitBtn = document.getElementById('submitConvention');
//     const cancelBtn = document.getElementById('cancelConvention');

//     if (form) {
//         form.addEventListener('submit', function(e) {
//             e.preventDefault();
            
//             const formData = new FormData();
//             const file = fileInput.files[0];
            
//             if (!file) {
//                 alert('Veuillez sélectionner un fichier');
//                 return;
//             }

//             // Validation côté client
//             if (file.size > 2 * 1024 * 1024) {
//                 alert('Le fichier ne doit pas dépasser 2 Mo');
//                 return;
//             }

//             if (file.type !== 'application/pdf') {
//                 alert('Seuls les fichiers PDF sont autorisés');
//                 return;
//             }

//             formData.append('convention', file);
            
//             // Désactiver le bouton pendant l'upload
//             submitBtn.disabled = true;
//             submitBtn.textContent = 'Téléchargement...';

//             fetch('/stalhub/secretary/upload-convention', {

//                 method: 'POST',
//                 body: formData
//             })
//             .then(response => {
//                 if (!response.ok) {
//                     throw new Error('Erreur réseau');
//                 }
//                 return response.json();
//             })
//             .then(data => {
//                 if (data.success) {
//                     alert('Convention téléchargée avec succès !');
//                     // Fermer la modal ou rediriger
//                     if (typeof closeModal === 'function') {
//                         closeModal();
//                     }
//                     // Optionnel : recharger la page pour afficher le nouveau fichier
//                     location.reload();
//                 } else {
//                     alert('Erreur : ' + data.message);
//                 }
//             })
//             .catch(error => {
//                 console.error('Erreur:', error);
//                 alert('Erreur lors du téléchargement : ' + error.message);
//             })
//             .finally(() => {
//                 // Réactiver le bouton
//                 submitBtn.disabled = false;
//                 submitBtn.textContent = 'Envoyer à l\'étudiant pour signé';
//             });
//         });
//     }

//     // Gestion du bouton annuler
//     if (cancelBtn) {
//         cancelBtn.addEventListener('click', function() {
//             if (typeof closeModal === 'function') {
//                 closeModal();
//             }
//         });
//     }
// });

// Fonction pour fermer la modal (à adapter selon votre implémentation)
// function closeModal() {
//     const modal = document.querySelector('.modal');
//     if (modal) {
//         modal.style.display = 'none';
//     }
// }


// // Fonctions pour gérer les modals
// function openConventionModal(demandeId) {
//     const modal = document.getElementById(`conventionModal-${demandeId}`);
//     if (modal) {
//         modal.style.display = 'flex';
//         document.body.style.overflow = 'hidden';
//     }
// }

// function closeConventionModal(demandeId) {
//     const modal = document.getElementById(`conventionModal-${demandeId}`);
//     if (modal) {
//         modal.style.display = 'none';
//         document.body.style.overflow = 'auto';
//     }
// }

// Fonction pour envoyer la convention à l'étudiant
// function sendConventionToStudent(demandeId) {
//     const sendBtn = document.getElementById(`sendBtn-${demandeId}`);
    
//     if (sendBtn) {
//         sendBtn.disabled = true;
//         sendBtn.textContent = 'Envoi en cours...';
//     }

//     const formData = new FormData();
//     formData.append('demande_id', demandeId);

//     fetch('/secretary/send-convention', {
//         method: 'POST',
//         body: formData
//     })
//     .then(response => {
//         if (!response.ok) {
//             throw new Error('Erreur réseau');
//         }
//         return response.json();
//     })
//     .then(data => {
//         if (data.success) {
//             alert('Convention envoyée à l\'étudiant avec succès !');
//             closeConventionModal(demandeId);
//             // Optionnel : recharger la page pour mettre à jour l'interface
//             location.reload();
//         } else {
//             alert('Erreur : ' + data.message);
//         }
//     })
//     .catch(error => {
//         console.error('Erreur:', error);
//         alert('Erreur lors de l\'envoi : ' + error.message);
//     })
//     .finally(() => {
//         if (sendBtn) {
//             sendBtn.disabled = false;
//             sendBtn.textContent = 'Envoyer à l\'étudiant pour signature';
//         }
//     });
// }

// Gestion des uploads

// JS à placer dans secretary-dashboard.js
document.addEventListener('DOMContentLoaded', () => {
  // Bouton d'ouverture de popup
  document.querySelectorAll('.upload-btn').forEach(button => {
    button.addEventListener('click', function () {
      const requestId = this.dataset.id;
      document.getElementById('request-id-hidden').value = requestId;
      document.getElementById('upload-popup').style.display = 'block';
    });
  });

  // Fermer le popup
  document.getElementById('close-popup').addEventListener('click', function () {
    document.getElementById('upload-popup').style.display = 'none';
  });

  // Validation fichier côté client
  document.getElementById('convention-file').addEventListener('change', function () {
    const file = this.files[0];
    if (file) {
      if (file.size > 10 * 1024 * 1024) {
        alert('Fichier trop volumineux (max 10MB)');
        this.value = '';
      }

      const allowedTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
      ];
      if (!allowedTypes.includes(file.type)) {
        alert('Fichier non autorisé (PDF / Word uniquement)');
        this.value = '';
      }
    }
  });

  // Envoi AJAX du formulaire
  document.getElementById('convention-form').addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    formData.append('action', 'upload_convention');

    fetch('/stalhub/secretary/upload-convention', {
      method: 'POST',
      body: formData,
    })
      .then(response => response.json())
      .then(data => {
        alert(data.message || 'Convention envoyée');
        document.getElementById('upload-popup').style.display = 'none';
        location.reload(); // ou updateRowStatus(...) si tu veux éviter le rechargement
      })
      .catch(error => {
        alert('Erreur : ' + error.message);
      });
  });
});


