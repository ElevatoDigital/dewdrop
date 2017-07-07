import FieldsCollection from './filter/fields-collection';
import FiltersCollection from './filter/filters-collection';
import FiltersView from './filter/filters-view';

class Filter {
    constructor(selector = '.filter-form') {
        if (!selector.length) {
            selector = '.filter-form'
        }

        $(selector).each(
            function (index, form) {
                var prefix = $(form).data('prefix'),
                    fields = new FieldsCollection(),
                    collection,
                    view;

                fields.loadConfigFromGlobalVariable(prefix);

                collection = new FiltersCollection();
                collection.loadValuesFromGlobalVariable(prefix);

                if (!collection.length) {
                    collection.add({});
                }

                view = new FiltersView({
                    fields: fields,
                    collection: collection
                });

                $(form).find('fieldset').first().append(view.render().el);
            }
        );
    }
}

export default Filter;
