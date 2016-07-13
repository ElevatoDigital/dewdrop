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

        // Render initial input state
        $('.btn-input-file').each(
            function (index, button) {
                var input,
                    view = new UploadView();

                button = $(button);
                input  = $(button.data('value-input'));

                if (input.val()) {
                    var url = input.val();
                    if (button.data('file-url')) {
                        url = button.data('file-url');
                    }

                    view
                        .setValueInput(input)
                        .setFileThumbnail(button.data('file-thumbnail'))
                        .setFileUrl(button.data('file-url'))
                        .renderFileValue(url, button.data('file-thumbnail'));
                }
            }
        );

        // Handle attempt to upload a file
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
