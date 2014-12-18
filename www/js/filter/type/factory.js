define(
    ['type/boolean-view', 'type/date-view', 'type/numeric-view', 'type/text-view', 'type/reference-view'],
    function (BooleanView, DateView, NumericView, TextView, ReferenceView) {
        'use strict';

        var typeMap = {
            boolean:   BooleanView,
            date:      DateView,
            numeric:   NumericView,
            text:      TextView,
            reference: ReferenceView
        };

        return function (type, options, model, index) {
            if ('undefined' === typeof options) {
                options = {};
            }

            options.inputIndex = index;
            options.model      = model;

            return new typeMap[type](options); 
        };
    }
);
