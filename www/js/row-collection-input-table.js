jQuery(function ($) {
    'use strict';

    var collections = $('.row-collection-input-table');

    var RowCollection = function (collection) {
        this.button   = collection.find('.btn-add-row');
        this.template = collection.find('.row-template').data('row-collection-template');
        this.count    = parseInt(collection.data('editor-count'), 10);
        this.alert    = collection.find('.alert-no-records');
        this.table    = collection.find('table');
    };

    RowCollection.prototype.init = function () {
        this.refreshVisibility();

        this.button.on(
            'click',
            $.proxy(
                function (e) {
                    e.preventDefault();
                    this.addRow(this.table, this.template, this.count);
                    this.count += 1;
                },
                this
            )
        );

        this.table.find('tr').each(
            $.proxy(
                function (index, tr) {
                    this.initRowEventHandlers($(tr));
                },
                this
            )
        );
    };

    RowCollection.prototype.refreshVisibility = function () {
        if (!this.table.find('tbody tr:visible').length) {
            this.alert.show();
            this.table.hide();
        } else {
            this.alert.hide();
            this.table.show();
        }
    };

    RowCollection.prototype.initRowEventHandlers = function (row) {
        var that = this;

        row.find('.btn-delete').on(
            'click',
            function (e) {
                var button = $(this);

                e.preventDefault();

                row.velocity(
                    'transition.expandOut',
                    {
                        complete: function () {
                            if (!button.data('is-new')) {
                                row.next().find('.row-collection-queued-to-delete').val(1);
                            } else {
                                row.next().remove();
                                row.remove();
                            }

                            that.refreshVisibility();

                            that.table.trigger('rowDeleted');
                        }
                    }
                );
            }
        );
    };

    RowCollection.prototype.addRow = function () {
        var html  = this.template.replace(/__INDEX__/g, this.count),
            tbody = this.table.find('tbody'),
            row;

        tbody.prepend(html);

        this.table.trigger('rowAdded');

        row = tbody.find('tr:first');

        // Forcing visibility here because the row isn't actually shown until Velocity is done
        this.table.show();
        this.alert.hide();

        row.velocity('transition.slideUpIn');

        row.find(':input:first').focus();

        this.initRowEventHandlers(row);
    };

    collections.each(
        function (index, collection) {
            collection = new RowCollection($(collection));
            collection.init();
        }
    );
});
