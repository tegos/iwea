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

        <div id="groups-chart" style="display:flex;gap:16px;">
            <div style="flex:1;min-width:0" id="container-chart-group-1"></div>
            <div style="flex:1;min-width:0" id="container-chart-group-2"></div>
        </div>
    </div>
</div>

</main>

<script>
    var cats          = <?= $categories ?>;
    var series_max    = <?= $series_max ?>;
    var series_groups = series_max;
    var group_1, group_2;

    function initChartGroups() {
        Highcharts.chart('container-chart-group-1', {
            chart: { type: 'spline' },
            title: { text: <?= json_encode($city_name . ', t° max, Група 1') ?> },
            xAxis: { categories: cats },
            yAxis: {
                title: { text: 'Температура, °C' },
                labels: { formatter: function() { return this.value + '°'; } }
            },
            tooltip: { crosshairs: true, shared: true },
            plotOptions: { spline: { marker: { radius: 5, lineColor: '#555', lineWidth: 1 }, dataLabels: { enabled: true } } },
            series: group_1
        });

        Highcharts.chart('container-chart-group-2', {
            chart: { type: 'spline' },
            title: { text: <?= json_encode($city_name . ', t° max, Група 2') ?> },
            xAxis: { categories: cats },
            yAxis: {
                title: { text: 'Температура, °C' },
                labels: { formatter: function() { return this.value + '°'; } }
            },
            tooltip: { crosshairs: true, shared: true },
            plotOptions: { spline: { marker: { radius: 5, lineColor: '#555', lineWidth: 1 }, dataLabels: { enabled: true } } },
            series: group_2
        });
    }
</script>

<?php echo $footer; ?>
