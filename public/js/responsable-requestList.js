/**
 * GESTIONNAIRE DE FILTRAGE POUR LA LISTE DES DEMANDES
 * ===================================================
 * 
 * Ce fichier gère le filtrage dynamique des demandes dans le tableau.
 * Fonctionnalités :
 * - Recherche textuelle (étudiant, entreprise)
 * - Filtrage par formation, date, type et état
 * - Réinitialisation des filtres
 * - Comptage des résultats affichés
 * 
 * @author groupe 1
 * @version 1.0
 */

// ============================================================================
// VARIABLES GLOBALES ET ÉLÉMENTS DOM
// ============================================================================

/**
 * Éléments de filtrage récupérés du DOM
 */
const filterElements = {
    search: document.getElementById("searchInput"),
    formation: document.getElementById("filterFormation"),
    date: document.getElementById("filterDate"),
    type: document.getElementById("filterType"),
    etat: document.getElementById("filterEtat"),
    resetBtn: document.getElementById("resetFilters")
};

/**
 * Éléments du tableau et compteurs
 */
const tableElements = {
    table: document.getElementById("demandesTable"),
    tbody: document.getElementById("demandesTable").getElementsByTagName("tbody")[0],
    visibleCount: document.getElementById("visibleCount"),
    totalCount: document.getElementById("totalCount")
};

// ============================================================================
// FONCTIONS PRINCIPALES
// ============================================================================

/**
 * Fonction principale de filtrage du tableau
 * Applique tous les filtres simultanément et met à jour l'affichage
 */
function filterTable() {
    // Récupération des valeurs des filtres
    const filters = {
        search: filterElements.search.value.toLowerCase().trim(),
        formation: filterElements.formation.value,
        date: filterElements.date.value,
        type: filterElements.type.value,
        etat: filterElements.etat.value
    };

    let visibleRows = 0;
    const totalRows = tableElements.tbody.rows.length;

    // Parcourir chaque ligne du tableau
    for (let row of tableElements.tbody.rows) {
        const isVisible = checkRowVisibility(row, filters);
        
        // Afficher ou masquer la ligne
        row.style.display = isVisible ? "" : "none";
        
        // Compter les lignes visibles
        if (isVisible) {
            visibleRows++;
        }
    }

    // Mettre à jour le compteur de résultats
    updateResultsCounter(visibleRows, totalRows);
}

/**
 * Vérifie si une ligne du tableau doit être affichée selon les filtres
 * @param {HTMLTableRowElement} row - La ligne à vérifier
 * @param {Object} filters - Objet contenant tous les filtres actifs
 * @returns {boolean} - True si la ligne doit être affichée
 */
function checkRowVisibility(row, filters) {
    const cells = row.getElementsByTagName("td");
    
    // Vérifier que la ligne a bien des cellules
    if (cells.length === 0) return false;

    // Test du filtre de recherche textuelle
    const matchSearch = checkSearchFilter(cells, filters.search);
    
    // Test des filtres spécifiques
    const matchFormation = checkSpecificFilter(cells[1], filters.formation);
    const matchDate = checkSpecificFilter(cells[3], filters.date);
    const matchType = checkSpecificFilter(cells[4], filters.type);
    const matchEtat = checkEtatFilter(cells[5], filters.etat);

    // La ligne est visible si tous les filtres correspondent
    return matchSearch && matchFormation && matchDate && matchType && matchEtat;
}

/**
 * Vérifie si la recherche textuelle correspond
 * @param {HTMLCollection} cells - Les cellules de la ligne
 * @param {string} searchTerm - Terme de recherche
 * @returns {boolean} - True si au moins une cellule contient le terme
 */
function checkSearchFilter(cells, searchTerm) {
    if (!searchTerm) return true; // Pas de filtre de recherche
    
    return Array.from(cells).some(cell => 
        cell.textContent.toLowerCase().includes(searchTerm)
    );
}

/**
 * Vérifie si un filtre spécifique correspond
 * @param {HTMLTableCellElement} cell - La cellule à vérifier
 * @param {string} filterValue - Valeur du filtre
 * @returns {boolean} - True si correspond ou pas de filtre
 */
function checkSpecificFilter(cell, filterValue) {
    if (!filterValue) return true; // Pas de filtre
    return cell.textContent.trim() === filterValue;
}

/**
 * Vérifie le filtre d'état (cas spécial avec span et classes CSS)
 * @param {HTMLTableCellElement} cell - La cellule contenant l'état
 * @param {string} etatFilter - Valeur du filtre d'état
 * @returns {boolean} - True si correspond ou pas de filtre
 */
function checkEtatFilter(cell, etatFilter) {
    if (!etatFilter) return true; // Pas de filtre d'état
    
    const spanElement = cell.querySelector('span');
    if (!spanElement) return false;
    
    return spanElement.classList.contains(etatFilter);
}

/**
 * Met à jour le compteur de résultats affichés
 * @param {number} visible - Nombre de lignes visibles
 * @param {number} total - Nombre total de lignes
 */
function updateResultsCounter(visible, total) {
    if (tableElements.visibleCount) {
        tableElements.visibleCount.textContent = visible;
    }
    if (tableElements.totalCount) {
        tableElements.totalCount.textContent = total;
    }
}

/**
 * Réinitialise tous les filtres à leur valeur par défaut
 */
function resetAllFilters() {
    filterElements.search.value = "";
    filterElements.formation.value = "";
    filterElements.date.value = "";
    filterElements.type.value = "";
    filterElements.etat.value = "";
    
    // Réappliquer le filtrage (tout sera visible)
    filterTable();
    
    // Message de confirmation (optionnel)
    console.log("Filtres réinitialisés");
}

/**
 * Ajoute une animation subtile lors du filtrage
 * @param {HTMLTableRowElement} row - La ligne à animer
 * @param {boolean} show - True pour afficher, false pour masquer
 */
function animateRowToggle(row, show) {
    if (show) {
        row.style.opacity = "0";
        row.style.display = "";
        // Animation fade-in
        setTimeout(() => {
            row.style.transition = "opacity 0.3s ease";
            row.style.opacity = "1";
        }, 10);
    } else {
        row.style.transition = "opacity 0.3s ease";
        row.style.opacity = "0";
        setTimeout(() => {
            row.style.display = "none";
        }, 300);
    }
}

// ============================================================================
// GESTIONNAIRES D'ÉVÉNEMENTS
// ============================================================================

/**
 * Initialisation des événements après chargement du DOM
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log("Initialisation du système de filtrage...");
    
    // Vérifier que tous les éléments nécessaires sont présents
    if (!validateRequiredElements()) {
        console.error("Certains éléments requis sont manquants dans le DOM");
        return;
    }

    // Attacher les événements de filtrage
    attachFilterEvents();
    
    // Initialiser les compteurs
    filterTable();
    
    console.log("Système de filtrage initialisé avec succès");
});

/**
 * Valide que tous les éléments DOM requis sont présents
 * @returns {boolean} - True si tous les éléments sont présents
 */
function validateRequiredElements() {
    const requiredElements = [
        filterElements.search,
        filterElements.formation,
        filterElements.date,
        filterElements.type,
        filterElements.etat,
        filterElements.resetBtn,
        tableElements.table,
        tableElements.tbody
    ];
    
    return requiredElements.every(element => element !== null);
}

/**
 * Attache tous les gestionnaires d'événements
 */
function attachFilterEvents() {
    // Événements de filtrage en temps réel
    filterElements.search.addEventListener("input", filterTable);
    filterElements.formation.addEventListener("change", filterTable);
    filterElements.date.addEventListener("change", filterTable);
    filterElements.type.addEventListener("change", filterTable);
    filterElements.etat.addEventListener("change", filterTable);
    
    // Événement de réinitialisation
    filterElements.resetBtn.addEventListener("click", resetAllFilters);
    
    // Événements pour améliorer l'UX
    addUXEnhancements();
}

/**
 * Ajoute des améliorations pour l'expérience utilisateur
 */
function addUXEnhancements() {
    // Effacer la recherche avec Escape
    filterElements.search.addEventListener("keydown", function(event) {
        if (event.key === "Escape") {
            this.value = "";
            filterTable();
            this.blur(); // Retirer le focus
        }
    });
    
    // Feedback visuel lors du survol des lignes
    tableElements.tbody.addEventListener("mouseover", function(event) {
        if (event.target.closest("tr")) {
            event.target.closest("tr").style.backgroundColor = "#f8f9fa";
        }
    });
    
    tableElements.tbody.addEventListener("mouseout", function(event) {
        if (event.target.closest("tr")) {
            event.target.closest("tr").style.backgroundColor = "";
        }
    });
}

// ============================================================================
// FONCTIONS UTILITAIRES PUBLIQUES
// ============================================================================

/**
 * API publique pour contrôler les filtres depuis l'extérieur
 */
window.RequestListFilters = {
    /**
     * Applique un filtre programmatiquement
     * @param {string} filterType - Type de filtre (search, formation, etc.)
     * @param {string} value - Valeur à appliquer
     */
    setFilter: function(filterType, value) {
        if (filterElements[filterType]) {
            filterElements[filterType].value = value;
            filterTable();
        }
    },
    
    /**
     * Récupère les filtres actuels
     * @returns {Object} - Objet contenant tous les filtres actifs
     */
    getFilters: function() {
        return {
            search: filterElements.search.value,
            formation: filterElements.formation.value,
            date: filterElements.date.value,
            type: filterElements.type.value,
            etat: filterElements.etat.value
        };
    },
    
    /**
     * Réinitialise tous les filtres
     */
    reset: resetAllFilters,
    
    /**
     * Applique le filtrage manuellement
     */
    refresh: filterTable
};