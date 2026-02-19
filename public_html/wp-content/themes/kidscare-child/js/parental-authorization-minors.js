(function () {
    'use strict';

    const onReady = (callback) => {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback, { once: true });
        } else {
            callback();
        }
    };

    const replaceIndexTokens = (value, index) => {
        if (typeof value !== 'string') {
            return value;
        }

        const indexString = String(index);

        return value
            .replace(/__index__/g, indexString)
            .replace(/minors\[\d+]/, `minors[${indexString}]`)
            .replace(/minor-\d+-/g, `minor-${indexString}-`);
    };

    onReady(() => {
        const section = document.querySelector('[data-minors-wrapper]');

        if (!section) {
            return;
        }

        const repeater = section.querySelector('[data-minors]');
        const itemsContainer = section.querySelector('[data-minors-items]');
        const addButton = section.querySelector('[data-add-minor]');
        const templateEl = document.getElementById('kidscare-minor-template');

        if (!repeater || !itemsContainer || !addButton || !templateEl) {
            return;
        }

        const renumberCards = () => {
            const cards = Array.from(itemsContainer.querySelectorAll('[data-minor]'));

            cards.forEach((card, index) => {
                const numberEl = card.querySelector('[data-minor-number]');

                if (numberEl) {
                    numberEl.textContent = index + 1;
                }

                card.querySelectorAll('input').forEach((input) => {
                    const originalName = input.getAttribute('name');
                    const originalId = input.getAttribute('id');

                    if (originalName) {
                        input.setAttribute('name', replaceIndexTokens(originalName, index));
                    }

                    if (originalId) {
                        input.id = replaceIndexTokens(originalId, index);
                    }
                });

                card.querySelectorAll('label[for]').forEach((label) => {
                    const originalFor = label.getAttribute('for');

                    if (originalFor) {
                        label.setAttribute('for', replaceIndexTokens(originalFor, index));
                    }
                });

                const removeButton = card.querySelector('[data-minor-remove]');

                if (removeButton) {
                    const labelTemplate = removeButton.getAttribute('data-remove-label-template');

                    if (labelTemplate && labelTemplate.includes('%d')) {
                        removeButton.setAttribute('aria-label', labelTemplate.replace('%d', String(index + 1)));
                    }
                }
            });

            toggleRemoveAvailability(cards.length);
        };

        const toggleRemoveAvailability = (count) => {
            const allowRemoval = count > 1;

            itemsContainer.querySelectorAll('[data-minor-remove]').forEach((button) => {
                button.disabled = !allowRemoval;
                button.setAttribute('aria-disabled', allowRemoval ? 'false' : 'true');
                button.classList.toggle('is-disabled', !allowRemoval);
            });
        };

        const addMinorCard = () => {
            const fragment = templateEl.content.cloneNode(true);
            itemsContainer.appendChild(fragment);
            const cards = itemsContainer.querySelectorAll('[data-minor]');
            const newCard = cards[cards.length - 1];

            if (newCard) {
                newCard.querySelectorAll('input').forEach((input) => {
                    input.value = '';
                });
            }

            renumberCards();

            if (newCard) {
                const firstField = newCard.querySelector('input');

                if (firstField) {
                    firstField.focus();
                }
            }
        };

        addButton.addEventListener('click', (event) => {
            event.preventDefault();
            addMinorCard();
        });

        section.addEventListener('click', (event) => {
            const removeButton = event.target.closest('[data-minor-remove]');

            if (!removeButton || removeButton.disabled) {
                return;
            }

            event.preventDefault();

            const card = removeButton.closest('[data-minor]');

            if (!card) {
                return;
            }

            const cards = itemsContainer.querySelectorAll('[data-minor]');

            if (cards.length <= 1) {
                return;
            }

            card.remove();
            renumberCards();
        });

        renumberCards();
    });
})();
