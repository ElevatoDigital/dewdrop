exports.ids = [2];
exports.modules = {

/***/ 129:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _uploadView = __webpack_require__(166);

var _uploadView2 = _interopRequireDefault(_uploadView);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var InputFile = function InputFile() {
    var selector = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '.btn-input-file';

    _classCallCheck(this, InputFile);

    if (!selector.length) {
        selector = '.btn-input-file';
    }

    // Render initial input state
    $(selector).each(function (index, button) {
        var input,
            view = new _uploadView2.default();

        button = $(button);
        input = $(button.data('value-input'));

        if (input.val()) {
            var url = input.val();
            if (button.data('file-url')) {
                url = button.data('file-url');
            }

            view.setValueInput(input).setFileThumbnail(button.data('file-thumbnail')).setFileUrl(button.data('file-url')).renderFileValue(url, button.data('file-thumbnail'));
        }
    });

    // Handle attempt to upload a file
    $(document).on('click', selector, function (e) {
        var view = new _uploadView2.default(),
            button = $(this);

        e.preventDefault();

        view.setValueInput($(button.data('value-input'))).setFileInputName(button.data('file-input-name')).setActionUrl(button.data('action-url'));

        document.body.appendChild(view.render().el);
    });
};

exports.default = InputFile;
module.exports = exports['default'];

/***/ }),

/***/ 148:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


module.exports = "<div class=\"input-file-status-ui\">\n    <div class=\"alert alert-danger\" role=\"alert\">\n        <% _.each(messages, function (message) { %>\n        <div><%- message %></div>\n        <% }); %>\n    </div>\n</div>\n";

/***/ }),

/***/ 149:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


module.exports = "<div class=\"input-file-status-ui\">\n    <div class=\"progress\">\n        <div class=\"progress-bar progress-bar-striped active\" role=\"progressbar\" aria-valuenow=\"45\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: 100%\">\n            <span class=\"sr-only\">Loading...</span>\n        </div>\n    </div>\n    <div class=\"progress-text text-muted\">Loading.  Please wait...</div>\n</div>\n";

/***/ }),

/***/ 150:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


module.exports = "<form class=\"input-file-upload-form\" enctype=\"multipart/form-data\" action=\"\" method=\"POST\">\n    <input class=\"input-file-uploader\" type=\"file\" name=\"<%- fileInputName %>\" />\n</form>\n";

/***/ }),

/***/ 151:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


module.exports = "<div class=\"input-file-status-ui\">\n    <% if (thumbnail) { %>\n    <div class=\"panel panel-default\">\n        <div class=\"panel-body\">\n            <a target=\"_blank\" href=\"<%- url %>\">\n                <div class=\"thumbnail\">\n                    <img src=\"<%- thumbnail %>\" />\n                </div>\n\n                <span class=\"glyphicon glyphicon-file\"></span>\n                <%- url.split('/').pop() %>\n            </a>\n        </div>\n    </div>\n    <% } else { %>\n    <a target=\"_blank\" href=\"<%- url %>\">\n        <span class=\"glyphicon glyphicon-file\"></span>\n        <%- url.split('/').pop() %>\n    </a>\n    <% } %>\n\n\n    <div class=\"btn-group btn-group-justified\" role=\"group\">\n        <div class=\"btn-group\" role=\"group\">\n            <button type=\"button\" class=\"btn btn-default btn-xs btn-remove\"><span class=\"glyphicon glyphicon-remove\"></span> Remove</button>\n        </div>\n        <!--\n        <div class=\"btn-group\" role=\"group\">\n            <button type=\"button\" class=\"btn btn-default btn-xs btn-copy\"><span class=\"glyphicon glyphicon-copy\"></span> Copy</button>\n        </div>\n        -->\n    </div>\n</div>\n";

/***/ }),

/***/ 166:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _uploadTemplate = __webpack_require__(150);

var _uploadTemplate2 = _interopRequireDefault(_uploadTemplate);

var _valueTemplate = __webpack_require__(151);

var _valueTemplate2 = _interopRequireDefault(_valueTemplate);

var _progressTemplate = __webpack_require__(149);

var _progressTemplate2 = _interopRequireDefault(_progressTemplate);

var _errorMessagesTemplate = __webpack_require__(148);

var _errorMessagesTemplate2 = _interopRequireDefault(_errorMessagesTemplate);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var uploadTemplate = _.template(_uploadTemplate2.default),
    valueTemplate = _.template(_valueTemplate2.default),
    progressTemplate = _.template(_progressTemplate2.default),
    errorMessagesTemplate = _.template(_errorMessagesTemplate2.default);

var UploadView = Backbone.View.extend({
    events: {
        'change input': 'handleFileSelected'
    },

    setValueInput: function setValueInput($valueInput) {
        this.$valueInput = $valueInput;
        this.$wrapper = $valueInput.parent();

        return this;
    },

    setFileInputName: function setFileInputName(fileInputName) {
        this.fileInputName = fileInputName;

        return this;
    },

    setFileThumbnail: function setFileThumbnail(fileThumbnail) {
        this.fileThumbnail = fileThumbnail;
        return this;
    },

    setFileUrl: function setFileUrl(fileUrl) {
        this.fileUrl = fileUrl;
        return this;
    },

    setActionUrl: function setActionUrl(actionUrl) {
        this.actionUrl = actionUrl;

        return this;
    },

    render: function render() {
        this.$el.html(uploadTemplate({
            fileInputName: this.fileInputName
        }));

        this.$el.find('input').click();

        if (this.$valueInput.val()) {
            var url = this.$valueInput.val();
            if (this.fileUrl) {
                url = this.fileUrl;
            }

            this.renderFileValue(url, this.fileThumbnail);
        }

        return this;
    },

    renderFileValue: function renderFileValue(url, thumbnail) {
        this.clearStatusUi();

        // @todo Refactor value UI into a sub-view so we can use Backbone event delegation for the buttons
        this.$wrapper.find('.value-wrapper').append(valueTemplate({
            url: url,
            thumbnail: thumbnail
        }));

        this.$wrapper.on('click', '.btn-remove', _.bind(function (e) {
            e.preventDefault();
            this.$valueInput.val('');
            this.clearStatusUi();
        }, this));

        this.$wrapper.on('click', '.btn-copy', _.bind(function (e) {
            e.preventDefault();
            this.$valueInput.val('');
        }, this));
    },

    renderErrorMessages: function renderErrorMessages(messages) {
        this.clearStatusUi();

        this.$wrapper.append(errorMessagesTemplate({
            messages: messages
        }));
    },

    renderProgressBar: function renderProgressBar() {
        this.clearStatusUi();

        this.$wrapper.find('.value-wrapper').append(progressTemplate({}));
    },

    clearStatusUi: function clearStatusUi() {
        this.$wrapper.find('.input-file-status-ui').remove();
    },

    handleFileSelected: function handleFileSelected(e) {
        var files = e.target.files,
            data = new FormData();

        _.each(files, function (file) {
            data.append(this.$el.find('input').attr('name'), file);
        }, this);

        this.renderProgressBar();

        $.ajax({
            url: this.actionUrl,
            type: 'POST',
            data: data,
            cache: false,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: _.bind(function (response) {
                if (!response || 'success' !== response.result) {
                    this.renderErrorMessages(response.messages);
                } else {
                    this.$valueInput.val(response.value).trigger('change');

                    this.renderFileValue(response.url, response.thumbnail);
                }
            }, this),
            error: _.bind(function () {
                this.renderErrorMessages(['There was an error uploading the selected file.  Please try again.']);
            }, this)
        });
    }
});

exports.default = UploadView;
module.exports = exports['default'];

/***/ })

};;
//# sourceMappingURL=2.js.map