<?php echo $header; ?>


<div class="hero home-background">
    <div class="container">
        <form action="/search" method="get" class="find-location">
            <input name="search" type="text" id="location-search" placeholder="Ваше місцезнаходження..." autocomplete="off">
            <input type="submit" value="Шукати">
        </form>


    </div>

    <div class="container">
        <select id="select-source">
            <?php foreach ($sites as $s): ?>
            <option value="<?= (int)$s['id'] ?>"<?= ($s['id'] == $site_id) ? ' selected' : '' ?>><?= htmlspecialchars($s['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <script>var site_id = '<?= (int)$site_id ?>';</script>
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