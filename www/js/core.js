(function ($) {
    'use strict';

    var DEWDROP = {};

    // Find the URL of this script
    var scriptTags = document.getElementsByTagName('script'),
        dewdropSrc = scriptTags[scriptTags.length - 1].src,
        bowerUrl   = dewdropSrc.substring(0, dewdropSrc.indexOf('bower_components')) + 'bower_components';

    DEWDROP.bowerUrl = function (url) {
        return bowerUrl + (0 === url.indexOf('/') ? '' : '/') + url;
    };

    // Supply pre-loaded jQuery as a requirejs module so we don't have to load it twice in WP
    if ('undefined' !== typeof define) {
        define(
            'jquery',
            function () {
                return jQuery;
            }
        );
    }

    moment.locale(navigator.language);

    $.fn.bootstrapTooltip = $.fn.tooltip.noConflict();
    $.fn.tooltip.Constructor = $.fn.bootstrapTooltip.Constructor;

    window.DEWDROP = DEWDROP;
}(jQuery));
