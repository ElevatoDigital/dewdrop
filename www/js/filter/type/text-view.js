define(
    ['type/base-view', 'text!type/text-template.html'],
    function (BaseView, templateHtml) {
        'use strict';

        var template = _.template(templateHtml);

        return BaseView.extend({
            template: template,
            
            updateValues: function () {
                this.model.set(
                    'values',
                    {
                        comp:  this.$el.find('select').val(),
                        value: this.$el.find('input.filter-value').val()
                    }
                );
            }
        });
    }
);
