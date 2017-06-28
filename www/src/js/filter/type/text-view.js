import BaseView from './base-view';
import templateHtml from './text-template.html';
import _ from 'underscore';

var template = _.template(templateHtml);

var TextView = BaseView.extend({
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

export default TextView;
