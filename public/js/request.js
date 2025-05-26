document.addEventListener("DOMContentLoaded", function () {
    const countrySelect = document.getElementById("country");
    const siretGroup = document.getElementById("siret-group");
    const siretInput = document.getElementById("siret");
    // Supprimer les espaces dès que l'utilisateur tape
    siretInput.addEventListener('input', () => {
        siretInput.value = siretInput.value.replace(/\s+/g, '');
    });

    const manualNote = document.getElementById("manual-entry-note");

    const autoFields = ['company_name', 'city', 'postal_code'];

    function toggleSiretField() {
        const isFrance = countrySelect.value === "France";

        siretGroup.style.display = isFrance ? "block" : "none";
        siretInput.required = isFrance;

        if (manualNote) {
            manualNote.style.display = isFrance ? "none" : "block";
        }

        if (!isFrance) {
            siretInput.value = '';
            document.getElementById('siret-result').innerText = '';

            autoFields.forEach(name => {
                const field = document.querySelector(`input[name="${name}"]`);
                if (field) {
                    field.readOnly = false;
                    field.dataset.originalReadonly = "false";
                }
            });
        }
    }

    countrySelect.addEventListener("change", toggleSiretField);
    toggleSiretField(); // Initialisation au chargement

    siretInput.addEventListener('blur', function () {
        const siret = this.value.trim();
        const resultDiv = document.getElementById('siret-result');

        if (siret.length === 14 && countrySelect.value === "France") {
            fetch('/stalhub/public/api/siret-check.php?siret=' + encodeURIComponent(siret))
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        resultDiv.innerHTML = `✅ Entreprise : ${data.nom}<br>🏢 Adresse : ${data.adresse}`;
                        resultDiv.style.color = 'green';

                        document.querySelector('input[name="company_name"]').value = data.nom || '';
                        document.querySelector('input[name="city"]').value = data.city || '';
                        document.querySelector('input[name="postal_code"]').value = data.postal_code || '';

                        autoFields.forEach(name => {
                            const field = document.querySelector(`input[name="${name}"]`);
                            if (field) {
                                field.readOnly = true;
                                field.dataset.originalReadonly = "true";
                            }
                        });
                    } else {
                        resultDiv.innerHTML = ` ${data.message}`;
                        resultDiv.style.color = 'red';

                        autoFields.forEach(name => {
                            const field = document.querySelector(`input[name="${name}"]`);
                            if (field) {
                                field.value = '';
                                field.readOnly = false;
                                field.dataset.originalReadonly = "false";
                            }
                        });
                    }
                })
                .catch(err => {
                    console.error(' Erreur JS :', err);
                    resultDiv.innerText = 'Erreur de requête.';
                    resultDiv.style.color = 'red';
                });
        }
    });
});
