(function ($) {
    'use strict';
    
    var nextRow = function () {
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
    };

    var previousRow = function () {
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

    };

    var edit = function () {
        var editLink = $('.bootstrap-table tbody tr.keyboard-selected a[data-keyboard-role="edit"]:first');

        if (editLink && editLink.attr('href')) {
            window.location.href = editLink.attr('href');
        }
    };
    
    var view = function () {
        var viewLink = $('.bootstrap-table tbody tr.keyboard-selected a[data-keyboard-role="view"]:first');

        if (viewLink && viewLink.attr('href')) {
            viewLink.click();
        }
    };
    
    var add = function () {
        var addLink = $('a[data-keyboard-role="create"]:first');

        if (addLink && addLink.attr('href')) {
            window.location.href = addLink.attr('href');
        }
    };

    var filter = function () {
        var filterButton = $('button[data-keyboard-role="filter"]:first');

        if (filterButton) {
            filterButton.click();

            $('.filter-form .filter-field').focus();
        }
    };
    
    key.setScope('listing');
    key('j', 'listing', nextRow);
    key('k', 'listing', previousRow);
    key('e', 'listing', edit);
    key('v', 'listing', view);
    key('c', 'listing', add);
    key('/', 'listing', filter);
}(jQuery));
