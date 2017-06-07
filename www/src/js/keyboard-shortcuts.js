import $ from 'jquery';
import key from 'keymaster';

class KeyboardShortcuts {
    constructor(scope = 'dewdrop-keyboard-shortcuts') {
        key.setScope('listing');
        key('j', 'listing', this.nextRow);
        key('k', 'listing', this.previousRow);
        key('e', 'listing', this.edit);
        key('v', 'listing', this.view);
        key('c', 'listing', this.add);
        key('/', 'listing', this.filter);
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

export default KeyboardShortcuts;
