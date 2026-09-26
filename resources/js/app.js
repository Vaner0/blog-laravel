const flash = document.querySelector('[data-flash]');
const articleStatus = document.querySelector('[data-article-status]');
const articleSubmit = document.querySelector('[data-article-submit]');

if (flash) {
    window.setTimeout(() => {
        flash.classList.add('is-hidden');
        window.setTimeout(() => flash.remove(), 250);
    }, 3000);
}

if (articleStatus instanceof HTMLSelectElement && articleSubmit instanceof HTMLButtonElement) {
    const updateSubmitLabel = () => {
        const isExisting = articleSubmit.dataset.existing === 'true';
        const initialStatus = articleSubmit.dataset.initialStatus;

        if (articleStatus.value === 'brouillon') {
            articleSubmit.textContent = 'Enregistrer le brouillon';
        } else if (isExisting && initialStatus === 'publie') {
            articleSubmit.textContent = 'Enregistrer les modifications';
        } else {
            articleSubmit.textContent = 'Publier l’article';
        }
    };

    articleStatus.addEventListener('change', updateSubmitLabel);
    updateSubmitLabel();
}
