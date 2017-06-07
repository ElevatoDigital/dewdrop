import $ from 'jquery';
import _ from 'underscore';
import bootstrap from 'bootstrap';
import moment from 'moment';
//import Backbone from 'backbone';
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
        moment.locale(navigator.language);

        $.fn.bootstrapTooltip = $.fn.tooltip.noConflict();

        if ($.fn.tooltip && $.fn.bootstrapTooltip) {
            $.fn.tooltip.Constructor = $.fn.bootstrapTooltip.Constructor;
        } else {
            $.fn.tooltip = $.fn.bootstrapTooltip;
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

    /* Abandoning routing based loading in preference of jQuery detection for now.
    route() {
        let router = Backbone.Router.extend({
            'routes': {
                //'admin/:component/index': 'index', // put ones that dont match default here
                'admin/:component/:page*splat': 'default'
            },
            'index': (component) => {
                console.log('index')
                //System.import('./admin/index.js')//.then((module) => { module.default() })
            },
            'default': (component, page) => {
                try {
                    System.import(`./admin/${page}.js`);
                } catch(e) {
                    console.warn(`No dewdrop module for page: ${page}`, e);
                }
            }
        });

        this.router = new router();

        Backbone.history.start({pushState: true, root: '/'});

        return this;
    }
    */
}

export default Dewdrop;
