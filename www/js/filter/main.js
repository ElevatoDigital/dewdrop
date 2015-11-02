var dewdropFilter = require.config({
    baseUrl: DEWDROP.bowerUrl('/dewdrop/www/js/filter'),
    paths: {
        text: DEWDROP.bowerUrl('/requirejs-text/text')
    }
});

dewdropFilter(
    ['jquery', 'fields-collection', 'filters-collection', 'filters-view'],
    function ($, FieldsCollection, FiltersCollection, FiltersView) {
        'use strict';

        $('.filter-form').each(
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
                    fields:     fields,
                    collection: collection
                });

                $(form).find('fieldset').first().append(view.render().el);
            }
        );
    }
);
