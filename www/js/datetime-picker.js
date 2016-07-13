require.config({
    paths: {
        "jquery-ui": DEWDROP.bowerUrl('/jquery-ui/jquery-ui.min'),
        timepicker:  DEWDROP.bowerUrl('/timepicker/jquery.timepicker.min')
    }
});

require(
    ['jquery', 'jquery-ui', 'timepicker'],
    function ($) {
        'use strict';

        // Used to append popovers.  Avoids WP and Bootstrap CSS conflicts.
        var styleWrapper = $('<div class="bootstrap-wrapper"></div>');

        $(document.body).append(styleWrapper);

        var renderInputs = function ($input, dateValue, timeValue) {
            var $row   = $('<div class="row"></div>'),
                $left  = $('<div class="col-xs-6"></div>'),
                $right = $('<div class="col-xs-6"></div>'),
                $date  = $('<input type="text" class="date-input form-control" />'),
                $time  = $('<input type="text" class="time-input form-control" />');

            $row.append($left);
            $row.append($right);

            $left.append($date);
            $right.append($time);

            $date.val(dateValue);
            $time.val(timeValue);

            $date.data('input', $input.attr('name'));
            $time.data('input', $input.attr('name'));

            $input.parent().append($row);

            $date.on(
                'change',
                function () {
                    $input.val($date.val() + ' ' + $time.val())
                }
            );

            $time.on(
                'change',
                function () {
                    $input.val($date.val() + ' ' + $time.val())
                }
            );
        };

        $('.input-timestamp').each(
            function (index, input) {
                var $input = $(input),
                    value  = $input.val().split(' '),
                    date   = value[0],
                    time   = value[1];

                $input.attr('type', 'hidden');

                renderInputs($input, date, time);
            }
        );

        if (Modernizr.touch && Modernizr.inputtypes.date) {
            $('.date-input').attr('type', 'date');
            $('.time-input').attr('type', 'time');
        } else {
            $('.time-input').timepicker({
                change: function () {
                    $(this).trigger('change');
                }
            });

            $('.date-input').each(
                function (index, input) {
                    var $input = $(input),
                        content;

                    content  = '<div class="date-input-popover" data-input="' + $input.data('input') + '">';
                    content += '<a href="#" class="btn btn-link btn-close">';
                    content += '<span class="glyphicon glyphicon-remove text-muted"></span>';
                    content += '</a>';
                    content += '<div class="date-wrapper"></div>';
                    content += '</div>';

                    $input.popover({
                        container: styleWrapper,
                        placement: 'bottom',
                        trigger:   'manual',
                        content:   content,
                        html:      true
                    });

                    $input.on(
                        'focus',
                        function () {
                            var $popover;

                            $input.popover('show');
                            $popover = $('[data-input="' + $input.data('input') + '"] .date-wrapper');

                            $('[data-input="' + $input.data('input') + '"] a').on(
                                'click',
                                function (e) {
                                    e.preventDefault();
                                    $input.popover('hide');
                                }
                            );

                            var options = {
                                changeMonth: true,
                                changeYear:  true,
                                defaultDate: moment($input.val(), 'MM/DD/YYYY').toDate(),
                                onSelect: function (e) {
                                    var selected = $popover.datepicker('getDate');

                                    if (selected) {
                                        $input.val(moment(selected).format('MM/DD/YYYY'));
                                    } else {
                                        $input.val('');
                                    }

                                    $input.trigger('change');
                                    $input.popover('hide');
                                }
                            };

                            $popover.datepicker(options);
                        }
                    );

                    $('input, textarea, select').on(
                        'focus',
                        function () {
                            if (this !== $input[0]) {
                                $input.popover('hide');
                            }
                        }
                    );
                }
            );

        }
    }
);
