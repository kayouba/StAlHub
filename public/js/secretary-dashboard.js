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
    const etat = row.children[6].textContent.toLowerCase(); 
    const fullText = row.textContent.toLowerCase();

    const matchFormation = !formationVal || formation.includes(formationVal);
    const matchEtat = !etatVal || etat.includes(etatVal);
    const matchSearch = !searchVal || fullText.includes(searchVal);

    row.style.display = (matchFormation && matchEtat && matchSearch) ? "" : "none";
  });
}

Object.values(filters).forEach(el => el.addEventListener("input", filterTable));

document.addEventListener('DOMContentLoaded', () => {
  // Bouton d'ouverture de popup
  document.querySelectorAll('.upload-btn').forEach(button => {
    button.addEventListener('click', function () {
      const requestId = this.dataset.id;
      document.getElementById('request-id-hidden').value = requestId;
      document.getElementById('upload-popup').style.display = 'flex';

    });
  });

  // Fermer le popup
  document.getElementById('close-popup').addEventListener('click', function () {
    document.getElementById('upload-popup').style.display = 'none';
  });

  // Validation du fichier 
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

      // Récupérer l'id de la demande 
      const demandeId = document.getElementById('request-id-hidden').value;

      const uploadBtn = document.querySelector(`button.upload-btn[data-id="${demandeId}"]`);
      if (uploadBtn) {
        uploadBtn.style.display = 'none'; 
      }
    })

      .catch(error => {
        alert('Erreur : ' + error.message);
      });
  });
});


