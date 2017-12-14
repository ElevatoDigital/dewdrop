import BaseView from './base-view';
import templateHtml from './text-template.html';
import _ from 'underscore';

var template = _.template(templateHtml);

var TextView = BaseView.extend({
    template: template,

    inputOperators: ['contains', 'not-contains', 'starts-with', 'ends-with'],

    noInputOperators: ['empty', 'not-empty'],

    events: {
        'change select': 'handleOperatorSelection'
    },

    postRender: function () {
        this.handleOperatorSelection();
    },

    updateValues: function () {
        this.model.set(
            'values',
            {
                comp:  this.$el.find('select').val(),
                value: this.$el.find('input.filter-value').val()
            }
        );
    },

    handleOperatorSelection: function () {
        var selected = this.$el.find('select').val();

        this.focusInput();
        this.updateValues();

        if (-1 !== this.inputOperators.indexOf(selected)) {
            this.$el.find('input.filter-value').show();
        } else if (-1 !== this.noInputOperators.indexOf(selected)) {
            this.$el.find('input.filter-value').hide();
        }
    }
});

export default TextView;
