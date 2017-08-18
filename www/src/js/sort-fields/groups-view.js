import List from './list-view';
import _ from 'underscore';

var GroupsView = Backbone.View.extend({
    el: '#groups-wrapper',

    events: {
        'sortstop': 'updateSortIndexes'
    },

    initialize: function () {
        this.collection.on('add remove', this.render, this);
    },

    render: function () {
        var hideTitle = (1 === this.collection.length);

        this.$el.empty();

        this.collection.each(
            function (group, index) {
                var view = new List({
                    model:          group,
                    hideTitle:      hideTitle,
                    ungroupedModel: this.collection.first()
                });

                this.$el.append(view.render().el);
            },
            this
        );

        this.$el.sortable({
            items: 'ul:not(.list-group:first)',
        }).disableSelection();

        return this;
    },

    updateSortIndexes: function () {
        this.$el.find('.list-group').each(
            _.bind(
                function (index, list) {
                    this.collection.get($(list).data('model-id')).set('sortIndex', index);
                    this.collection.sort();
                },
                this
            )
        );
    }
});

export default GroupsView;
