import $ from 'jquery';

class Loader {
    summernote(override) {
        if (override || $('.summernote').length) {
            System.import('./summernote').then((mod) => { new mod.default(override); });
        }
    }
    deleteButton(override) {
        if (override || $('.btn-delete').length) {
            System.import('./delete-button').then((mod) => { new mod.default(override); });
        }
    }
    activityLogUserInformation(override) {
        if (override || $('.activity-log-user-information').length) {
            System.import('./activity-log-user-information').then((mod) => { new mod.default(override); });
        }
    }
    importEditControl(override) {
        if (override || $('.import-edit-control').length) {
            System.import('./import-edit-control').then((mod) => { new mod.default(override); });
        }
    }
    cascadeSelect(override) {
        if (override || $('[data-cascade-from]').length) {
            System.import('./cascade-select').then((mod) => { new mod.default(override); });
        }
    }
    datePicker(override) {
        if (override || $('.input-date').length) {
            System.import('./date-picker').then((mod) => { new mod.default(override); });
        }
    }
    datetimePicker(override) {
        if (override || $('.input-timestamp').length) {
            System.import('./datetime-picker').then((mod) => { new mod.default(override); });
        }
    }
    listingSortable(override) {
        if (override || $('[data-dewdrop~="listing-sortable"]').length) {
            System.import('./listing-sortable').then((mod) => { new mod.default(override); });
        }
    }
    optionInputDecorator(override) {
        if (override || $('.option-input-decorator').length) {
            System.import('./option-input-decorator').then((mod) => { new mod.default(override); });
        }
    }
    rowCollectionInputTable(override) {
        if (override || $('.row-collection-input-table').length) {
            System.import('./row-collection-input-table').then((mod) => { new mod.default(override); });
        }
    }
    filter(override) {
        if (override || $('.filter-form').length) {
            System.import('./filter').then((mod) => { new mod.default(override); });
        }
    }
    sortFields(override) {
        if (override || $('#sort-form').length) {
            System.import('./sort-fields').then((mod) => { new mod.default(override); });
        }
    }
    inputFile(override) {
        if (override || $('.btn-input-file').length) {
            System.import('./input-file').then((mod) => { new mod.default(override); });
        }
    }
    listingKeyboardShortcuts(override) {
        if (override || $('a[data-target="#keyboard-shortcuts-modal"]').length) {
            System.import('./listing-keyboard-shortcuts').then((mod) => { new mod.default(override); });
        }
    }
    bulkActionForm(override) {
        if (override || $('.bulk-action-form').length) {
            System.import('./bulk-action-form').then((mod) => { new mod.default(override); });
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
    load(module, override = true) {
        this.loader[module](override)
    }
}

export default Detector;
