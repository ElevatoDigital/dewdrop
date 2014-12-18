require.config({
    paths: {
        text: DEWDROP.bowerUrl('/requirejs-text/text'),
    }
});

require(
    ['jquery'],
    function ($) {
        'use strict';

        var panel      = $('.bulk-action-panel'),
            checkboxes = $('.bulk-action-form .bulk-checkbox'),
            checkAll   = $('.bulk-action-check-all input'),
            pagesAlert = $('.bulk-action-form .bulk-action-check-all-alert');

        var animationOptions = {
            duration: 100,
            complete: function () {
                panel.toggleClass('in');
            }
        };

        var refresh = function () {
            var selected = $('.bulk-action-form .bulk-checkbox:checked');

            if (selected.length && !panel.hasClass('in')) {
                panel.velocity('transition.slideUpIn', animationOptions);
            } else if (!selected.length && panel.hasClass('in')) {
                panel.velocity('transition.slideDownOut', animationOptions);
            }

            if (selected.length === checkboxes.length) {
                checkAll.prop('checked', true);

                if (pagesAlert) {
                    pagesAlert.velocity('fadeIn', {duration: 100});
                }
            } else {
                checkAll.prop('checked', false);

                if (pagesAlert) {
                    pagesAlert.velocity('fadeOut', {duration: 100});
                }
            }
        };

        checkAll.on(
            'change',
            function (e) {
                if ($(this).prop('checked')) {
                    checkboxes.prop('checked', true);
                } else {
                    checkboxes.prop('checked', false);
                }

                refresh();
            }
        );

        $(checkboxes).on('change', refresh);

        refresh();
    }
);
