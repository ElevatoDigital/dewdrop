import _ from 'underscore';

var Model = Backbone.Model.extend({
    defaults: {
        allowEditing: true,
        animate:      true,
        title:        '',
        caption:      '',
        sortIndex:    null,
        fields:       []
    }
});

var GroupsCollection = Backbone.Collection.extend({
    model: Model,

    comparator: 'sortIndex',

    toJSON: function () {
        return this.map(
            function (model) {
                return _.pick(model.toJSON(this), ['title', 'fields'])
            }
        );
    },

    initializeWithGlobalVariable: function () {
        if ('undefined' === typeof window.initialFieldGroupsConfig) {
            throw 'Could not find initialFieldGroupsConfig variable in global scope';
        }

        _.each(
            window.initialFieldGroupsConfig,
            function (group, index) {
                group.allowEditing = true;
                group.animate      = false;
                group.sortIndex    = index;

                if (0 === index) {
                    group.allowEditing = false;
                }

                this.add(group);
            },
            this
        );
    }
});

export default GroupsCollection;
