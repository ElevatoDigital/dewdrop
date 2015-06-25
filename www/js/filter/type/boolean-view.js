define(
    ['type/base-view', 'text!type/boolean-template.html'],
    function (BaseView, templateHtml) {
        'use strict';

        var template = _.template(templateHtml);

        return BaseView.extend({
            template: template,

            updateValues: function () {
                this.model.set(
                    'values',
                    {
                        value: this.$el.find('select').val()
                    }
                );
            }
        });
    }
);
