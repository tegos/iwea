<main class="main-content">
    <div class="fullwidth-block">
        <div class="container">
            <div id="container-chart-diff"></div>
            <div id="container-chart-max" style="margin-top:20px;"></div>
        </div>

    </div>
</main>

<script>
    var cats = <?php echo $categories;    ?>;
    var series = <?=$series?>;
    var chartDiff;
    function initChartMin() {
        chartDiff = Highcharts.chart('container-chart-diff', {
                    chart: {
                        type: 'spline'
                    },
                    title: {
                        text: '<?=$city_name?>, різниця температур'
                    },

                    xAxis: {
                        categories: cats
                    },
                    yAxis: {
                        title: {
                            text: 'Температура, °C'
                        },
                        labels: {
                            formatter: function () {
                                return this.value + '°';
                            }
                        }
                    },
                    tooltip: {
                        crosshairs: true,
                        shared: true
                    },
                    plotOptions: {
                        spline: {
                            marker: {
                                radius: 5,
                                lineColor: '#555555',
                                lineWidth: 1
                            },
                            dataLabels: {
                                enabled: true
                            }
                        }
                    },
                    series: series
                });
    }
</script>

<script>
    var series_max = <?=$series_max?>;
    function initChartMax() {
        Highcharts.chart('container-chart-max', {
                    chart: {
                        type: 'line'
                    },
                    title: {
                        text: '<?=$city_name?>, різниця макс. температур'
                    },

                    xAxis: {
                        categories: cats
                    },
                    yAxis: {
                        title: {
                            text: 'Температура, °C'
                        },
                        labels: {
                            formatter: function () {
                                return this.value + '°';
                            }
                        }
                    },
                    tooltip: {
                        crosshairs: true,
                        shared: true
                    },
                    plotOptions: {
                        spline: {
                            marker: {
                                radius: 5,
                                lineColor: '#555555',
                                lineWidth: 1
                            },
                            dataLabels: {
                                enabled: true
                            }
                        }
                    },
                    series: series_max
                });
    }

    function removeSeries () {
        var chart = chartDiff;
        var seriesLength = chart.series.length;
        for (var i = seriesLength - 1; i > -1; i--) {
            chart.series[i].remove();
        }
    }
</script>