"use strict";
jQuery(document).ready(function($) {

    // Gift card datepicker
    $('.datepicker').each(function() {
        $(this).datepicker({dateFormat: "yy-mm-dd"});
    });

    // Gift card delivery method
    var delv = $('.wznd_gift_card_delivery select');

    if (delv.val() == '2') {
        hide_show_email_fields(true);
    }
    delv.on('change', function() {
        if (delv.val() == '2') {
            hide_show_email_fields(true);
        } else {
            hide_show_email_fields(false);
        }
    });

    function hide_show_email_fields(show_fields) {
        if (show_fields == true) {
            $('.wznd_gift_card_show_if_email').removeClass('wznd_gift_card_hide_field');
        } else {
            $('.wznd_gift_card_show_if_email').addClass('wznd_gift_card_hide_field');
        }
    }


    //Wallet partial payment
    $('#wooznd_partialpayment').on('change', function() {
        var chk = $(this);
        var use_ppm = 0;
        if (chk.is(':checked')) {
            use_ppm = 1;
        }
        var box = $('.wooznd_partialpayment_box').css('position', 'relative');
        var overlay = $('<div class="blockUI blockOverlay"></div>').prependTo(box);

        jQuery.ajax({
            type: "post",
            url: woocommerce_params.ajax_url,
            data: {action: "wooznd_partialpayment", use_ppm: use_ppm},
            success: function(response) {
                if (response == "success") {
                    jQuery('body').trigger('update_checkout');
                    overlay.remove();
                    box.removeAttr('style');
                }
            },
            error: function(jqXHR, exception) {
                chk[0].checked = (use_ppm == 1);
                overlay.remove();
                box.removeAttr('style');
            }
        });



    });

});
