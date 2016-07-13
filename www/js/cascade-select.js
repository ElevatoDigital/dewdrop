(function () {
    'use strict';

    $(document).on(
        'change',
        ':input',
        function (e) {
            var parent   = $(this),
                children = $('[data-cascade-from="#' + parent.attr('id') + '"]');

            children.each(
                function (index, child) {
                    renderChildOptions($(child), parent.val());
                }
            );
        }
    );

    var renderChildOptions = function (select, parentValue) {
        var groups = select.data('cascade-options'),
            blank  = $('<option value=""></option>');

        select.empty();

        if (select.data('show-blank')) {
            blank.text(select.data('blank-title'));
            select.append(blank);
        }

        $.each(
            groups,
            function (index, group) {
                if (group.groupId === parseInt(parentValue, 10)) {
                    $.each(
                        group.options,
                        function (optionIndex, option) {
                            var node = $('<option></option>');
                            node
                                .attr('value', option.value)
                                .text(option.title);
                            select.append(node);
                        }
                    );
                }
            }
        );
    };

    // Render based upon initial parent widget states
    $('[data-cascade-from]').each(
        function (index, select) {
            var parentNode;

            select     = $(select);
            parentNode = $(select.data('cascade-from'));

            renderChildOptions(select, parentNode.val());

            select.val(select.data('value'));
        }
    );
}());
