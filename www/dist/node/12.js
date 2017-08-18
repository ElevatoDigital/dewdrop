exports.ids = [12];
exports.modules = {

/***/ 124:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_moment__ = __webpack_require__(0);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_moment___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_moment__);
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }



var DatePicker = function DatePicker() {
    _classCallCheck(this, DatePicker);

    // Used to append popovers.  Avoids WP and Bootstrap CSS conflicts.
    var styleWrapper = $('<div class="bootstrap-wrapper"></div>');

    $(document.body).append(styleWrapper);

    if (Modernizr.touch && Modernizr.inputtypes.date) {
        $('.input-date').attr('type', 'date');
    } else {
        $('.input-date').each(function (index, input) {
            var $input = $(input),
                content,
                yearRange,
                inputName = $input.attr('name');

            content = '<div class="date-input-popover" data-input="' + $input.data('input') + '">';
            content += '<a href="#" class="btn btn-link btn-close">';
            content += '<span class="glyphicon glyphicon-remove text-muted"></span>';
            content += '</a>';
            content += '<div class="date-wrapper"></div>';
            content += '</div>';

            $input.popover({
                container: styleWrapper,
                placement: 'bottom',
                trigger: 'manual',
                content: content,
                html: true
            });

            if (inputName && inputName.indexOf('birthdate') > -1) {
                yearRange = '-100:+0';
            } else {
                yearRange = '-100:+100';
            }

            $input.on('focus', function () {
                var $popover;

                $input.popover('show');

                $popover = $('[data-input="' + $input.data('input') + '"] .date-wrapper');

                $('[data-input="' + $input.data('input') + '"] a').on('click', function (e) {
                    e.preventDefault();
                    $input.popover('hide');
                });

                var options = {
                    changeMonth: true,
                    changeYear: true,
                    yearRange: yearRange,
                    defaultDate: __WEBPACK_IMPORTED_MODULE_0_moment___default()($input.val()).toDate(),
                    onSelect: function onSelect(e) {
                        var selected = $popover.datepicker('getDate');

                        if (selected) {
                            $input.val(__WEBPACK_IMPORTED_MODULE_0_moment___default()(selected).format('MM/DD/YYYY'));
                        } else {
                            $input.val('');
                        }

                        $input.trigger('change');
                        $input.popover('hide');
                    }
                };

                $popover.datepicker(options);
            });

            $('input, textarea, select').on('focus', function () {
                if (this !== $input[0]) {
                    $input.popover('hide');
                }
            });
        });
    }
};

/* harmony default export */ __webpack_exports__["default"] = (DatePicker);

/***/ })

};;
//# sourceMappingURL=12.js.map