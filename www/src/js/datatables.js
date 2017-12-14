import deparam from 'jquery-deparam';
import 'datatables.net';
import 'datatables.net-bs';
import _ from 'underscore';

class Datatables {

    constructor(selector = '.bootstrap-table') {
        if (!selector.length) {
            selector = '.bootstrap-table'
        }

        this.$table = $('.bootstrap-table');

        var defaultOptions = JSON.parse($('#datatables-options').text()),
            options        = $.extend(true, {}, defaultOptions);

        options.ajax = {
            url: this.getUrl(),
            data: function (data, settings) {
                data.format = 'datatables';

                if (-1 == settings._iDisplayLength) {
                    data["disable-pagination"] = 1;
                }
            }
        };

        options.drawCallback = (settings) => {
            if (window.history.pushState) {
                window.history.pushState(null, null, this.getDewdropUrl(settings));
            }

            this.updateSortingOptionsInFilterForm(settings);
            $('#dataTable-loading-overlay').remove();
        };

        this.$table.on('processing.dt', (e, settings, processing) => {
            let overlay = `<div id="dataTable-loading-overlay" class="dataTable-loading-overlay"></div>`;

            if (processing && !$('#dataTable-loading-overlay').length) {
                this.$table.before(overlay)
            }
        });

        if (window.DEWDROP) {
            window.DEWDROP.datatables = this.$table.DataTable(options);
        }
    }

    /**
     * Maintain Sort fields in the Filter component, this makes it so that
     * when you filter you can keep your current sorting method.
     * @param settings
     */
    updateSortingOptionsInFilterForm(settings) {
        let $filterForm        = $('.filter-form'),
            sorts              = this.getDewdropSortParams(settings),
            inputString        = '',
            sortInputSelectors = [
                'input[type="hidden"][name="sort"]',
                'input[type="hidden"][name="dir"]',
                'input[type="hidden"][name="sort[]"]',
                'input[type="hidden"][name="dir[]"]'
            ]

        // Remove existing sorts
        $filterForm.find(sortInputSelectors.join(', ')).remove();

        sorts.sort.forEach((sort, index) => {
            inputString += `<input type="hidden" name="sort[]" value="${sort}" />
                            <input type="hidden" name="dir[]" value="${sorts.dir[index]}" />`;
        });

        if (inputString.length) {
            $filterForm.prepend(inputString);
        }
    }

    /**
     * Get the url of the current page without any stock dewdrop sorting/pagination (sort, dir, listing-page);
     * @param params Additional query params you want to add to the url.
     * @returns {string}
     */
    getUrl(params = null) {
        var query = deparam(window.location.search.substring(1)),
            url   = window.location.origin + window.location.pathname;

        query     = _.omit(query, 'sort', 'dir', 'listing-page');

        if (params) {
            $.extend(query, params);
        }

        return url + '?' + $.param(query);
    }

    /**
     *
     * @param settings
     * @returns {{sort: Array, dir: Array}}
     */
    getDewdropSortParams(settings) {
        let params = {
            "sort":         [],
            "dir":          []
        };

        _.each(settings.aaSorting, function(sort) {
            var columnIndex = sort[0],
                columnDir   = sort[1];

            params.sort.push(settings.aoColumns[columnIndex].name);
            params.dir.push(columnDir);
        });

        return params;
    }

    /**
     * Get the url with the sorting parameters.
     * @param settings
     */
    getDewdropUrl(settings) {
        var listingPage = (-1 == settings._iDisplayLength) ? 1 : ((settings._iDisplayStart / settings._iDisplayLength) + 1),
            params      = {
                "listing-page": listingPage,
                "page-size":  settings._iDisplayLength
            };

        params = Object.assign({}, this.getDewdropSortParams(settings), params);

        return this.getUrl(params);
    }
}

export default Datatables;
