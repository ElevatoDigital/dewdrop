(function () {
    var decorators = $('.option-input-decorator');

    var setupDecorator = function (decorator) {
        var toggle = decorator.find('.btn-add-option:first'),
            submit = decorator.find('.btn-submit-option:last'),
            well   = decorator.find('.well:first');

        toggle.on(
            'click',
            function (e) {
                e.preventDefault();

                if ('block' === well.css('display')) {
                    well.velocity('slideUp');
                } else {
                    well.velocity('slideDown');
                    well.find('input:first').focus();
                }
            }
        );

        submit.on(
            'click',
            function (e) {
                e.preventDefault();
                submitDecorator(decorator);
            }
        );

        /* Remove all inputs that were part of an option-input-decorator prior to actual submit */
        decorator.closest('form').on(
            'submit',
            function (e) {
                well.remove();
            }
        );
    };

    var submitDecorator = function (decorator) {
        var data = decorator.find(':input').serialize();

        $.ajax(
            decorator.data('save-action'),
            {
                type: 'POST',
                data: data,
                success: function (response) {
                    decorator.find('.form-group')
                        .removeClass('has-feedback')
                        .removeClass('has-error')
                        .removeClass('alert')
                        .removeClass('alert-danger')
                        .find('.error-message')
                            .remove();

                    if (!response.result || 'error' === response.result) {
                        alert('Error while saving new option.  Please try again.');
                    } else if ('success' === response.result) {
                        reRenderControl(decorator, response.id);
                    } else if ('invalid' === response.result) {
                        decorator.velocity('callout.shake');
                        renderValidationMessages(decorator, response.messages);
                    }
                },
                error: function () {
                    alert('Error while saving new option.  Please try again.');
                }
            }
        );
    };

    var renderValidationMessages = function (decorator, messages) {
        var controlName,
            controlMessages,
            formGroup;

        for (controlName in messages) {
            if (messages.hasOwnProperty(controlName)) {
                controlMessages = messages[controlName];
                formGroup       = decorator.find('#' + controlName).closest('.form-group');

                if (fieldHasMessages(controlMessages)) {
                    formGroup
                        .addClass('has-feedback')
                        .addClass('has-error')
                        .addClass('alert')
                        .addClass('alert-danger')
                        .append(renderMessagesForField(messages[controlName]));
                }
            }
        }
    };

    var fieldHasMessages = function (messages) {
        var hasMessages = false,
            validatorKey;

        for (validatorKey in messages) {
            if (messages.hasOwnProperty(validatorKey)) {
                hasMessages = true;
                break;
            }
        }

        return hasMessages;
    };

    var renderMessagesForField = function (messages) {
        var validatorKey,
            wrapper = $('<div></div>'),
            div;

        for (validatorKey in messages) {
            if (messages.hasOwnProperty(validatorKey)) {
                div = $('<div class="help-block error-message"></div>');
                div.text(messages[validatorKey]);
                wrapper.append(div);
            }
        }

        return wrapper;
    };

    var reRenderControl = function (decorator, newOptionValue) {
        $.ajax(
            decorator.data('render-url') + '?value=' + newOptionValue + '&field=' + decorator.data('field-id'),
            {
                type: 'GET',
                success: function (response) {
                    decorator.find('.option-input-original-control:first').html(
                        $(response).find('.option-input-original-control:first').html()
                    );

                    decorator.find('.well:first').velocity('slideUp');
                }
            }
        );
    };

    $(document).on(
        'keydown',
        function (e) {
            if (13 === e.which && $(e.target).is('.option-input-decorator :input')) {
                e.preventDefault();
            }
        }
    );

    decorators.each(
        function (index, decorator) {
            decorator = $(decorator);

            setupDecorator(decorator);
        }
    );
}());
