// Variables globales
let modeAffectationAuto = false;
let tuteurAutoSelectionne = null;

// ============================================================================
// GESTION DE LA REDIRECTION AUTOMATIQUE 
// ============================================================================

/**
 * Vérifie si la page doit être redirigée automatiquement après traitement
 * Cette fonction s'exécute au chargement de la page
 */
function checkAutoRedirect() {
    // Récupération des paramètres URL
    const urlParams = new URLSearchParams(window.location.search);
    const processed = urlParams.get('processed');
    
    // Si la demande a été traitée avec succès
    if (processed === 'success') {
        // Vérifier qu'il y a bien un message de succès à afficher
        const flashMessage = document.querySelector('.flash-message.success');
        
        if (flashMessage) {
            // Ajouter une indication visuelle de redirection
            addRedirectCounter();
            
            // Redirection automatique après 4 secondes
            setTimeout(() => {
                window.location.href = "/stalhub/responsable/requestList";
            }, 4000);
        }
    }
}

/**
 * Ajoute un compteur visuel pour la redirection automatique
 */
function addRedirectCounter() {
    const flashMessage = document.querySelector('.flash-message.success');
    if (!flashMessage) return;
    
    // Création du compteur
    const redirectInfo = document.createElement('div');
    redirectInfo.className = 'redirect-info';
    redirectInfo.innerHTML = `
        <div style="margin-top: 10px; padding: 10px; background: rgba(255,255,255,0.2); border-radius: 4px; font-size: 14px;">
            <i class="fas fa-clock"></i> Redirection automatique dans <span id="countdown">3</span> secondes...
            <button onclick="redirectNow()" style="margin-left: 10px; padding: 2px 8px; background: white; border: none; border-radius: 3px; cursor: pointer;">
                Continuer maintenant
            </button>
        </div>
    `;
    
    flashMessage.appendChild(redirectInfo);
    
    // Démarrer le décompte
    startCountdown();
}

/**
 * Démarre le décompte visuel
 */
function startCountdown() {
    let seconds = 3;
    const countdownElement = document.getElementById('countdown');
    
    const interval = setInterval(() => {
        seconds--;
        if (countdownElement) {
            countdownElement.textContent = seconds;
        }
        
        if (seconds <= 0) {
            clearInterval(interval);
        }
    }, 1000);
}

/**
 * Redirige immédiatement vers la liste
 */
function redirectNow() {
    window.location.href = "/stalhub/responsable/requestList";
}

// ============================================================================
// FONCTIONS ORIGINALES 
// ============================================================================

// Affiche ou cache les détails d'un élément
function toggleCard(id) {
  const content = document.getElementById(`content-${id}`);
  const icon = document.getElementById(`icon-${id}`).firstChild;
  if (content.style.display === 'none') {
    content.style.display = 'block';
    icon.classList.replace('fa-plus', 'fa-minus');
  } else {
    content.style.display = 'none';
    icon.classList.replace('fa-minus', 'fa-plus');
  }
}

// Active/désactive le mode d'affectation automatique
function toggleAffectationMode() {
  const auto = document.getElementById('affectationAuto').checked;
  const select = document.getElementById('selectionManuelle');
  const bouton = document.getElementById('btnValider');
  const selection = document.getElementById('tuteurSelectionne');
  const selectElement = document.getElementById('tuteur_id');
  modeAffectationAuto = auto;
  if (auto) {
    select.style.display = 'none';
    selection.style.display = 'block';
    const options = Array.from(selectElement.options)
      .filter(opt => opt.value && !opt.disabled);
    if (options.length > 0) {
      const best = options.reduce((a, b) =>
        parseInt(a.dataset.quotaActuel) < parseInt(b.dataset.quotaActuel) ? a : b
      );
      selectElement.value = best.value;
      document.getElementById('tuteurNomSelectionne').innerText = best.text;
      bouton.disabled = false;
      tuteurAutoSelectionne = {
        id: best.value,
        nom: best.text,
        quota: best.dataset.quotaActuel
      };
    }
  } else {
    select.style.display = 'block';
    selection.style.display = 'none';
    bouton.disabled = true;
    selectElement.value = '';
    tuteurAutoSelectionne = null;
  }
}

// Passe manuellement en mode manuel
function changerTuteur() {
  document.getElementById('affectationAuto').checked = false;
  toggleAffectationMode();
}

// Suggestion manuelle d'un tuteur avec quota le plus bas
function suggererTuteur() {
  const selectElement = document.getElementById('tuteur_id');
  const options = Array.from(selectElement.options)
    .filter(opt => opt.value && !opt.disabled);
  if (options.length > 0) {
    const best = options.reduce((a, b) =>
      parseInt(a.dataset.quotaActuel) < parseInt(b.dataset.quotaActuel) ? a : b
    );
    selectElement.value = best.value;
    document.getElementById('btnValider').disabled = false;
  }
}

// ============================================================================
// INITIALISATION
// ============================================================================

// Active/désactive le bouton "Valider" selon la sélection
document.addEventListener('DOMContentLoaded', function () {
  // Vérifier la redirection automatique en premier
  checkAutoRedirect();
  
  // Initialisation des événements originaux
  const selectElement = document.getElementById('tuteur_id');
  const btnValider = document.getElementById('btnValider');
  if (selectElement && btnValider) {
    selectElement.addEventListener('change', () => {
      btnValider.disabled = selectElement.value === '';
    });
  }
});