import _ from 'underscore';
import moment from 'moment';
import Detector from './module-detector';

class Dewdrop {
    constructor() {
        window.DEWDROP = this;

        // Dynamically set the webpack public path since it varies
        let key = 'dewdrop/www/dist/js/',
            url = _.chain(document.getElementsByTagName('script'))
                .pluck('src')
                .filter((a) => {
                    return (a.indexOf(key) >= 0);
                })
                .first()
                .value();


        this.publicPath = url.substring(0, url.indexOf(key) + key.length);

        __webpack_public_path__ = this.publicPath;
    }

    init() {
        if (undefined === $ && jQuery) {
            window.$ = jQuery;
        }
        if (undefined === $) {
            console.error('jQuery must be installed to use dewdrop.');
        }

        moment.locale(navigator.language);

        /*** Handle jQuery plugin naming conflict between jQuery UI and Bootstrap ***/
        if ($.widget && $.widget.bridge) {
            if ($.ui) {
                if ($.ui.button) {
                    $.widget.bridge('uibutton', $.ui.button);
                }
                if ($.ui.tooltip) {
                    $.widget.bridge('uitooltip', $.ui.tooltip);
                }
            }
        }

        // Disable submit inputs upon applicable form submissions
        $('.dewdrop-submit.disable-on-submit').closest('form').on(
            'submit',
            function () {
                $(this).find('.dewdrop-submit.disable-on-submit').prop('disabled', true);
            }
        );

        this.detectAndLoadModules();

        return this;
    }

    detectAndLoadModules() {
        (new Detector()).loadModules();
    }

    loadModule(module) {
        (new Detector()).load(module);
    }

    bowerUrl(url) {
        var dewdropSrc = this.publicPath,
            bowerUrl   = dewdropSrc.substring(0, dewdropSrc.indexOf('bower_components')) + 'bower_components';

        return bowerUrl + (0 === url.indexOf('/') ? '' : '/') + url;
    }
}

export default Dewdrop;
