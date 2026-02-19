(function($) {
    "use strict";

    $.Popup = function(popid) {
        var ppbx = new Object();
        ppbx.id = popid;
        ppbx.show = function(options) {
            var settings = {
                width: '350px',
                url: null,
                title: 'untitle',
                content: null,
                css_class: 'popup'
            };
            if (options) {
                $.extend(settings, options);
            }
            ppbx.create(settings.css_class);
            var modal = jQuery(".popup-modal");
            var container = jQuery("#con_" + this.id);
            container.css('width', settings.width);
            modal.fadeIn(300);
            container.fadeIn(300);

            container.find('h1').html(settings.title);

            if (settings.content) {
                if ($.isFunction(settings.content)) {
                    container.find('.popup-padding').html(settings.content(container.find('.popup-padding')));
                } else {
                    container.find('.popup-padding').html(settings.content);
                }
            }

            container.find('.popup-btn-close').on('click', function() {
                ppbx.close();
            });
            re_position(container);
            $(window).on('resize', function() {
                re_position(container);
            });


        };



        ppbx.close = function() {

            var modal = $("#mod_" + this.id);
            var container = $("#con_" + this.id);
            modal.fadeOut(300, function() {
                modal.remove();
            });
            container.fadeOut(300, function() {
                container.remove();
            });

        };

        ppbx.get_content = function() {
            return $("#con_" + this.id);
        };

        ppbx.create = function(css_class) {
            var cssl = 'popup-container';
            if (css_class != '') {
                cssl = 'popup-container ' + css_class;
            }
            $('<div id="mod_' + this.id + '" class="popup-modal"></div>').appendTo(document.body);
            $('<div id="con_' + this.id + '" class="' + cssl + '"></div>').appendTo(document.body);


            var modal = $("#mod_" + this.id).hide().on('click', function() {
                ppbx.close();
            });
            var container = $("#con_" + this.id).hide();

            container.append('<div class="popup-header"><h1></h1><span class="dashicons dashicons-no-alt"></span></div>');
            container.append('<div class="popup-content"><div class="popup-padding"></div></div>');
            var close_btn = container.find('.popup-header .dashicons');
            close_btn.on('click', function() {
                ppbx.close();
            });

        };





        function re_position(box) {
            var tp = ($(window).height() / 2) - (box.outerHeight() / 2);
            var lf = ($(window).width() / 2) - (box.outerWidth() / 2);
            box.css({top: tp, left: lf});
        }

        return ppbx;
    };

})(jQuery);