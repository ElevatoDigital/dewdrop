define(
    [
        'text!upload-template.html',
        'text!value-template.html',
        'text!progress-template.html',
        'text!error-messages-template.html'
    ],
    function (templateHtml, valueTemplateHtml, progressTemplateHtml, errorMessagesTemplateHtml) {
        'use strict';

        var uploadTemplate        = _.template(templateHtml),
            valueTemplate         = _.template(valueTemplateHtml),
            progressTemplate      = _.template(progressTemplateHtml),
            errorMessagesTemplate = _.template(errorMessagesTemplateHtml);

        return Backbone.View.extend({
            events: {
                'change input': 'handleFileSelected'
            },

            setValueInput: function ($valueInput) {
                this.$valueInput = $valueInput;
                this.$wrapper    = $valueInput.parent();

                return this;
            },

            setFileInputName: function (fileInputName) {
                this.fileInputName = fileInputName;

                return this;
            },

            setActionUrl: function(actionUrl) {
                this.actionUrl = actionUrl;

                return this;
            },

            render: function () {
                this.$el.html(
                    uploadTemplate(
                        {
                            fileInputName: this.fileInputName
                        }
                    )
                );

                this.$el.find('input').click();

                if (this.$valueInput.val()) {
                    this.renderFileValue(this.$valueInput.val());
                }

                return this;
            },

            renderFileValue: function (url) {
                this.clearStatusUi();

                // @todo Refactor value UI into a sub-view so we can use Backbone event delegation for the buttons
                this.$wrapper.append(
                    valueTemplate(
                        {
                            url: url
                        }
                    )
                );

                this.$wrapper.on(
                    'click',
                    '.btn-remove',
                    _.bind(
                        function (e) {
                            e.preventDefault();
                            this.$valueInput.val('');
                            this.clearStatusUi();
                        },
                        this
                    )
                );

                this.$wrapper.on(
                    'click',
                    '.btn-copy',
                    _.bind(
                        function (e) {
                            e.preventDefault();
                            this.$valueInput.val('');
                        },
                        this
                    )
                );
            },

            renderErrorMessages: function (messages) {
                this.clearStatusUi();

                this.$wrapper.append(
                    errorMessagesTemplate(
                        {
                            messages: messages
                        }
                    )
                )
            },

            renderProgressBar: function () {
                this.clearStatusUi();

                this.$wrapper.append(
                    progressTemplate(
                        {

                        }
                    )
                );
            },

            clearStatusUi: function () {
                this.$wrapper.find('.input-file-status-ui').remove();
            },

            handleFileSelected: function (e) {
                var files = e.target.files,
                    data  = new FormData();

                _.each(
                    files,
                    function (file) {
                        data.append(this.$el.find('input').attr('name'), file);
                    },
                    this
                );

                this.renderProgressBar();

                $.ajax({
                    url: this.actionUrl,
                    type: 'POST',
                    data: data,
                    cache: false,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: _.bind(
                        function (response) {
                            if (!response || 'success' !== response.result) {
                                this.renderErrorMessages(response.messages);
                            } else {
                                this.$valueInput.val(response.url);

                                this.renderFileValue(response.url);
                            }
                        },
                        this
                    ),
                    error: _.bind(
                        function () {
                            this.renderErrorMessages(
                                ['There was an error uploading the selected file.  Please try again.']
                            );
                        },
                        this
                    )
                });
            }
        });
    }
);
