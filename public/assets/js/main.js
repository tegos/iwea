/**
 * Created by IBAH on 08.05.2016.
 */


$(window).load(function () {
    

    try {
        initChartMin();
        initChartMax();
    }
    catch (err) {
    }


    $("svg").each(function () {
        $(this).find("text").last().remove();
        $(this).find("desc").remove();
    });


    var options = {
        url: '/api.php?method=getSCities',
        list: {
            match: {
                enabled: true
            },
            onClickEvent: function () {
                //$('.find-location').submit();
            }
        },
        theme: "plate-dark"
    };

    $("#location-search").easyAutocomplete(options);

    $.getJSON('/api.php?method=getSitesForSelect', function (dd) {

        $('#select-source').ddslick({
            data: dd,
            width: '100%',
            imagePosition: 'left',
            onSelected: function (data) {
                var selData = data.selectedData;

                if (selData.value !== site_id) {
                    var loc = '/?action=set_site_id&site_id=' + selData.value;
                    $.get(loc, function () {
                        location.reload();
                    });
                }
                //console.log(selData);
            }
        });

    });

    try {
        removeSeries();
    } catch (e) {
    }


    $('#source-list-sites input').change(function () {
        var ul = $('#source-list-sites');
        //var id_site = $(this).val();

        var chart = $('#container-chart-diff').highcharts();

        removeSeries();
        var ids = [];
        ul.find('input:checked').each(function () {
            var inp = $(this);
            ids.push(inp.val());
        });

        if (ids.length == 2) {
            var series_dif = {};
            var series_dif_max = {};
            var data_diff_max = [];
            var data_diff = [];
            var i;
            for (i = 0; i < series[ids[0]]['data'].length; i++) {
                var diff = series[ids[0]]['data'][i] - series[ids[1]]['data'][i];
                var diff_max = series_max[ids[0]]['data'][i] - series_max[ids[1]]['data'][i];
                data_diff.push(diff);
                data_diff_max.push(diff_max);
            }
            series_dif.name = 'Min - ' + series[ids[0]].name + '-' + series[ids[1]].name;
            series_dif.data = data_diff;
            series_dif.type = 'line';

            series_dif_max.name = 'Max - ' + series[ids[0]].name + '-' + series[ids[1]].name;
            series_dif_max.data = data_diff_max;
            series_dif_max.type = 'line';

            chart.addSeries(
                series_dif
            );
            chart.addSeries(
                series_dif_max
            );


        } else {
            if ($('#source-list-sites input:checked').length > 2) {
                $('#source-list-sites input:checked').each(function () {
                    $(this).click();
                });
            }
            if (ids.length != 1) {
                removeSeries();
            }
        }
    });

    setActiveMenuItem();

    if (action == 'analytics') {
        buildTableTemterature();
        buildTableDistance();
        buildTableKoef();

        var handler = onVisibilityChange($('#table-result-koef'), function (e) {
            prepareProgressBar();
        });

        //$(window).on('DOMContentLoaded load resize scroll', handler);

        getGroup();

        $('#analyze').click(function () {
            var days = $('#interval').val();
            var site = $('#site-int').val();
            buildTableAnalyze(days, site);
        });

    }

    // close alert
    $('p.close').click(function () {
        var alert = $(this).parents('.alert').first();
        alert.fadeOut(500);
    });


});


