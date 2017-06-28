import UploadView from './input-file/upload-view';

class InputFile {
    constructor(selector = '.btn-input-file') {
        // Render initial input state
        $(selector).each(
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
            selector,
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
}

export default InputFile;
