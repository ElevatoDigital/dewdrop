exports.ids = [1];
exports.modules = {

/***/ 134:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__sort_fields_groups_collection__ = __webpack_require__(168);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__sort_fields_groups_view__ = __webpack_require__(169);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__sort_fields_add_group_popover_view__ = __webpack_require__(167);
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }





var SortFields = function SortFields() {
    _classCallCheck(this, SortFields);

    var collection = new __WEBPACK_IMPORTED_MODULE_0__sort_fields_groups_collection__["a" /* default */](),
        popover = new __WEBPACK_IMPORTED_MODULE_2__sort_fields_add_group_popover_view__["a" /* default */]({ collection: collection }),
        groups = new __WEBPACK_IMPORTED_MODULE_1__sort_fields_groups_view__["a" /* default */]({ collection: collection });

    collection.initializeWithGlobalVariable();

    $('#sort-form').on('submit', function (e) {
        $('#sorted-fields').val(JSON.stringify(collection.toJSON()));
    });
};

/* harmony default export */ __webpack_exports__["default"] = (SortFields);

/***/ }),

/***/ 152:
/***/ (function(module, exports) {

module.exports = "<div class=\"form-inline\">\n    <input placeholder=\"Enter group title...\" type=\"text\" class=\"form-control\" />\n    <button class=\"btn btn-success btn-add-group\"><span class=\"glyphicon glyphicon-ok\"></span></button>\n</div>\n";

/***/ }),

/***/ 153:
/***/ (function(module, exports) {

module.exports = "<li data-field-id=\"<%- id %>\" class=\"list-group-item\">\n    <span class=\"field-label\"><%- label %></span>\n    <span class=\"text-muted glyphicon glyphicon-align-justify pull-right\"></span>\n</li>\n";

/***/ }),

/***/ 154:
/***/ (function(module, exports) {

module.exports = "<li class=\"list-group-item list-group-item-title\">\n    <span class=\"badge\">\n        <%- fields.length %>\n        <%- (1 === fields.length ? 'Field' : 'Fields') %>\n    </span>\n    <h4>\n        <%- title %>\n    </h4>\n    <% if (caption) { %>\n    <div class=\"help-block\"><%- caption %></div>\n    <% } %>\n    <% if (allowEditing) { %>\n    <div class=\"title-input form-inline\">\n        <input type=\"text\" class=\"title-input form-control\" value=\"<%- title %>\" />\n        <button type=\"button\" class=\"btn btn-save-title btn-success btn-sm\"><span class=\"glyphicon glyphicon-ok\"></span></button>\n        <button type=\"button\" class=\"btn btn-cancel btn-link btn-sm\">Cancel</button>\n    </div>\n    <div class=\"title-buttons\">\n        <a href=\"#\" class=\"btn-remove pull-right btn btn-sm btn-default\">\n            <span class=\"glyphicon glyphicon-remove\"></span>\n            Delete Group\n        </a>\n        <a href=\"#\" class=\"btn-edit pull-right btn btn-sm btn-success\">\n            <span class=\"glyphicon glyphicon-pencil\"></span>\n            Edit Title\n        </a>\n    </div>\n    <% } %>\n</li>\n";

/***/ }),

/***/ 167:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__add_group_popover_template_html__ = __webpack_require__(152);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__add_group_popover_template_html___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__add_group_popover_template_html__);


var AddGroupPopoverView = Backbone.View.extend({
    el: '#add-group-wrapper',

    events: {
        'click #add-group': 'preventSubmission',
        'show.bs.popover #add-group': 'focusInput',
        'keydown .popover-content input:first': 'addGroupOnEnter',
        'click .popover-content .btn': 'addGroup'
    },

    groups: null,

    initialize: function initialize(attributes, options) {
        this.$el.find('#add-group').popover({
            html: true,
            placement: 'top',
            content: __WEBPACK_IMPORTED_MODULE_0__add_group_popover_template_html___default.a
        });
    },

    setGroups: function setGroups(groups) {
        this.groups = groups;

        return this;
    },

    preventSubmission: function preventSubmission(e) {
        e.preventDefault();
    },

    focusInput: function focusInput(e) {
        setTimeout(_.bind(function () {
            this.$el.find('.popover-content input').focus();
        }, this), 1);
    },

    addGroupOnEnter: function addGroupOnEnter(e) {
        if (13 === e.keyCode) {
            return this.addGroup(e);
        }
    },

    addGroup: function addGroup(e) {
        e.preventDefault();

        $('#add-group').popover('hide');

        this.collection.add({
            title: this.$el.find('.popover-content input').val()
        });
    }
});

/* harmony default export */ __webpack_exports__["a"] = (AddGroupPopoverView);

/***/ }),

/***/ 168:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var Model = Backbone.Model.extend({
    defaults: {
        allowEditing: true,
        animate: true,
        title: '',
        caption: '',
        sortIndex: null,
        fields: []
    }
});

var GroupsCollection = Backbone.Collection.extend({
    model: Model,

    comparator: 'sortIndex',

    toJSON: function toJSON() {
        return this.map(function (model) {
            return _.pick(model.toJSON(this), ['title', 'fields']);
        });
    },

    initializeWithGlobalVariable: function initializeWithGlobalVariable() {
        if ('undefined' === typeof window.initialFieldGroupsConfig) {
            throw 'Could not find initialFieldGroupsConfig variable in global scope';
        }

        _.each(window.initialFieldGroupsConfig, function (group, index) {
            group.allowEditing = true;
            group.animate = false;
            group.sortIndex = index;

            if (0 === index) {
                group.allowEditing = false;
            }

            this.add(group);
        }, this);
    }
});

/* harmony default export */ __webpack_exports__["a"] = (GroupsCollection);

/***/ }),

/***/ 169:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__list_view__ = __webpack_require__(170);


var GroupsView = Backbone.View.extend({
    el: '#groups-wrapper',

    events: {
        'sortstop': 'updateSortIndexes'
    },

    initialize: function initialize() {
        this.collection.on('add remove', this.render, this);
    },

    render: function render() {
        var hideTitle = 1 === this.collection.length;

        this.$el.empty();

        this.collection.each(function (group, index) {
            var view = new __WEBPACK_IMPORTED_MODULE_0__list_view__["a" /* default */]({
                model: group,
                hideTitle: hideTitle,
                ungroupedModel: this.collection.first()
            });

            this.$el.append(view.render().el);
        }, this);

        this.$el.sortable({
            items: 'ul:not(.list-group:first)'
        }).disableSelection();

        return this;
    },

    updateSortIndexes: function updateSortIndexes() {
        this.$el.find('.list-group').each(_.bind(function (index, list) {
            this.collection.get($(list).data('model-id')).set('sortIndex', index);
            this.collection.sort();
        }, this));
    }
});

/* harmony default export */ __webpack_exports__["a"] = (GroupsView);

/***/ }),

/***/ 170:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__list_template_html__ = __webpack_require__(154);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__list_template_html___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__list_template_html__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__list_field_template_html__ = __webpack_require__(153);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__list_field_template_html___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1__list_field_template_html__);



var listTemplate = _.template(__WEBPACK_IMPORTED_MODULE_0__list_template_html___default.a),
    fieldTemplate = _.template(__WEBPACK_IMPORTED_MODULE_1__list_field_template_html___default.a);

var ListView = Backbone.View.extend({
    template: listTemplate,

    tagName: 'ul',

    className: 'list-group',

    hideTitle: false,

    events: {
        'sortstop': 'updateModelFields',
        'sortremove': 'updateModelFields',
        'sortreceive': 'updateModelFields',
        'click .btn-remove': 'delete',
        'click .btn-edit': 'showTitleInput',
        'click .btn-save-title': 'saveTitle',
        'click .btn-cancel': 'cancelTitleInput',
        'keydown .title-input': 'saveTitleOnEnter'
    },

    initialize: function initialize(attributes, options) {
        this.model.on('change:title', this.render, this);
        this.model.on('change:fields', this.updateBadge, this);

        this.hideTitle = attributes.hideTitle;
        this.ungroupedModel = attributes.ungroupedModel;
    },

    render: function render() {
        var list;

        this.$el.html(this.template(this.model.toJSON()));
        this.$el.find('.title-input').hide();

        if (this.model.get('animate') && !this.model.get('fields').length) {
            this.$el.velocity('transition.flipYIn');
        }

        // We don't want to show the title at all when there is only 1 group
        if (this.hideTitle) {
            this.$el.find('.list-group-item-title').remove();
        }

        this.$el.data('model-id', this.model.cid);

        this.$el.sortable({
            items: 'li:not(.list-group-item-title)',
            connectWith: '.list-group'
        }).disableSelection();

        _.each(this.model.get('fields'), function (field, index) {
            this.$el.append(fieldTemplate(field));
        }, this);

        return this;
    },

    delete: function _delete(e) {
        e.preventDefault();

        // Pass remaining fields over to the "ungrouped" set
        this.ungroupedModel.set('fields', this.ungroupedModel.get('fields').concat(this.model.get('fields')));

        this.model.destroy();
    },

    showTitleInput: function showTitleInput(e) {
        e.preventDefault();

        this.$el.find('h4').hide();
        this.$el.find('.title-input').show();
        this.$el.find('.title-input').focus();
    },

    saveTitleOnEnter: function saveTitleOnEnter(e) {
        if (13 === e.keyCode) {
            this.saveTitle(e);
        }
    },

    saveTitle: function saveTitle(e) {
        e.preventDefault();

        this.model.set('title', this.$el.find('.title-input input').val());

        this.cancelTitleInput(e);
    },

    cancelTitleInput: function cancelTitleInput(e) {
        e.preventDefault();

        this.$el.find('.title-input').hide();
        this.$el.find('h4').show();
    },

    updateBadge: function updateBadge() {
        var count = this.model.get('fields').length,
            suffix = 1 === count ? '' : 's';

        this.$el.find('.badge').text(count + ' Field' + suffix);
    },

    updateModelFields: function updateModelFields(e, ui) {
        var fields = [];

        this.$el.find('li:not(.list-group-item-title)').each(function (index, element) {
            fields.push({
                id: $(element).data('field-id'),
                label: $(element).find('.field-label').text()
            });
        });

        this.model.set('fields', fields);
    }
});

/* harmony default export */ __webpack_exports__["a"] = (ListView);

/***/ })

};;
//# sourceMappingURL=1.js.map