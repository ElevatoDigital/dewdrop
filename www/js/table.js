// the imports?define=>false stuff is to disable AMD
var $           = jQuery,
    deparam     = require('jquery-deparam'),
    DataTable   = require('imports?define=>false!datatables.net')(window, $),
    DataTableBS = require('imports?define=>false!datatables.net-bs')(window, $);

var $initialTable = $('.bootstrap-table');

/**
 * Get the url of the current page without any stock dewdrop sorting/pagination (sort, dir, listing-page);
 * @param params Additional query params you want to add to the url.
 * @returns {string}
 */
function getUrl(params = null) {
    var query = deparam(window.location.search.substring(1)),
        url   = window.location.origin + window.location.pathname;

    query     = _.omit(query, 'sort', 'dir', 'listing-page');

    if (params) {
        $.extend(query, params);
    }

    return url + '?' + $.param(query);
}

/**
 * Get the url with the sorting parameters.
 * @param settings
 */
function getDewdropUrl(settings) {
    var params = {
        "sort":         [],
        "dir":          [],
        "listing-page": (settings._iDisplayStart / settings._iDisplayLength) + 1
    };

    _.each(settings.aaSorting, function(sort) {
        var columnIndex = sort[0],
            columnDir   = sort[1];

        params.sort.push(settings.aoColumns[columnIndex].name);
        params.dir.push(columnDir);
    });

    return getUrl(params);
}

var options  = $.extend(true, {}, INDEX_TABLE_OPTIONS);

options.ajax = {
    url: getUrl(),
    data: function (data, settings) {
        data.format = 'datatables';
    }
};

options.drawCallback = function(settings) {
    if (window.history.pushState) {
        window.history.pushState(null, null, getDewdropUrl(settings));
    }
};

// Make the table available to developers
window.INDEX_TABLE = $initialTable.DataTable(options);
