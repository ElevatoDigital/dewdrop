define(
    ['jquery', 'text!add-group-popover-template.html'],
    function ($, popoverHtml) {
        'use strict';

        return Backbone.View.extend({
            el: '#add-group-wrapper',

            events: {
                'click #add-group':                     'preventSubmission',
                'show.bs.popover #add-group':           'focusInput',
                'keydown .popover-content input:first': 'addGroupOnEnter',
                'click .popover-content .btn':          'addGroup',
            },
            
            groups: null,

            initialize: function (attributes, options) {
                this.$el.find('#add-group').popover(
                    {
                        html:      true,
                        placement: 'top',
                        content:   popoverHtml
                    }
                );
            },

            setGroups: function (groups) {
                this.groups = groups;

                return this;
            },

            preventSubmission: function (e) {
                e.preventDefault();
            },

            focusInput: function (e) {
                setTimeout(
                    _.bind(
                        function () {
                            this.$el.find('.popover-content input').focus();
                        },
                        this
                    ),
                    1
                );
            },

            addGroupOnEnter: function (e) {
                if (13 === e.keyCode) {
                    return this.addGroup(e);
                }
            },

            addGroup: function (e) {
                e.preventDefault();

                $('#add-group').popover('hide');

                this.collection.add({
                    title: this.$el.find('.popover-content input').val()
                });
            }
        });
    }
);
