define(
    ['filter-view'],
    function (FilterView) {
        'use strict';

        return Backbone.View.extend({
            fields: null,

            initialize: function (attributes, options) {
                this.fields = attributes.fields;

                this.collection.on('add remove', this.render, this);
            },

            render: function () {
                this.$el.empty();

                this.collection.each(
                    function (filter, index) {
                        var view = new FilterView({
                            collection: this.collection,
                            fields:     this.fields,
                            model:      filter,
                            index:      index
                        });

                        this.$el.append(view.render().el);
                    },
                    this
                );

                return this;
            }
        });
    }
);
