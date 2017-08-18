exports.ids = [11];
exports.modules = {

/***/ 126:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

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

/***/ })

};;
//# sourceMappingURL=11.js.map