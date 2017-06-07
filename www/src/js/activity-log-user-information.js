// @todo test this

import $ from 'jquery';

class ActivityLogUserInformation {
    constructor() {
        $('.activity-log-user-information').popover({
            html:      true,
            container: 'body',
            placement: 'bottom'
        });
    }
}

export default ActivityLogUserInformation;
