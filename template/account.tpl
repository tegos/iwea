<?php echo $header; ?>

<main class="main-content">
    <div class="fullwidth-block">
        <div class="container">
            <h2 class="section-title">Мій профіль</h2>

            <div class="row">

                <?php if($results)  { ?>
                <ul class="arrow-list">
                    <?php foreach ($results as $res) { ?>
                    <li><a href="/set-city?city_id=<?php echo $res['id']; ?>"><?php echo $res['name']; ?></a>
                    </li>
                    <?php } ?>
                </ul>
                <?php }else { ?>
                <h3>Немає результатів</h3>
                <?php } ?>


            </div>
        </div>
    </div>


</main>

<?php echo $footer; ?>