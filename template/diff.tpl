<?php echo $header; ?>

<main class="main-content">

<div class="container">
    <div class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
        <a itemprop="url" href="/"><span itemprop="title">Головна</span></a>
        <span itemprop="title">Різниця джерел</span>
    </div>
</div>

<div class="fullwidth-block">
    <div class="container">
        <h1 class="section-title">Різниця джерел</h1>
        <h4>
            Щоб отримати різницю температур, оберіть два джерела зі списку.
        </h4>
    </div>
</div>

<br/>
<div class="forecast-table">
    <div class="container">
        <ul id="source-list-sites">
            <?php $ki = 0; foreach ($sites as $site) { ?>
            <li>
                <input value="<?= $ki ?>" type="checkbox" id="cb<?= $ki ?>"/>
                <label for="cb<?= $ki ?>">
                    <span class="src-dot" style="background:<?= htmlspecialchars($site['color']) ?>"></span>
                    <?= htmlspecialchars($site['name']) ?>
                </label>
            </li>
            <?php $ki++; } ?>
        </ul>
    </div>
</div>

<?php echo $chart_diff; ?>

</main>

<?php echo $footer; ?>
