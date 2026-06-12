<?php echo $header; ?>

<div class="container">

    <div class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
        <a itemprop="url" href="/">
            <span itemprop="title">Головна</span>
        </a>
        <span itemprop="title"><?php echo $page_title; ?></span>
    </div>
</div>

<div class="fullwidth-block">
    <div class="container all-text-header">
        <h1 class="section-title"><?php echo $page_title; ?></h1>
        <?php if(isset($url_prev)): ?>
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
        <a href="<?php echo $url_prev; ?>">
            <span><?php echo $title_prev; ?></span>
        </a>
        <?php endif; ?>

        <i class="fa-solid fa-calendar" aria-hidden="true"></i>

        <?php if(isset($url_next)): ?>

        <a href="<?php echo $url_next; ?>">
            <span><?php echo $title_next; ?></span>
        </a>
        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
        <?php endif; ?>
        <h2 class="text-center">Середня температура</h2>
    </div>
</div>


<div class="forecast-table">
    <div class="container">


        <div class="forecast-container">
            <div class="today forecast">
                <div class="forecast-header">
                    <div class="day">
                        <p class="f-date"><?php echo $day_now; ?></p>
                    </div>
                    <div class="date">
                        <p class="f-date"><?php echo $now_month; ?>, <?php echo $now_month_d; ?></p>
                    </div>
                </div> <!-- .forecast-header -->
                <div class="forecast-content">
                    <div class="location"><?=$city_name?></div>
                    <div class="degree">
                        <table>
                            <tr>
                                <td>
                                    <small class="text-small-info">[макс.]</small>

                                </td>
                                <td>
                                    <div class="num">
                                        <?php echo $forecasts[0]['max']; ?><sup>o</sup>C<br/>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <small class="text-small-info no-margin">[мін.]</small>
                                </td>
                                <td>
                                    <small class="cold  `"><?php echo $forecasts[0]['min']; ?><sup>o</sup>C</small>
                                </td>
                            </tr>
                        </table>


                    </div>

                </div>
            </div>

            <?php for ($i = 1; $i < count($forecasts); $i++) {  $forecast = $forecasts[$i];  ?>
            <div class="forecast">
                <div class="forecast-header">
                    <div class="day">
                        <p class="f-date"><?php echo $forecast['day']; ?>, <?php echo $forecast['day_date']; ?></p>
                    </div>
                </div> <!-- .forecast-header -->
                <div class="forecast-content">

                    <div class="degree"><?php echo $forecast['max']; ?><sup>o</sup>C</div>
                    <small><?php echo $forecast['min']; ?><sup>o</sup>C</small>
                </div>
            </div>
            <?php } ?>

        </div>
    </div>
</div>

<?php echo $chart; ?>

<?php echo $footer; ?>