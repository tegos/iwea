<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0,maximum-scale=1">
<meta name="keywords"
          content="<?php echo $keywords; ?>">

    <meta name="description"
          content="<?php echo $description; ?>">
    <title><?php echo $title; ?></title>


    <link rel="canonical" href="<?php echo $canonical; ?>"/>
    <link rel="icon" href="/assets/images/icons/favicon.ico" sizes="16x16 32x32 48x48" type="image/x-icon"/>
    <link rel="icon" href="/assets/images/icons/iwea.png" sizes="32x32" type="image/png"/>
    <link rel="apple-touch-icon" href="/assets/images/icons/apple-touch-icon.png"/>

    <!--link rel="stylesheet" href="/assets/css/style.css" media="all"/-->
    <style>
        html {
            background: #fff;
            color: #000 !important;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        html,
        button,
        input,
        select,
        textarea {
            font-family: Roboto;
        }

        body {
            margin: 0;
        }

        .home-background {
            background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
        }
    </style>
</head>




<body>



<div class="site-content">
    <div class="site-header">
        <div class="container">
            <a href="/" class="branding">
                <img src="/assets/images/logo.png" title="Logo iWea" alt="Logo iWea" class="logo"/>

                <div class="logo-type">
                    <?php if(isset($is_home)){ ?>
                    <h1 class="site-title">iWEA</h1>
                    <?php }else{ ?>
                    <h2 class="site-title">iWEA</h2>
                    <?php } ?>

                    <h2 class="site-description">Порівняння прогнозу погоди з різних джерел</h2>
                </div>
            </a>

            <div class="main-navigation">
                <button type="button" class="menu-toggle"><i class="fa fa-bars"></i></button>
                <ul class="menu">
                    <?php $a = $current_action ?? 'home'; ?>
                    <li class="menu-item<?= $a === 'home'      ? ' current-menu-item' : '' ?>"><a href="/">Головна</a></li>
                    <li class="menu-item<?= $a === 'info'      ? ' current-menu-item' : '' ?>"><a href="/info">Список джерел</a></li>
                    <li class="menu-item<?= $a === 'all'       ? ' current-menu-item' : '' ?>"><a href="/all">Погода сьогодні</a></li>
                    <li class="menu-item<?= $a === 'analytics' ? ' current-menu-item' : '' ?>"><a href="/analytics">Аналітика</a></li>
                    <?php if (!$user): ?>
                    <li class="menu-item<?= in_array($a, ['auth_reg','reg']) ? ' current-menu-item' : '' ?>"><a href="/login">Увійти</a></li>
                    <?php else: ?>
                    <li class="menu-item">
                        <a href="/account"><?= htmlspecialchars($user['name']) ?></a>
                    </li>
                    <?php endif; ?>
                </ul> <!-- .menu -->
            </div> <!-- .main-navigation -->

            <div class="mobile-navigation"></div>

        </div>
    </div>