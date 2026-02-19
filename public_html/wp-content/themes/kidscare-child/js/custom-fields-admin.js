(function ($) {
    'use strict';

    const BUTTON_ID = 'lpkr-add-children-fields';
    const FIELD_LIST_SELECTOR = '#booked-cf-sortables';

    function addField(type, label, required = true) {
        const addButton = $(`.cfButton[data-type="${type}"]`).first();

        if (!addButton.length) {
            return null;
        }

        const list = $(FIELD_LIST_SELECTOR);
        const initialCount = list.children('li').length;

        addButton.trigger('click');

        const items = list.children('li');
        const newItem = items.eq(items.length - 1);

        if (!newItem.length || items.length === initialCount) {
            return null;
        }

        const textInput = newItem.find('input[type="text"], textarea').first();

        if (textInput.length && label) {
            textInput.val(label).trigger('keyup');
        }

        if (required) {
            const requiredToggle = newItem.find('.cf-required-checkbox');
            if (requiredToggle.length) {
                requiredToggle.prop('checked', true).trigger('change');
            }
        }

        return newItem;
    }

    function ensureChildrenFields() {
        const fieldList = $(FIELD_LIST_SELECTOR);

        if (!fieldList.length) {
            return;
        }

        if (!fieldList.find('input[name^="paid-service-label---"]').length) {
            addField('paid-service-label', 'Entrée enfant');
        }

        addField('single-line-text-label', "Prénom de l’enfant");
        addField('single-line-text-label', 'Date de naissance (JJ/MM/AAAA)');
    }

    function insertButton() {
        if (document.getElementById(BUTTON_ID)) {
            return;
        }

        const anchorButton = $('.booked-cf-block .cfButton').last();
        const target = anchorButton.length ? anchorButton.parent() : $('#booked-custom-fields .booked-cf-block');

        if (!target.length) {
            return;
        }

        const helperButton = $(`
            <button type="button" class="button" id="${BUTTON_ID}">
                + Ajouter les champs enfants
            </button>
        `);

        helperButton.on('click', (event) => {
            event.preventDefault();
            ensureChildrenFields();
        });

        target.append(helperButton);
    }

    $(document).ready(() => {
        if (!$('#booked-custom-fields').length) {
            return;
        }

        insertButton();
    });
})(jQuery);
