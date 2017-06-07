import BaseView from './base-view';
import templateHtml from './boolean-template.html';
import _ from 'underscore';

var template = _.template(templateHtml);

var BooleanView = BaseView.extend({
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

export default BooleanView;
