<?php echo $header; ?>

<main class="main-content">

<div class="container">
    <div class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
        <a itemprop="url" href="/"><span itemprop="title">Головна</span></a>
        <span itemprop="title">Точність прогнозу</span>
    </div>
</div>

<div class="fullwidth-block no-padding light-block-analyze">
    <div class="container all-text-header">
        <h1 class="section-title">Точність прогнозу</h1>
        <h2 class="text-center">Порівняльний аналіз даних прогнозу погоди різних сайтів</h2>

        <p class="text-center" style="color:#666;margin-top:8px;">
            Показує наскільки точним був прогноз кожного джерела N днів тому порівняно з останнім записаним значенням.
        </p>

        <br/>

        <span>Інтервал: </span>
        <select id="interval">
            <option value="3">3 дні тому</option>
            <option value="5">5 днів тому</option>
            <option value="7">7 днів тому</option>
        </select>

        <span class="space"></span>

        <button id="analyze" class="button">Ok</button>

        <br/><br/>
        <div id="table-result-analyze"></div>
        <br/>
        <div id="progress-result-analyze"></div>
    </div>
</div>

</main>

<?php echo $footer; ?>
