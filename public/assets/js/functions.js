/**
 * Created by IBAH on 10.05.2016.
 */

function parse(val) {
    var result = '',
        tmp = [];
    location.search
        .substr(1)
        .split("&")
        .forEach(function (item) {
            tmp = item.split("=");
            if (tmp[0] === val) result = decodeURIComponent(tmp[1]);
        });
    return result;
}

function lastSegment() {
    var url = location.href;
    var lastSegment = url.split('/').pop();
    return lastSegment;
}

function setActiveMenuItem() {
    action = lastSegment();
    if (action == undefined || action == null || action == '') {
        action = '/';
    }


    var ul_menu = $('ul.menu');
    var checked = false;
    var lies = ul_menu.find('li');
    lies.removeClass('current-menu-item');

    lies.each(function () {
        var li = $(this);
        var href = li.find('a').attr('href');

        //if (href.indexOf('action=' + action) > -1) {
        if (href.endsWith(action)) {
            li.addClass('current-menu-item');
            checked = true;
        }
    });

    if (!checked) {
        lies.first().addClass('current-menu-item');
    }
}


function buildTableTemterature() {
    var table = $('<table border="1"  class="source-table"/>');

    for (var i = 0; i < 7; i++) {
        var tr = $('<tr/>');
        var data = series[i]['data'];
        for (var j = 0; j < data.length; j++) {
            var td = $('<td/>').text(data[j]);
            td.appendTo(tr);
        }
        tr.appendTo(table);
    }
    $('#table-result-input').html(table);
    return table;
}

function buildTableDistance() {
    var table = $('<table border="1"  class="source-table dist-table"/>');


    for (var i = 0; i < 7; i++) {
        var tr = $('<tr class="dist-tr"/>');
        for (var j = 0; j < 7; j++) {
            var dist = findDistance(i, j);

            var td = $('<td/>').text(dist);
            if (i == j) {
                td.addClass('td-center-main');
            }
            td.appendTo(tr);
        }
        tr.appendTo(table);
    }

    var width_td = 350 / 7;


    $('#table-result-distance').html(table);

    //var real_height_td_pixel = $('.dist-table td').first().width();

    var style = '.dist-table td {width: ' + width_td + 'px;} .dist-tr {height: ' + width_td + 'px;}';
    var s_ob = $('<style/>').html(style);
    s_ob.appendTo($('head'));

    //alert(real_heigth_td_pixel);

    return table;
}

function findDistance(i, j) {
    var n = 7;
    var data = series_max[i]['data'];
    var sum = +0;
    for (var k = 0; k < n; k++) {
        var diff = series_max[i]['data'][k] - series_max[j]['data'][k];
        var sqr = Math.pow(diff, 2);
        sum += sqr;
    }
    console.log(sum);
    //return Math.sqrt(sum).toFixed(3);
    return (sum / n).toFixed(3);
}

function buildTableKoef() {
    var table = $('<table border="1"  class="source-table-koef"/>');

    $.getJSON('/api.php?method=getDataAnalyze', function (gd) {
        var head = 1;
        var head_tr = 1;
        for (var d in gd) {
            if (head == 1) {
                var tr_head = $('<tr class="head-tr-koef"/>');
                tr_head.appendTo(table);
                head = 0;
            }

            var tr = $('<tr/>');
            var data = gd[d];

            $('<td/>').text(d).appendTo(tr);

            if (head_tr == 1) {
                $('<td class="ff-empty"/>').text('').appendTo(tr_head);
            }

            var sum_min = 0;
            var sum_max = 0;
            for (var j = 0; j < data.length; j++) {
                var last = data.length - 1;
                var v = data[j];
                var html = 'Min: <b>' + v.min + '</b><br/>Max: <b>' + v.max + '</b>';
                var td = $('<td/>').html(html);
                td.appendTo(tr);

                if (j != last) {
                    sum_max += v.max;
                    sum_min += v.min;
                } else {
                    var k_min = sum_min / last;
                    var k_max = sum_max / last;

                    var percent_min = (Math.abs(data[last].min - k_min) /
                    data[last].min ).toFixed(2);

                    var percent_max = (Math.abs(data[last].max - k_max) /
                    data[last].max  ).toFixed(2);

                    var absolute_min = 1 - percent_min;
                    var absolute_max = 1 - percent_max;

                    var absolute_general = ((absolute_max + absolute_min) / 2 * 100).toFixed(2);
                    var clas = 'koef-value ';
                    if (absolute_general < 90) {
                        clas += 'less-90';
                    } else if (absolute_general < 95) {
                        clas += 'less-95';
                    }


                    var html = '<div w="' + absolute_general + '" class="' + clas + '">' +
                        absolute_general + '%</div>';

                    var td = $('<td/>').html(html);
                    td.appendTo(tr);
                }

                if (head_tr == 1) {
                    $('<td/>').text(v.datew).appendTo(tr_head);
                }
            }

            if (head_tr == 1) {
                $('<td/>').text('').appendTo(tr_head);
            }

            head_tr = 0;
            tr.appendTo(table);
        }
    });

    $('#table-result-koef').html(table);

    return table;
}

function getGroup() {
    var matrix = Create2DArray(7, 7);
    for (var i = 0; i < 7; i++) {
        for (var j = 0; j < 7; j++) {
            var dist = parseFloat(findDistance(i, j));
            matrix[i][j] = (dist);
        }
    }

    var n = 7;

    var next = Create2DArray(7, 7);
    for (var i = 0; i < n; ++i)
        for (var u = 0; u < n; ++u)
            for (var v = 0; v < n; ++v) {
                if (matrix[u][i] + matrix[i][v] < matrix[u][v]) {
                    matrix[u][v] = matrix[u][i] + matrix[i][v]
                    next[u][v] = i
                }
            }

    var groupOne = [];
    var groupTwo = [];

    groupOne.push(0);
    while (groupOne.length < 4) {
        var last_path = groupOne.last();
        var arr = matrix[last_path];
        var m = minimum(arr, groupOne);
        var ind = matrix[last_path].indexOf(m);
        groupOne.push(ind);
    }

    // i = count sites (may changes)
    for (var i = 0; i < 10; i++) {
        if (groupOne.indexOf(i) == -1) {
            groupTwo.push(i + 1);
        }
    }

    groupOne.forEach(function (it, i) {
        groupOne[i] = it + 1;
    });


    var table = $('<table border="1"  class="source-table group-class"/>');
    var tr = $('<tr/>');
    var tr1 = $('<tr/>');
    $('<td>Група 1</td><td>Група 2</td>').appendTo(tr);
    tr.appendTo(table);

    $.getJSON('/api.php?method=getSites', function (json) {

        var gr_1 = [], gr_2 = [];
        groupOne.forEach(function (itt) {
            json.forEach(function (it) {
                if (itt == it.id) {
                    gr_1.push(it.name);
                }
            });
        });

        groupTwo.forEach(function (itt) {
            json.forEach(function (it) {
                if (itt == it.id) {
                    gr_2.push(it.name);
                }
            });
        });

        var td_1 = $('<td/>');
        var td_2 = $('<td/>');

        td_1.html(gr_1.join(', '));
        td_2.html(gr_2.join(', '));

        td_1.appendTo(tr1);
        td_2.appendTo(tr1);
        tr1.appendTo(table);

        $('#table-result-group').html(table);

        console.log(gr_1);
        console.log(gr_2);

        group_1 = [];
        group_2 = [];

        console.dir(series_groups);

        series_groups.forEach(function (it) {
            gr_1.forEach(function (itt) {
                if (it.name == itt) {
                    group_1.push(it);
                }
            });

            gr_2.forEach(function (itt) {
                if (it.name == itt) {
                    group_2.push(it);
                }
            });
        });

        console.log(group_1);
        console.log(group_2);

        initChartGroups();

        $("#groups-chart svg").each(function () {
            $(this).find("text").last().remove();
            $(this).find("desc").remove();
        });

    });


    //console.log(groupOne);
}

function buildTableAnalyze(days, site) {
    var table = $('<table border="1"  class="source-table source-table-analyze"/>');
    var div_progress_container = $('#progress-result-analyze');
    div_progress_container.html('');

    $.getJSON('/api.php?method=getDataAnalyze&days=' + days, function (gd) {
        //console.log(gd);
        for (var name in gd) {
            var data = gd[name];
            var k = 0;
            var real = 0;

            var tr = $('<tr/>');
            var tr_1 = $('<tr class="tr-analyze-1"/>');

            var tds = $('<td rowspan="2" class="td-name-site"/>');
            tds.html(name);
            tds.appendTo(tr);
            //tds.appendTo(tr_1);

            for (var i = 0; i < data.length; i++) {
                var it = data[i];
                if (i != data.length - 1) {
                    var td = $('<td/>');
                    td.html(it.max);
                    td.appendTo(tr);
                    k++;
                } else {
                    real = it.max;
                }
            }

            var sum = 0;
            for (var i = 0; i < k; i++) {
                var diff = data[i]['max'] - real;
                diff = Math.pow(diff, 2);
                sum += diff;
                var td = $('<td/>');
                td.html(real);
                td.appendTo(tr_1);
            }

            sum = Math.sqrt(sum) / k;

            //

            var maxx = 3;

            var div_progress = $('<div class="progress-result-analyze"/>');

            var width = sum / maxx * 100;
            div_progress.css('width', 0);
            if (width > 70) {
                div_progress.addClass('no-color');
            }
            if (width > 50) {
                div_progress.addClass('middle-color');
            } else {
                div_progress.addClass('ok-color');
            }


            var p_text = $('<p/>');
            p_text.html(
                name + ' Похибка: <strong>' + sum.toFixed(2) + '</strong>'
            );

            p_text.appendTo(div_progress_container);
            div_progress.appendTo(div_progress_container);

            div_progress.attr('width', width + '%');

            tr.appendTo(table);
            tr_1.appendTo(table);
        }
        $('#table-result-analyze').html(table);

        setTimeout(function () {
            div_progress_container.find('.progress-result-analyze').each(function () {
                //alert();
                var t = $(this);
                var width = t.attr('width');
                t.css('width', width);
            });
        }, 1000);


    });
}


function minimum(arr, exc) {
    if (exc == undefined) {
        exc = [];
    }
    var par = [];
    for (var i = 0; i < arr.length; i++) {
        if (!isNaN(arr[i]) && arr[i] !== 0 && exc.indexOf(i) == -1
        ) {
            par.push(arr[i]);
        }
    }
    return Math.min.apply(Math, par);
}

function Create2DArray(rows, columns) {
    var x = new Array(rows);
    for (var i = 0; i < rows; i++) {
        x[i] = new Array(columns);
    }
    return x;
}

if (!Array.prototype.last) {
    Array.prototype.last = function () {
        return this[this.length - 1];
    };
}

function prepareProgressBar() {
    var div = $('.koef-value');
    div.each(function () {
        var t = $(this);
        t.css('width', t.html());
    });
}


function isElementInViewport(el) {
    if (typeof jQuery === "function" && el instanceof jQuery) {
        el = el[0];
    }
    var rect = el.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && /*or $(window).height() */
        rect.right <= (window.innerWidth || document.documentElement.clientWidth) /*or $(window).width() */
    );
}

function onVisibilityChange(el, callback) {
    var old_visible;
    return function () {
        var visible = isElementInViewport(el);
        if (visible != old_visible) {
            old_visible = visible;
            if (typeof callback == 'function') {
                callback();
            }
        }
    }
}