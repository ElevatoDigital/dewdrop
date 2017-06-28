import GroupsCollection from './sort-fields/groups-collection';
import GroupsView from './sort-fields/groups-view';
import Popover from './sort-fields/add-group-popover-view';

class SortFields {
    constructor() {
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
}

export default SortFields;
