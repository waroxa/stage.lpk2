(function () {
    const FRENCH_PATH_REGEX = /\/fr(?:\/|$)/i;

    const translations = {
        fr: {
            exact: {
                'Save and View Cart': 'Enregistrer et voir le panier',
                'Checkout Now': 'Passer au paiement',
                'More': 'Plus',
                'Less': 'Moins',
                'Quantity:': 'Quantité :',
                'Total:': 'Total :',
                'or': 'ou'
            },
            suffix: [
                { from: /\bTickets\.?$/i, to: 'Billets' }
            ]
        },
        en: {
            exact: {
                'Enregistrer et voir le panier': 'Save and View Cart',
                'Passer au paiement': 'Checkout Now',
                'Plus': 'More',
                'Moins': 'Less',
                'Quantité :': 'Quantity:',
                'Quantité:': 'Quantity:',
                'Total :': 'Total:',
                'ou': 'or'
            },
            suffix: [
                { from: /\bBillets\.?$/i, to: 'Tickets' }
            ]
        }
    };

    const getLanguage = () => {
        const htmlLang = (document.documentElement.getAttribute('lang') || '').toLowerCase();

        if (htmlLang.startsWith('fr')) {
            return 'fr';
        }

        if (htmlLang.startsWith('en')) {
            return 'en';
        }

        return FRENCH_PATH_REGEX.test(window.location.pathname) ? 'fr' : 'en';
    };

    const normalizeText = (value) => value.replace(/\s+/g, ' ').trim();

    const localizeNode = (node, dictionary) => {
        if (!node || !node.textContent) {
            return;
        }

        const original = node.textContent;
        const trimmed = normalizeText(original);

        if (!trimmed) {
            return;
        }

        if (Object.prototype.hasOwnProperty.call(dictionary.exact, trimmed)) {
            node.textContent = original.replace(trimmed, dictionary.exact[trimmed]);
            return;
        }

        let updated = original;

        dictionary.suffix.forEach((rule) => {
            updated = updated.replace(rule.from, rule.to);
        });

        if (updated !== original) {
            node.textContent = updated;
        }
    };

    const translateModal = () => {
        const modal = document.getElementById('tribe-modal__cart');

        if (!modal) {
            return;
        }

        const language = getLanguage();
        const dictionary = translations[language] || translations.fr;
        const selectors = [
            '.tribe-tickets__tickets-title',
            '.tribe-tickets__tickets-item-title',
            '.tribe-tickets__tickets-item-details-summary-button--more',
            '.tribe-tickets__tickets-item-details-summary-button--less',
            '.tribe-common-c-btn',
            '.tribe-common-c-btn-link',
            '.tribe-common-c-btn-border',
            '.tribe-tickets__tickets-footer',
            '.tribe-tickets__tickets-quantity',
            '.tribe-tickets__tickets-total'
        ];

        modal.querySelectorAll(selectors.join(',')).forEach((el) => {
            if (el.childNodes.length === 1 && el.firstChild.nodeType === Node.TEXT_NODE) {
                localizeNode(el.firstChild, dictionary);
                return;
            }

            el.childNodes.forEach((child) => {
                if (child.nodeType === Node.TEXT_NODE) {
                    localizeNode(child, dictionary);
                }
            });
        });
    };

    const observeModal = () => {
        const observer = new MutationObserver(() => {
            translateModal();
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
            characterData: true
        });
    };

    document.addEventListener('DOMContentLoaded', function () {
        translateModal();
        observeModal();
    });
})();
