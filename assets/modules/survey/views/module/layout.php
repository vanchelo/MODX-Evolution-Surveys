<?php defined('MODX_BASE_PATH') or die('Error'); ?>
<!doctype html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?> :: <?= $app->t('management') ?></title>
    <link href="media/style/<?= $modx->config['manager_theme'] ?>/style.css" type="text/css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/assets/modules/survey/module/module.css" />
    <link rel="stylesheet" type="text/css" href="/assets/modules/survey/module/icons/icons.css" />
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
    <script type="text/javascript" src="/assets/modules/survey/module/module.js"></script>
</head>
<body class="survey_module">

<div class="sectionHeader"><?= $title ?></div>
<div class="sectionBody">
    <?php include 'menu.php' ?>
    <div class="sectContent"><?= $content ?></div>
</div>
</body>
</html>
