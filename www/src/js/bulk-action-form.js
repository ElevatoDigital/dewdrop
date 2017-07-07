import Velocity from 'velocity-animate';
import VelocityUI from 'velocity-ui-pack';

class BulkActionForm {
    constructor() {
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
                Velocity(panel, 'transition.slideUpIn', animationOptions);
            } else if (!selected.length && panel.hasClass('in')) {
                Velocity(panel, 'transition.slideDownOut', animationOptions);
            }

            if (selected.length === checkboxes.length) {
                checkAll.prop('checked', true);

                if (pagesAlert) {
                    Velocity(pagesAlert, 'fadeIn', {duration: 100});
                }
            } else {
                checkAll.prop('checked', false);

                if (pagesAlert) {
                    Velocity(pagesAlert, 'fadeOut', {duration: 100});
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
}

export default BulkActionForm;
