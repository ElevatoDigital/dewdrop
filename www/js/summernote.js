require.config({
    paths: {
        summernote: DEWDROP.bowerUrl('/summernote/dist/summernote')
    }
});

require(
    ['jquery', 'summernote'],
    function ($) {
        jQuery(function () {
            'use strict';

            var $ = jQuery;

            $('.summernote').summernote({
                height: 400
            });
        });
    }
);
