(function () {
    'use strict';

    $(document).on(
        'click',
        '.import-edit-control .btn-column',
        function (e) {
            var button  = $(this),
                control = button.closest('.import-edit-control');

            e.preventDefault();

            control.find('.btn-group-import .btn').removeClass('active');
            button.addClass('active');

            control.find('.import-mode-input').val('column');
            control.find('.import-column').velocity('slideDown');
            control.find('.import-edit').hide();
        }
    );

    $(document).on(
        'click',
        '.import-edit-control .btn-edit',
        function (e) {
            var button  = $(this),
                control = button.closest('.import-edit-control');

            e.preventDefault();

            control.find('.btn-group-import .btn').removeClass('active');
            button.addClass('active');

            control.find('.import-mode-input').val('value');
            control.find('.import-edit').velocity('slideDown');
            control.find('.import-column').hide();
        }
    );

    $(document).on(
        'click',
        '.import-edit-control .btn-blank',
        function (e) {
            var button  = $(this),
                control = button.closest('.import-edit-control');

            e.preventDefault();

            control.find('.btn-group-import .btn').removeClass('active');
            button.addClass('active');

            control.find('.import-mode-input').val('blank');
            control.find('.import-edit').hide();
            control.find('.import-column').hide();
        }
    );

    $('.import-edit-control').each(
        function (index, control) {
            var modeInput;

            control   = $(control);
            modeInput = control.find('.import-mode-input');

            if ('column' === modeInput.val()) {
                control.find('.import-column').show();
                control.find('.btn-group-import .btn-column').addClass('active');
            } else if ('value' === modeInput.val()) {
                control.find('.import-edit').show();
                control.find('.btn-group-import .btn-edit').addClass('active');
            } else {
                control.find('.btn-group-import .btn-blank').addClass('active');
            }
        }
    );
}());
