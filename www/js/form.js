jQuery(function ($) {

    'use strict';

    // Disable submit inputs upon applicable form submissions
    $('.dewdrop-submit.disable-on-submit').closest('form').on(
        'submit',
        function () {
            $(this).find('.dewdrop-submit.disable-on-submit').prop('disabled', true);
        }
    );

});