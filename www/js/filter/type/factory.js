define(
    [
        'type/boolean-view',
        'type/date-view',
        'type/time-view',
        'type/numeric-view',
        'type/text-view',
        'type/reference-view',
        'type/manytomany-view'
    ],
    function (BooleanView, DateView, TimeView, NumericView, TextView, ReferenceView, ManyToManyView) {
        'use strict';

        let typeMap = {
            boolean:    BooleanView,
            date:       DateView,
            time:       TimeView,
            numeric:    NumericView,
            text:       TextView,
            reference:  ReferenceView,
            manytomany: ManyToManyView
        };

        return function (type, options, model, index) {
            if (!options) {
                options = {};
            }

            options.inputIndex = index;
            options.model      = model;

            return new typeMap[type](options);
        };
    }
);
