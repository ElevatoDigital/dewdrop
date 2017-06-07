import $ from 'jquery';
import Velocity from 'velocity-animate';
import VelocityUI from 'velocity-ui-pack';

class RowCollection {
    constructor(collection) {
        this.button   = collection.find('.btn-add-row');
        this.template = collection.find('.row-template').data('row-collection-template');
        this.count    = parseInt(collection.data('editor-count'), 10);
        this.alert    = collection.find('.alert-no-records');
        this.table    = collection.find('table');
    }

    init() {
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
    }

    refreshVisibility() {
        if (!this.table.find('tbody tr:visible').length) {
            this.alert.show();
            this.table.hide();
        } else {
            this.alert.hide();
            this.table.show();
        }
    }

    initRowEventHandlers(row) {
        var that = this;

        row.find('.btn-delete-row').on(
            'click',
            function (e) {
                var button = $(this);

                e.preventDefault();

                Velocity(
                    row,
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
                        }
                    }
                );
            }
        );
    }

    addRow() {
        var html  = this.template.replace(/__INDEX__/g, this.count),
            tbody = this.table.find('tbody'),
            row;

        tbody.prepend(html);

        row = tbody.find('tr:first');

        // Forcing visibility here because the row isn't actually shown until Velocity is done
        this.table.show();
        this.alert.hide();

        Velocity(row, 'transition.slideUpIn');

        row.find(':input:first').focus();

        this.initRowEventHandlers(row);
    }
}

class RowCollectionInputTable {
    constructor() {
        var collections = $('.row-collection-input-table');

        collections.each(
            function (index, collection) {
                collection = new RowCollection($(collection));
                collection.init();
            }
        );
    }
}

export default RowCollectionInputTable;
