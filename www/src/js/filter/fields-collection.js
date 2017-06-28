import Backbone from 'backbone';
import _ from 'underscore';

var Model = Backbone.Model.extend({
    defaults: {
        id:       '',
        label:    '',
        type:     '',
        options:  {},
        defaults: {}
    }
});

var FieldsCollection = Backbone.Collection.extend({
    model: Model,

    loadConfigFromGlobalVariable: function (prefix) {
        var name = 'FILTER_FIELDS';

        if (prefix) {
            name = prefix + name;
        }

        if ('undefined' === typeof window[name]) {
            throw 'Could not find initial config for filter form';
        }

        _.each(
            window[name],
            function (field) {
                this.add(field);
            },
            this
        );
    }
});

export default FieldsCollection;
