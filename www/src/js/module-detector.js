import $ from 'jquery';

class Loader {
    summernote(override) {
        if (override || $('.summernote').length) {
            System.import('./summernote').then((mod) => { new mod.default(); });
        }
    }
    deleteButton(override) {
        if (override || $('.btn-delete').length) {
            System.import('./delete-button').then((mod) => { new mod.default(); });
        }
    }
    activityLogUserInformation(override) {
        if (override || $('.activity-log-user-information').length) {
            System.import('./activity-log-user-information').then((mod) => { new mod.default(); });
        }
    }
    importEditControl(override) {
        if (override || $('.import-edit-control').length) {
            System.import('./import-edit-control').then((mod) => { new mod.default(); });
        }
    }
    cascadeSelect(override) {
        if (override || $('[data-cascade-from]').length) {
            System.import('./cascade-select').then((mod) => { new mod.default(); });
        }
    }
    datePicker(override) {
        if (override || $('.input-date').length) {
            System.import('./date-picker').then((mod) => { new mod.default(); });
        }
    }
    datetimePicker(override) {
        if (override || $('.input-timestamp').length) {
            System.import('./datetime-picker').then((mod) => { new mod.default(); });
        }
    }
    listingSortable(override) {
        if (override || $('[data-dewdrop~="listing-sortable"]').length) {
            System.import('./listing-sortable').then((mod) => { new mod.default(); });
        }
    }
    optionInputDecorator(override) {
        if (override || $('.option-input-decorator').length) {
            System.import('./option-input-decorator').then((mod) => { new mod.default(); });
        }
    }
    rowCollectionInputTable(override) {
        if (override || $('.row-collection-input-table').length) {
            System.import('./row-collection-input-table').then((mod) => { new mod.default(); });
        }
    }
}

class Detector {
    constructor() {
        this.loader = new Loader();
    }
    loadModules() {
        // Execute each method of Loader
        for (let name of Object.getOwnPropertyNames(Object.getPrototypeOf(this.loader))) {
            let method = this.loader[name];

            if (!(method instanceof Function) || method === Loader) {
                continue;
            }
            method();
        }
    }
    load(module) {
        this.loader[module](true)
    }
}

export default Detector;
