webpackJsonp([12],{

/***/ 140:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("Object.defineProperty(__webpack_exports__, \"__esModule\", { value: true });\n/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_jquery__ = __webpack_require__(1);\n/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_jquery___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_jquery__);\nfunction _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError(\"Cannot call a class as a function\"); } }\n\n\n\nvar DeleteButton = function DeleteButton() {\n    _classCallCheck(this, DeleteButton);\n\n    __WEBPACK_IMPORTED_MODULE_0_jquery___default()('.btn-delete').on('click', function (e) {\n        var button = __WEBPACK_IMPORTED_MODULE_0_jquery___default()(this),\n            message = button.data('message');\n\n        e.preventDefault();\n\n        if (!message) {\n            message = 'Are you sure you want to delete this item?';\n        }\n\n        if (confirm(message)) {\n            __WEBPACK_IMPORTED_MODULE_0_jquery___default.a.ajax(button.data('href'), {\n                type: 'POST',\n                success: function success(response) {\n                    window.location.href = button.data('redirect');\n                }\n            });\n        }\n    });\n};\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (DeleteButton);\n\n//////////////////\n// WEBPACK FOOTER\n// ./www/src/js/delete-button.js\n// module id = 140\n// module chunks = 12\n\n//# sourceURL=webpack:///./www/src/js/delete-button.js?");

/***/ })

});