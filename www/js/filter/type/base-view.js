define(
    function () {
        'use strict';

        return Backbone.View.extend({
            tagName: 'span',

            className: 'form-inline',

            inputIndex: null,

            options: null,

            events: {
                'change select': 'focusInput',
                'blur input':    'updateValues',
                'change input':  'updateValues'
            },

            initialize: function (attributes, options) {
                this.inputIndex = attributes.inputIndex;

                if ('undefined' !== typeof attributes.options) {
                    this.options = attributes.options;
                }
            },

            render: function () {
                this.$el.html(
                    this.template({
                        inputIndex: this.inputIndex,
                        values:     this.model.get('values'),
                        options:    this.options
                    })
                );

                if (this.model.get('isNew')) {
                    this.focusOnNextTick();
                }

                this.model.set('isNew', false);

                this.postRender();

                return this;
            },

            postRender: function () {

            },

            focusInput: function () {
                var inputs = this.$el.find('input');

                if (inputs.length) {
                    inputs.first().focus();
                }

                this.updateValues();
            },

            focusOnNextTick: function () {
                setTimeout(
                    _.bind(
                        this.focusInput,
                        this
                    ),
                    1
                );
            }
        });
    }
);
