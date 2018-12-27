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
            control.find('.import-column').find('select.form-control').attr('required', true);
            control.find('.import-edit').hide();
            control.find('.import-edit').find('input.form-control').removeAttr('required');
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
            control.find('.import-edit').find('input.form-control').attr('required', true);
            control.find('.import-column').hide();
            control.find('.import-column').find('select.form-control').removeAttr('required');
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
            control.find('.import-edit').find('input.form-control').removeAttr('required');
            control.find('.import-column').hide();
            control.find('.import-column').find('select.form-control').removeAttr('required');
        }
    );

    $('.import-edit-control').each(
        function (index, control) {
            var modeInput;

            control   = $(control);
            modeInput = control.find('.import-mode-input');

            if ('column' === modeInput.val()) {
                control.find('.import-column').show();
                control.find('.import-column').find('select.form-control').attr('required', true);
                control.find('.import-edit').find('input.form-control').removeAttr('required');
                control.find('.btn-group-import .btn-column').addClass('active');
            } else if ('value' === modeInput.val()) {
                control.find('.import-edit').show();
                control.find('.import-edit').find('input.form-control').attr('required', true);
                control.find('.import-column').find('select.form-control').removeAttr('required');
                control.find('.btn-group-import .btn-edit').addClass('active');
            } else {
                control.find('.btn-group-import .btn-blank').addClass('active');
                control.find('.import-edit').find('input.form-control').removeAttr('required');
                control.find('.import-column').find('select.form-control').removeAttr('required');
            }
        }
    );
}());
