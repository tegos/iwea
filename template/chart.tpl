<main class="main-content">
    <div class="fullwidth-block">
        <div class="container">
            <div id="container-chart-min"></div>
        </div>
    </div>
</main>

<script>
    var cats = <?php echo $categories;    ?>;
    var series = <?=$series?>;
    function initChartMin() {
        Highcharts.chart('container-chart-min', {
                    chart: {
                        type: 'spline'
                    },
                    title: {
                        text: <?= json_encode($city_name . ', погода на 7 днів') ?>,
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