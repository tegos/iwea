<?php echo $header; ?>

<main class="main-content">

<div class="container">
    <div class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
        <a itemprop="url" href="/"><span itemprop="title">Головна</span></a>
        <span itemprop="title">Класифікація</span>
    </div>
</div>

<div class="fullwidth-block">
    <div class="container all-text-header">
        <h1 class="section-title">Класифікація джерел</h1>
        <h2 class="text-center">Класифікація даних сайтів прогнозу погоди</h2>

        <br/>

        <div class="text-center">Матриця відстаней між графіками максимальних температур</div>
        <div id="table-result-distance"></div>

        <br/><br/>
        <div id="table-result-group"></div>

        <br/>

        <div class="container" id="groups-chart">
            <div class="pull-left"  id="container-chart-group-1"></div>
            <div class="pull-right" id="container-chart-group-2"></div>
        </div>
    </div>
</div>

</main>

<script>
    var cats         = <?= $categories ?>;
    var series_groups = <?= $series_max ?>;
    var group_1, group_2;

    function initChartGroups() {
        $('#container-chart-group-1').highcharts({
            chart: { type: 'spline' },
            title: { text: '<?= $city_name ?>, t° max, Група 1' },
            xAxis: { categories: cats },
            yAxis: {
                title: { text: 'Температура, °C' },
                labels: { formatter: function() { return this.value + '°'; } }
            },
            tooltip: { crosshairs: true, shared: true },
            plotOptions: { spline: { marker: { radius: 5, lineColor: '#555', lineWidth: 1 }, dataLabels: { enabled: true } } },
            series: group_1,
            exporting: { enabled: false }
        });

        $('#container-chart-group-2').highcharts({
            chart: { type: 'spline' },
            title: { text: '<?= $city_name ?>, t° max, Група 2' },
            xAxis: { categories: cats },
            yAxis: {
                title: { text: 'Температура, °C' },
                labels: { formatter: function() { return this.value + '°'; } }
            },
            tooltip: { crosshairs: true, shared: true },
            plotOptions: { spline: { marker: { radius: 5, lineColor: '#555', lineWidth: 1 }, dataLabels: { enabled: true } } },
            series: group_2,
            exporting: { enabled: false }
        });
    }
</script>

<?php echo $footer; ?>
