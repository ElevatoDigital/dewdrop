import $ from 'jquery';
import key from 'keymaster';

class ListingKeyboardShortcuts {
    constructor(scope = 'dewdrop-listing-keyboard-shortcuts') {
        key.setScope(scope);
        key('j', scope, this.nextRow);
        key('k', scope, this.previousRow);
        key('e', scope, this.edit);
        key('v', scope, this.view);
        key('c', scope, this.add);
        key('/', scope, this.filter);
    }

    nextRow() {
        var selected = $('.bootstrap-table tbody tr.keyboard-selected'),
            next;

        if (!selected.length) {
            $('.bootstrap-table tbody tr:first').addClass('keyboard-selected');
        } else {
            selected.removeClass('keyboard-selected');

            next = selected.next();

            if (next) {
                next.addClass('keyboard-selected');
            }
        }
    }

    previousRow() {
        var selected = $('.bootstrap-table tbody tr.keyboard-selected'),
            previous;

        if (!selected || !selected.length) {
            $('.bootstrap-table tbody tr:last').addClass('keyboard-selected');
        } else {
            selected.removeClass('keyboard-selected');

            previous = selected.prev();

            if (previous) {
                previous.addClass('keyboard-selected');
            }
        }

    }

    edit() {
        var editLink = $('.bootstrap-table tbody tr.keyboard-selected a[data-keyboard-role="edit"]:first');

        if (editLink && editLink.attr('href')) {
            window.location.href = editLink.attr('href');
        }
    }

    view() {
        var viewLink = $('.bootstrap-table tbody tr.keyboard-selected a[data-keyboard-role="view"]:first');

        if (viewLink && viewLink.attr('href')) {
            viewLink.click();
        }
    }

    add() {
        var addLink = $('a[data-keyboard-role="create"]:first');

        if (addLink && addLink.attr('href')) {
            window.location.href = addLink.attr('href');
        }
    }

    filter() {
        var filterButton = $('button[data-keyboard-role="filter"]:first');

        if (filterButton) {
            filterButton.click();

            $('.filter-form .filter-field').focus();
        }
    }
}

export default ListingKeyboardShortcuts;
