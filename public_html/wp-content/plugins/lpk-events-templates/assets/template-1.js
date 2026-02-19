(() => {
  const getCurrentLanguage = () => {
    const htmlLang = (document.documentElement?.lang || '').toLowerCase();
    if (htmlLang.startsWith('fr')) return 'fr';
    if (htmlLang.startsWith('en')) return 'en';

    const path = (window.location.pathname || '').toLowerCase();
    if (path.startsWith('/fr/')) return 'fr';
    if (path.startsWith('/en/')) return 'en';

    return 'fr';
  };

  const replaceExactText = (selector, translations, lang) => {
    document.querySelectorAll(selector).forEach((node) => {
      const text = (node.textContent || '').trim();
      if (!text) return;

      Object.entries(translations).forEach(([from, toByLang]) => {
        if (text === from) {
          node.textContent = toByLang[lang] || toByLang.fr;
        }
      });
    });
  };

  const localizeTicketsModal = () => {
    const lang = getCurrentLanguage();
    const translations = {
      'Save and View Cart': { fr: 'Enregistrer et voir le panier', en: 'Save and View Cart' },
      'Checkout Now': { fr: 'Passer à la caisse', en: 'Checkout Now' },
      'or': { fr: 'ou', en: 'or' },
      'Tickets': { fr: 'Billets', en: 'Tickets' },
    };

    replaceExactText('#tribe-modal__cart .tribe-tickets__tickets-view-cart', translations, lang);
    replaceExactText('#tribe-modal__cart .tribe-tickets__tickets-buy', translations, lang);
    replaceExactText('#tribe-modal__cart .tribe-tickets__tickets-footer-separator', translations, lang);
    replaceExactText('.tribe-dialog__wrapper--ar .tribe-modal__title, .tribe-dialog__wrapper--ar .tribe-dialog__title', translations, lang);
  };

  const modalObserver = new MutationObserver(() => {
    localizeTicketsModal();
  });

  if (document.body) {
    modalObserver.observe(document.body, { childList: true, subtree: true });
  }

  document.addEventListener('DOMContentLoaded', localizeTicketsModal);
  window.addEventListener('load', localizeTicketsModal);

  const shareButtons = document.querySelectorAll('[data-lpk-share-url]');

  shareButtons.forEach((button) => {
    button.addEventListener('click', async () => {
      const url = button.getAttribute('data-lpk-share-url') || window.location.href;
      const title = document.title || 'Event';

      if (navigator.share) {
        try {
          await navigator.share({ title, url });
          return;
        } catch (error) {
          if (error && error.name === 'AbortError') {
            return;
          }
        }
      }

      if (navigator.clipboard && navigator.clipboard.writeText) {
        try {
          await navigator.clipboard.writeText(url);
          button.classList.add('is-copied');
          const originalText = button.textContent;
          button.textContent = button.dataset.copiedLabel || 'Lien copié';
          window.setTimeout(() => {
            button.textContent = originalText;
            button.classList.remove('is-copied');
          }, 1800);
          return;
        } catch (error) {
          // Fall back to opening the URL.
        }
      }

      window.open(url, '_blank', 'noopener');
    });
  });
})();
