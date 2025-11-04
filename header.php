<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="format-detection" content="telephone=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" itemprop="description" content="">
    <meta name="keywords" itemprop="keywords" content="">

    <!-- WordPress Title -->
    <title><?php wp_title(); ?></title>

    <!-- favicon -->
    <!-- <link rel="icon" type="image/png" href="<?php echo get_template_directory_uri(); ?>/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="<?php echo get_template_directory_uri(); ?>/favicon.svg" />
    <link rel="shortcut icon" href="<?php echo get_template_directory_uri(); ?>/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_template_directory_uri(); ?>/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="" />
    <link rel="manifest" href="<?php echo get_template_directory_uri(); ?>/site.webmanifest" /> -->
    <!-- favicon -->

    <!-- Open Graph  -->
    <!-- <meta property="og:type" content="business.business">
    <meta property="og:title" content=" ">
    <meta property="og:description" content="<?php bloginfo('description'); ?>">
    <meta property="og:url" content="<?php echo esc_url(get_permalink()); ?>">
    <meta property="og:image" content="<?php echo get_template_directory_uri(); ?>/OG.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="627">
    <meta property="og:site_name" content="">
    <meta property="og:locale" content="ru_RU"> -->
    <!-- Open Graph -->

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <div class="wrapper">
        <header class="header">
            <div class="container">
                <?php require_once(TEMPLATE_PATH . '_header-main.php'); ?>
            </div>
        </header>
        <main class="main">