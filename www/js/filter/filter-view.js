define(
    ['type/factory', 'text!filter-template.html'],
    function (typeFactory, templateHtml) {
        'use strict';

        var $          = $ || jQuery,
            customHtml = $('#filter-template').text(),
            template   = _.template(customHtml ? customHtml : templateHtml);

        return Backbone.View.extend({
            events: {
                'change .filter-field': 'selectField',
                'click .js-add':       'addFilter',
                'click .js-remove':    'removeFilter'
            },

            className: 'filter-row',

            fields: null,

            selected: null,

            initialize: function (attributes, options) {
                this.fields   = attributes.fields;
                this.selected = this.model.get('field');
                this.index    = attributes.index;
            },

            render: function () {
                this.$el.html(
                    template({
                        fields:   this.fields.toJSON(),
                        selected: this.selected,
                        index:    this.index
                    })
                );

                if (this.selected) {
                    var selectedField = this.fields.findWhere({id: this.selected}),
                        values        = this.model.get('values');

                    _.each(
                        selectedField.get('defaults'),
                        function (value, key) {
                            if ('undefined' === typeof values[key]) {
                                values[key] = value;
                            }
                        }
                    );

                    this.model.set('values', values);

                    this.$el.find('.filter-control-wrapper').html(
                        typeFactory(
                            selectedField.get('type'),
                            selectedField.get('options'),
                            this.model,
                            this.index
                        ).render().el
                    );
                }

                if (1 === this.collection.length) {
                    this.$el.find('.btn-remove').attr('disabled', 'disabled');
                }

                return this;
            },

            selectField: function (e) {
                this.selected = this.$el.find('.filter-field').val();
                this.model.set('field', this.selected);
                this.render();
            },

            addFilter: function (e) {
                e.preventDefault();
                this.collection.add({});
            },

            removeFilter: function (e) {
                e.preventDefault();
                this.model.destroy();
            }
        });
    }
);
