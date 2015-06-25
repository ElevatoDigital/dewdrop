require.config({
    paths: {
        text:        DEWDROP.bowerUrl('/requirejs-text/text'),
        "jquery-ui": DEWDROP.bowerUrl('/jquery-ui/jquery-ui.min')
    }
});

require(
    ['jquery', 'jquery-ui'],
    function ($) {
        jQuery(function () {
            'use strict';

            var $ = jQuery;

            var sortableListings = $('[data-dewdrop~="listing-sortable"]');

            sortableListings.each(
                function () {
                    var submitUrl = $(this).data('sort-url'),
                        tableBody = $(this).find('tbody');

                    var save = function () {
                        var sortedIds = [];

                        tableBody.find('td span[data-id]').each(
                            function (index, span) {
                                sortedIds.push($(span).data('id'));
                            }
                        );

                        $.ajax(
                            submitUrl,
                            {
                                type: 'POST',
                                data: {
                                    sort_order: sortedIds
                                },
                                success: function (response) {
                                    console.log(response);
                                    if (!response.result || 'success' !== response.result) {
                                        alert('Could not save sort order.  Please try again');
                                    }
                                },
                                error: function () {
                                    alert('Unexpected error occurred.  Please try again');
                                }
                            }
                        );
                    };

                    tableBody.sortable({
                        placeholder: 'alert-info',
                        forcePlaceholderSize: true,
                        handle: '.handle',
                        stop: function (e, ui) {
                            save();
                        },
                        helper: function (e, ui) {
                            ui.children().each(
                                function() {
                                    $(this).width($(this).width());
                                }
                            );

                            return ui;
                        }
                    });
                }
            );
        });
    }
);
