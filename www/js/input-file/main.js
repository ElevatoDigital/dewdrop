var dewdropInputFile = require.config({
    baseUrl: DEWDROP.bowerUrl('/dewdrop/www/js/input-file'),
    paths: {
        text: DEWDROP.bowerUrl('/requirejs-text/text')
    }
});

dewdropInputFile(
    ['jquery', 'upload-view'],
    function ($, UploadView) {
        'use strict';

        $(document).on(
            'click',
            '.btn-input-file',
            function (e) {
                var view   = new UploadView(),
                    button = $(this);

                e.preventDefault();

                view
                    .setValueInput($(button.data('value-input')))
                    .setFileInputName(button.data('file-input-name'))
                    .setActionUrl(button.data('action-url'));

                document.body.appendChild(view.render().el);
            }
        );
    }
);
