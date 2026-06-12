<main class="main-content">
    <div class="fullwidth-block">
        <div class="container">
            <div id="container-chart-min"></div>
        </div>

        <div class="fullwidth-block"><hr/></div>

        <div class="container">
            <div id="container-chart-max"></div>
        </div>
    </div>
</main>

<script>
    var cats = <?php echo $categories;    ?>;
    var series = <?=$series?>;
    function initChartMin() {
        $('#container-chart-min').highcharts({
                    chart: {
                        type: 'spline'
                    },
                    title: {
                        text: '<?=$city_name?>, температура (мін.) на 7 днів'
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
                    series: series ,
                    exporting: {
                        enabled: false
                    }
                }
        );
    }
</script>

<script>

    var series_max = <?=$series_max?>;
    function initChartMax() {
        $('#container-chart-max').highcharts({
                    chart: {
                        type: 'spline'
                    },
                    title: {
                        text: '<?=$city_name?>, температура (макс.) на 7 днів'
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
                    series: series_max,
                    exporting: {
                        enabled: false
                    }
                }
        );
    }
</script>