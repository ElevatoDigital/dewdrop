define(
    ['jquery', 'jquery-ui', 'text!list-template.html', 'text!list-field-template.html'],
    function ($, ui, listHtml, fieldHtml) {
        'use strict';

        var listTemplate  = _.template(listHtml),
            fieldTemplate = _.template(fieldHtml);

        return Backbone.View.extend({
            template: listTemplate,

            tagName: 'ul',

            className: 'list-group',

            hideTitle: false,

            events: {
                'sortstop':              'updateModelFields',
                'sortremove':            'updateModelFields',
                'sortreceive':           'updateModelFields',
                'click .btn-remove':     'delete',
                'click .btn-edit':       'showTitleInput',
                'click .btn-save-title': 'saveTitle',
                'click .btn-cancel':     'cancelTitleInput',
                'keydown .title-input':  'saveTitleOnEnter'
            },

            initialize: function (attributes, options) {
                this.model.on('change:title', this.render, this);
                this.model.on('change:fields', this.updateBadge, this);

                this.hideTitle      = attributes.hideTitle;
                this.ungroupedModel = attributes.ungroupedModel;
            },

            render: function () {
                var list;

                this.$el.html(this.template(this.model.toJSON()));
                this.$el.find('.title-input').hide();

                if (this.model.get('animate') && !this.model.get('fields').length) {
                    this.$el.velocity('transition.flipYIn');
                }

                // We don't want to show the title at all when there is only 1 group
                if (this.hideTitle) {
                    this.$el.find('.list-group-item-title').remove();
                }

                this.$el.data('model-id', this.model.cid);

                this.$el.sortable({
                    items:       'li:not(.list-group-item-title)',
                    connectWith: '.list-group'
                }).disableSelection();
                
                _.each(
                    this.model.get('fields'),
                    function (field, index) {
                        this.$el.append(fieldTemplate(field));
                    },
                    this
                );

                return this;
            },

            delete: function (e) {
                e.preventDefault();

                // Pass remaining fields over to the "ungrouped" set
                this.ungroupedModel.set(
                    'fields',
                    this.ungroupedModel.get('fields').concat(this.model.get('fields'))
                );

                this.model.destroy();
            },

            showTitleInput: function (e) {
                e.preventDefault();

                this.$el.find('h4').hide();
                this.$el.find('.title-input').show();
                this.$el.find('.title-input').focus();
            },

            saveTitleOnEnter: function (e) {
                if (13 === e.keyCode) {
                    this.saveTitle(e);
                }
            },

            saveTitle: function (e) {
                e.preventDefault();

                this.model.set('title', this.$el.find('.title-input input').val());

                this.cancelTitleInput(e);
            },

            cancelTitleInput: function (e) {
                e.preventDefault();

                this.$el.find('.title-input').hide();
                this.$el.find('h4').show();
            },

            updateBadge: function () {
                var count  = this.model.get('fields').length,
                    suffix = (1 === count ? '' : 's');

                this.$el.find('.badge').text(count + ' Field' + suffix);
            },

            updateModelFields: function (e, ui) {
                var fields = [];

                this.$el.find('li:not(.list-group-item-title)').each(
                    function (index, element) {
                        fields.push({
                            id:        $(element).data('field-id'),
                            label:     $(element).find('.field-label').text()
                        });
                    }
                );

                this.model.set('fields', fields);
            }
        });
    }
);
