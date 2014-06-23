require.config({
    baseUrl: DEWDROP.bowerUrl('/dewdrop/www/js/sort-fields'),
    paths: {
        text: DEWDROP.bowerUrl('/requirejs-text/text'),
    }
});

require(
    ['jquery', 'groups-collection', 'groups-view', 'add-group-popover-view'],
    function ($, GroupsCollection, GroupsView, Popover) {
        var collection = new GroupsCollection(),
            popover    = new Popover({collection: collection}),
            groups     = new GroupsView({collection: collection});

        collection.initializeWithGlobalVariable();

        $('#sort-form').on(
            'submit',
            function (e) {
                $('#sorted-fields').val(JSON.stringify(collection.toJSON()));
            }
        );
    }
);