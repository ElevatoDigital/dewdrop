import BooleanView from './boolean-view';
import DateView from './date-view';
import NumericView from './numeric-view';
import TextView from './text-view';
import ReferenceView from './reference-view';
import ManyToManyView from './manytomany-view';

var typeMap = {
    boolean:    BooleanView,
    date:       DateView,
    numeric:    NumericView,
    text:       TextView,
    reference:  ReferenceView,
    manytomany: ManyToManyView
};

var Factory = function (type, options, model, index) {
    if (!options) {
        options = {};
    }

    options.inputIndex = index;
    options.model      = model;

    return new typeMap[type](options);
};

export default Factory;
