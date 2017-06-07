import $ from 'jquery';

class DeleteButton {
    constructor() {
        $('.btn-delete').on(
            'click',
            function (e) {
                var button  = $(this),
                    message = button.data('message');

                e.preventDefault();

                if (!message) {
                    message = 'Are you sure you want to delete this item?';
                }

                if (confirm(message)) {
                    $.ajax(
                        button.data('href'),
                        {
                            type: 'POST',
                            success: function (response) {
                                window.location.href = button.data('redirect');
                            }
                        }
                    );
                }
            }
        );
    }
}

export default DeleteButton;
