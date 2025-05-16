document.getElementById('siret').addEventListener('blur', function () {
    const siret = this.value.trim();
    if (siret.length === 14) {
        fetch('/stalhub/public/api/siret-check.php?siret=' + siret)
            .then(res => res.json())
            .then(data => {
                const resultDiv = document.getElementById('siret-result');

                if (data.success) {
                    resultDiv.innerHTML = `✅ Entreprise : ${data.nom}<br>🏢 Adresse : ${data.adresse}`;
                    resultDiv.style.color = 'green';

                    // Remplissage automatique des champs
                    document.querySelector('input[name="company_name"]').value = data.nom || '';
                    document.querySelector('input[name="siren"]').value = data.siren || '';
                    document.querySelector('input[name="city"]').value = data.city || '';
                    document.querySelector('input[name="postal_code"]').value = data.postal_code || '';

                    // Optionnel : rendre les champs readonly pour éviter la modification
                    document.querySelector('input[name="company_name"]').readOnly = true;
                    document.querySelector('input[name="siren"]').readOnly = true;
                    document.querySelector('input[name="city"]').readOnly = true;
                    document.querySelector('input[name="postal_code"]').readOnly = true;

                } else {
                    resultDiv.innerHTML = `❌ ${data.message}`;
                    resultDiv.style.color = 'red';

                    // Reset des champs si invalide
                    ['company_name', 'siren', 'city', 'postal_code'].forEach(name => {
                        const field = document.querySelector(`input[name="${name}"]`);
                        if (field) {
                            field.value = '';
                            field.readOnly = false;
                        }
                    });
                }
            })
            .catch(err => {
                console.error('❌ Erreur JS :', err);
                document.getElementById('siret-result').innerText = 'Erreur de requête.';
            });
    }
});
