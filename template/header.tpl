<!DOCTYPE html>
<html lang="Uk">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0,maximum-scale=1">
    <meta name="author" content="Іван Михавко (Ivan Tegos Mykhavko )">
    <meta name="keywords"
          content="<?php echo $keywords; ?>">

    <meta name="description"
          content="<?php echo $description; ?>">
    <title><?php echo $title; ?></title>


    <meta name="yandex-verification" content="b1ee326afc347f89"/>
    <meta name="yandex-verification" content="eb6d57b876ddf2e1"/>

    <link rel="canonical" href="<?php echo $canonical; ?>"/>
    <link rel="icon" href="/assets/images/icons/iwea.png"/>
    <link rel="shortcut icon" href="/assets/images/icons/iwea.png"/>
    <link rel="icon" href="/assets/images/icons/iwea.png" type="image/png"/>
    <link rel="icon" href="/assets/images/icons/favicon.ico" type="image/x-icon"/>

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
            background-image: url('/assets/images/banner.png');
            background-size: cover;
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

                    <h2 class="site-description">Веб-застосування для порівняння прогнозу погоди за різними сайтами
                    </h2>
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
                    <li class="menu-item<?= in_array($a, ['auth_reg','reg']) ? ' current-menu-item' : '' ?>"><a href="/?action=auth_reg">Увійти</a></li>
                    <?php else: ?>
                    <li class="menu-item">
                        <a href="/?action=account"><?= htmlspecialchars($user['name']) ?></a>
                    </li>
                    <?php endif; ?>
                </ul> <!-- .menu -->
            </div> <!-- .main-navigation -->

            <div class="mobile-navigation"></div>

        </div>
    </div>