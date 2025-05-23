
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
