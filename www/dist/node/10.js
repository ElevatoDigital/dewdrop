exports.ids = [10];
exports.modules = {

/***/ 131:
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;(function (global, factory) {
    if (true) {
        !(__WEBPACK_AMD_DEFINE_ARRAY__ = [module, exports], __WEBPACK_AMD_DEFINE_FACTORY__ = (factory),
				__WEBPACK_AMD_DEFINE_RESULT__ = (typeof __WEBPACK_AMD_DEFINE_FACTORY__ === 'function' ?
				(__WEBPACK_AMD_DEFINE_FACTORY__.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__)) : __WEBPACK_AMD_DEFINE_FACTORY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
    } else if (typeof exports !== "undefined") {
        factory(module, exports);
    } else {
        var mod = {
            exports: {}
        };
        factory(mod, mod.exports);
        global.listingSortable = mod.exports;
    }
})(this, function (module, exports) {
    'use strict';

    Object.defineProperty(exports, "__esModule", {
        value: true
    });

    function _classCallCheck(instance, Constructor) {
        if (!(instance instanceof Constructor)) {
            throw new TypeError("Cannot call a class as a function");
        }
    }

    var ListingSortable = function ListingSortable() {
        _classCallCheck(this, ListingSortable);

        var sortableListings = $('[data-dewdrop~="listing-sortable"]');

        sortableListings.each(function () {
            var submitUrl = $(this).data('sort-url'),
                tableBody = $(this).find('tbody');

            var save = function save() {
                var sortedIds = [];

                tableBody.find('td span[data-id]').each(function (index, span) {
                    sortedIds.push($(span).data('id'));
                });

                $.ajax(submitUrl, {
                    type: 'POST',
                    data: {
                        sort_order: sortedIds
                    },
                    success: function success(response) {
                        if (!response.result || 'success' !== response.result) {
                            alert('Could not save sort order.  Please try again');
                        }
                    },
                    error: function error() {
                        alert('Unexpected error occurred.  Please try again');
                    }
                });
            };

            tableBody.sortable({
                placeholder: 'alert-info',
                forcePlaceholderSize: true,
                handle: '.handle',
                stop: function stop(e, ui) {
                    save();
                },
                helper: function helper(e, ui) {
                    ui.children().each(function () {
                        $(this).width($(this).width());
                    });

                    return ui;
                }
            });
        });
    };

    exports.default = ListingSortable;
    module.exports = exports['default'];
});

/***/ })

};;
//# sourceMappingURL=10.js.map