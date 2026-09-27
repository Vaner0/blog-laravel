const flash = document.querySelector('[data-flash]');
const articleStatus = document.querySelector('[data-article-status]');
const articleSubmit = document.querySelector('[data-article-submit]');
const articleForm = document.querySelector('[data-article-form]');
const articleImage = document.querySelector('[data-article-image]');
const imageStatus = document.querySelector('[data-image-status]');
const imageError = document.querySelector('[data-image-error]');

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

if (
    articleForm instanceof HTMLFormElement
    && articleImage instanceof HTMLInputElement
    && articleSubmit instanceof HTMLButtonElement
    && imageStatus instanceof HTMLElement
    && imageError instanceof HTMLElement
) {
    let isSubmitting = false;

    articleForm.addEventListener('submit', async (event) => {
        const image = articleImage.files?.[0];

        if (!image || articleForm.dataset.imageOptimized === 'true') {
            return;
        }

        event.preventDefault();

        if (isSubmitting) {
            return;
        }

        isSubmitting = true;
        articleSubmit.disabled = true;
        articleSubmit.textContent = 'Optimisation de l’image…';
        imageError.hidden = true;
        imageStatus.textContent = 'Redimensionnement et compression de l’image avant l’envoi…';

        try {
            const optimizedImage = await optimizeImage(image);
            const transfer = new DataTransfer();

            transfer.items.add(optimizedImage);
            articleImage.files = transfer.files;
            articleForm.dataset.imageOptimized = 'true';
            imageStatus.textContent = `Image optimisée : ${formatFileSize(optimizedImage.size)}. Envoi en cours…`;
            articleForm.submit();
        } catch (error) {
            imageError.textContent = error instanceof Error
                ? error.message
                : 'Impossible de préparer cette image. Essaie un fichier JPEG, PNG ou WebP.';
            imageError.hidden = false;
            imageStatus.textContent = '';
            articleSubmit.disabled = false;
            isSubmitting = false;
        }
    });
}

async function optimizeImage(image) {
    let bitmap;

    try {
        bitmap = await createImageBitmap(image);
    } catch {
        throw new Error('Cette image ne peut pas être lue par le navigateur. Essaie un fichier JPEG, PNG ou WebP.');
    }

    try {
        const maxDimension = 1600;
        const maxOutputSize = 4 * 1024 * 1024;
        const initialScale = Math.min(1, maxDimension / Math.max(bitmap.width, bitmap.height));
        let scale = initialScale;
        let quality = 0.82;

        for (let attempt = 0; attempt < 8; attempt++) {
            const canvas = document.createElement('canvas');
            canvas.width = Math.max(1, Math.round(bitmap.width * scale));
            canvas.height = Math.max(1, Math.round(bitmap.height * scale));

            const context = canvas.getContext('2d');

            if (!context) {
                throw new Error('Le navigateur ne peut pas préparer cette image.');
            }

            context.drawImage(bitmap, 0, 0, canvas.width, canvas.height);

            const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/webp', quality));
            canvas.width = 0;
            canvas.height = 0;

            if (!blob) {
                throw new Error('Le navigateur ne peut pas convertir cette image en WebP.');
            }

            if (blob.size <= maxOutputSize) {
                const baseName = image.name.replace(/\.[^.]+$/, '');

                return new File([blob], `${baseName}.webp`, {
                    type: 'image/webp',
                    lastModified: Date.now(),
                });
            }

            quality = Math.max(0.45, quality - 0.08);

            if (attempt >= 2) {
                scale *= 0.8;
            }
        }
    } finally {
        bitmap.close();
    }

    throw new Error('Cette image reste trop volumineuse après compression. Essaie une image de plus petite résolution.');
}

function formatFileSize(bytes) {
    return `${(bytes / (1024 * 1024)).toFixed(1)} Mo`;
}
