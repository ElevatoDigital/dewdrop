define(
    ['type/base-view', 'text!type/numeric-template.html'],
    function (BaseView, templateHtml) {
        'use strict';

        var template = _.template(templateHtml);

        return BaseView.extend({
            template: template,

            singleInputOperators: ['is-less-than', 'is', 'is-more-than'],

            doubleInputOperators: ['is-between'],

            events: {
                'change select': 'handleOperatorSelection',
                'blur input':    'updateValues',
                'change input':  'updateValues'
            },

            handleOperatorSelection: function () {
                var selected = this.$el.find('select').val();

                this.focusInput();
                this.updateValues();

                if (-1 !== this.singleInputOperators.indexOf(selected)) {
                    this.$el.find('.filter-numeric-inputs').show();
                    this.$el.find('.filter-numeric-operand2-wrapper').hide();
                } else {
                    this.$el.find('.filter-numeric-inputs').show();
                    this.$el.find('.filter-numeric-operand2-wrapper').show();
                }
            },

            postRender: function () {
                this.handleOperatorSelection();
            },

            updateValues: function () {
                this.model.set(
                    'values',
                    {
                        comp:     this.$el.find('select').val(),
                        operand1: this.$el.find('input.filter-operand1').val(),
                        operand2: this.$el.find('input.filter-operand2').val()
                    }
                );
            }
        });
    }
);
