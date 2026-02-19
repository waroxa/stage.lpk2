"use strict";
jQuery(document).ready(function($) {

    //Prepares the Order refund button
    refund_button();

    // Datepicker
    $('.datepicker').each(function() {
        $(this).datepicker({dateFormat: "yy-mm-dd"});
    });

// Gift Card Product Admin Option
    var price_type = $('#_wznd_giftcard_price_type');
    $('#_wznd_giftcard_price_type').on('change', function() {
        $('.show_if_price_default,.show_if_price_range,.show_if_price_select').hide();

        if (price_type.val() == 'range') {
            $('.show_if_price_range').show();
        }
        if (price_type.val() == 'select') {
            $('.show_if_price_select').show();
        }
    });
    if (price_type.val() == 'range') {
        $('.show_if_price_range').show();
    }
    if (price_type.val() == 'select') {
        $('.show_if_price_select').show();
    }



//Wallet Admin buttons
    $('.credit-wallet').on('click', function() {
        wooznd_wallet_add_credit($(this));
        return false;
    });

    $('.debit-wallet').on('click', function() {
        wooznd_wallet_subtract_credit($(this));
        return false;
    });

    $('.view-wallet').on('click', function() {
        wooznd_wallet_view_details($(this));
        return false;
    });

    $('.new-wallet').on('click', function() {
        wooznd_wallet_new($(this));
        return false;
    });

    $('.new-giftcard').on('click', function() {
        wooznd_giftcard_new($(this));
        return false;
    });
    $('.edit-giftcard').on('click', function() {
        wooznd_giftcard_edit($(this));
        return false;
    });


//Transactions view button
    $('.view-transaction').on('click', function() {
        wooznd_transaction_view_details($(this));
        return false;
    });

    //Refund view button
    $('.view-refund').on('click', function() {
        wooznd_refund_view_details($(this));
        return false;
    });

    function wooznd_wallet_add_credit(obj) {
        var mod = $.Popup('cwoo_w');
        var templ = obj.parent().find('.credit-wallet-template');
        mod.show({content: templ.html(), title: templ.attr('data-title'), width: '730px', css_class: 'woo_wallet'});
    }

    function wooznd_wallet_subtract_credit(obj) {
        var mod = $.Popup('dwoo_w');
        var templ = obj.parent().find('.debit-wallet-template');
        mod.show({content: templ.html(), title: templ.attr('data-title'), width: '730px', css_class: 'woo_wallet'});
    }

    function wooznd_wallet_view_details(obj) {
        var mod = $.Popup('vwoo_w');
        var templ = obj.parent().parent().find('.view-wallet-template');
        mod.show({content: templ.html(), title: templ.attr('data-title'), width: '730px', css_class: 'woo_wallet'});
    }

    function wooznd_wallet_new(obj) {
        var mod = $.Popup('nwoo_w');
        var templ = obj.parent().find('.new-wallet-template');
        mod.show({content: templ.html(), title: templ.attr('data-title'), width: '730px', css_class: 'woo_wallet'});
    }

    function wooznd_transaction_view_details(obj) {
        var mod = $.Popup('vwoo_tr');
        var templ = obj.parent().find('.view-transaction-template');
        mod.show({content: templ.html(), title: templ.attr('data-title'), width: '730px', css_class: 'woo_wallet'});
    }

    function wooznd_refund_view_details(obj) {
        var mod = $.Popup('vwoo_ref');
        var templ = obj.parent().find('.view-refund-template');
        mod.show({content: templ.html(), title: templ.attr('data-title'), width: '730px', css_class: 'woo_wallet'});
    }

    function refund_button() {
        $('.refund-actions').prepend($('.woo_wallet_hidden').html());

        $('.wallet-refund').on('click', function() {
            return confirm($(this).attr('data-warnme'));
        });
    }


    function wooznd_giftcard_new(obj) {
        var mod = $.Popup('nwoo_g');
        var templ = obj.parent().find('.new-giftcard-template');
        mod.show({content: templ.html(), title: templ.attr('data-title'), width: '750px', css_class: 'woo_wallet'});

        // Datepicker
        mod.get_content().find('.pop_datepicker').each(function() {
            $(this).datepicker({dateFormat: "yy-mm-dd"});
        });

        // UI Tabs
        mod.get_content().find('.wsp-tabs').wooznd_ui_tab();
    }

    function wooznd_giftcard_edit(obj) {
        var mod = $.Popup('edwoo_g');
        var templ = obj.parent().find('.edit-giftcard-template');
        mod.show({content: templ.html(), title: templ.attr('data-title'), width: '750px', css_class: 'woo_wallet'});
        // Datepicker
        mod.get_content().find('.pop_datepicker').each(function() {
            $(this).datepicker({dateFormat: "yy-mm-dd"});
        });

        // UI Tabs
        mod.get_content().find('.wsp-tabs').wooznd_ui_tab();
    }
});





(function($) {

    $.fn.wooznd_ui_tab = function(options) {

        return this.each(function() {
            // Default options.
            var settings = $.extend({
                head_class: '.wsp-tabs-head',
                head_active_class: 'wsp-active',
                body_active_class: 'wsp-active'
            }, options);
            var obj = $(this);

            obj.find(settings.head_class).find('li a').each(function() {
                var anc = $(this);
                anc.on('click', function() {

                    anc.parent().siblings().removeClass(settings.head_active_class);
                    anc.parent().addClass(settings.head_active_class);

                    var tab_body = $('.popup-content').find(anc.attr('href'));
                    tab_body.siblings().removeClass(settings.body_active_class);
                    tab_body.addClass(settings.body_active_class);
                    return false;
                });
            });

        });


    };

}(jQuery));
