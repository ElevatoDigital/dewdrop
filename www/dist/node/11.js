exports.ids = [11];
exports.modules = {

/***/ 126:
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
        global.deleteButton = mod.exports;
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

    var DeleteButton = function DeleteButton() {
        _classCallCheck(this, DeleteButton);

        $('.btn-delete').on('click', function (e) {
            var button = $(this),
                message = button.data('message');

            e.preventDefault();

            if (!message) {
                message = 'Are you sure you want to delete this item?';
            }

            if (confirm(message)) {
                $.ajax(button.data('href'), {
                    type: 'POST',
                    success: function success(response) {
                        window.location.href = button.data('redirect');
                    }
                });
            }
        });
    };

    exports.default = DeleteButton;
    module.exports = exports['default'];
});

/***/ })

};;
//# sourceMappingURL=11.js.map