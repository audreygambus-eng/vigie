const API = '/api';

async function lireJson(url) {
    const reponse = await fetch(url, { headers: { Accept: 'application/json' } });
    if (!reponse.ok) {
        throw new Error(`Erreur ${reponse.status} sur ${url}`);
    }
    return reponse.json();
}

function ajouterOption(liste, valeur, texte) {
    const option = document.createElement('option');
    option.value = valeur;
    option.textContent = texte;
    liste.append(option);
}

async function chargerCategories() {
    const categories = await lireJson(`${API}/categories`);
    const filtre = document.querySelector('#filtre-categorie');
    const choix = document.querySelector('#categorieId');

    for (const categorie of categories) {
        ajouterOption(filtre, categorie.id, categorie.libelle);
        ajouterOption(choix, categorie.id, categorie.libelle);
    }
}

const listeRessources = document.querySelector('#liste-ressources');

async function chargerRessources(categorieId = '') {
    const url = categorieId === ''
        ? `${API}/ressources`
        : `${API}/ressources?categorie=${encodeURIComponent(categorieId)}`;
    const ressources = await lireJson(url);

    listeRessources.replaceChildren();
    for (const ressource of ressources) {
        const lien = document.createElement('a');
        lien.href = `/ressources/${ressource.id}/consulter`;
        lien.target = '_blank';
        lien.rel = 'nofollow noopener';
        lien.textContent = ressource.titre;

        const categorie = document.createElement('span');
        categorie.textContent = ressource.categorie.libelle;

        const element = document.createElement('li');
        element.append(lien, ' — ', categorie);
        listeRessources.append(element);
    }
}

document.querySelector('#filtre-categorie').addEventListener('change', async (evenement) => {
    await chargerRessources(evenement.target.value);
});

const listeTop = document.querySelector('#top-ressources');
const topVide = document.querySelector('#top-vide');
const periode = document.querySelector('#periode');

async function chargerTop() {
    const resultat = await lireJson(`${API}/statistiques/top?jours=${encodeURIComponent(periode.value)}`);

    listeTop.replaceChildren();
    topVide.hidden = resultat.top.length > 0;

    for (const ligne of resultat.top) {
        const pluriel = ligne.nombre > 1 ? 's' : '';
        const element = document.createElement('li');
        element.textContent = `${ligne.titre} (${ligne.nombre} consultation${pluriel})`;
        listeTop.append(element);
    }
}

periode.addEventListener('change', async () => {
    await chargerTop();
});

window.addEventListener('focus', async () => {
    await chargerTop();
});

const formulaire = document.querySelector('#formulaire-ajout');
const message = document.querySelector('#message');

function effacerErreurs() {
    for (const zone of formulaire.querySelectorAll('.erreur')) {
        zone.textContent = '';
    }
    message.textContent = '';
}

function afficherErreurs(erreur) {
    if (!erreur.violations) {
        message.textContent = erreur.detail;
        return;
    }
    for (const violation of erreur.violations) {
        const zone = document.querySelector(`#erreur-${violation.propertyPath}`);
        if (zone) {
            zone.textContent = violation.title;
        }
    }
}

formulaire.addEventListener('submit', async (evenement) => {
    evenement.preventDefault();
    effacerErreurs();

    const champs = formulaire.elements;
    const donnees = {
        titre: champs.titre.value.trim(),
        url: champs.url.value.trim(),
        categorieId: champs.categorieId.value === '' ? null : Number(champs.categorieId.value),
    };

    const reponse = await fetch(`${API}/ressources`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify(donnees),
    });

    if (reponse.status === 201) {
        formulaire.reset();
        message.textContent = 'Ressource ajoutée.';
        await chargerRessources(document.querySelector('#filtre-categorie').value);
    } else if (reponse.status === 422) {
        afficherErreurs(await reponse.json());
    } else {
        message.textContent = `L'ajout a échoué (erreur ${reponse.status}).`;
    }
});

await Promise.all([chargerCategories(), chargerRessources(), chargerTop()]);