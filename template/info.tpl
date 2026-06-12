<?php echo $header; ?>

<main class="main-content">

    <div class="container">
        <div class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
            <a itemprop="url" href="/">
                <span itemprop="title">Головна</span>
            </a>
            <span itemprop="title">Список джерел</span>
        </div>
    </div>

    <div class="fullwidth-block">
        <div class="container">
            <h1 class="section-title">Список джерел</h1>

            <div class="row">
                <table border="1" class="source-table">

                    <?php foreach ($sites as $site) { ?>
                    <tr>
                        <td>
                            <img src="<?php echo $site['image_url']; ?>" height="50"
                                 alt="<?php echo $site['name']; ?>">
                        </td>
                        <td style="color: <?php echo $site['color']; ?>;">
                            <b><?php echo $site['name']; ?></b>
                        </td>
                        <td>
                            <a rel="nofollow" href="<?php echo $site['url']; ?>">
                                <?php echo $site['url']; ?>
                            </a>
                        </td>
                        <td>
                            <b><?php echo $site['country']; ?></b>
                        </td>

                    </tr>
                    <?php } ?>
                </table>


            </div>
        </div>
    </div>


</main>

<?php echo $footer; ?>