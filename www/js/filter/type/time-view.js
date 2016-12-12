define(
    ['type/base-view', 'text!type/time-template.html'],
    function (BaseView, templateHtml) {
        'use strict';

        let template = _.template(templateHtml);

        return BaseView.extend({
            template: template,

            singleInputOperators: ['on-or-before', 'before', 'on-or-after', 'after', 'is'],

            doubleInputOperators: ['between'],

            noInputOperators: [],

            events: {
                'change select': 'handleOperatorSelection',
                'blur input':    'updateValues',
                'change input':  'updateValues'
            },

            postRender: function () {
                this.handleOperatorSelection();
            },

            handleOperatorSelection: function () {
                let selected = this.$el.find('select').val();

                this.focusInput();
                this.updateValues();

                if (-1 !== this.singleInputOperators.indexOf(selected)) {
                    this.$el.find('.filter-time-inputs').show();
                    this.$el.find('.filter-time-end-wrapper').hide();
                } else if (-1 !== this.noInputOperators.indexOf(selected)) {
                    this.$el.find('.filter-time-inputs').hide();
                } else {
                    this.$el.find('.filter-time-inputs').show();
                    this.$el.find('.filter-time-end-wrapper').show();
                }
            },

            updateValues: function () {
                this.model.set(
                    'values',
                    {
                        comp:  this.$el.find('select').val(),
                        start: this.$el.find('input.filter-start').val(),
                        end:   this.$el.find('input.filter-end').val()
                    }
                );
            }
        });
    }
);
