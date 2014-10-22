<?php defined('MODX_BASE_PATH') or die('Error'); ?>
<div class="survey-menu">
    <ul class="actionButtons">
    <?php if ($app->isInstalled()): ?>
        <li><a href="index.php?a=112&id=<?= $id ?>"><img alt="" src="media/style/<?= $modx->config['manager_theme'] ?>/images/icons/table.gif"> <?= $app->t('surveys') ?></a></li>
        <li><a href="index.php?a=112&id=<?= $id ?>&action=create"><img alt="" src="media/style/<?= $modx->config['manager_theme'] ?>/images/icons/add.png"> <?= $app->t('add_survey') ?></a></li>
    <?php else: ?>
        <li><a href="index.php?a=112&id=<?= $id ?>&action=install"><img alt="" src="media/style/<?= $modx->config['manager_theme'] ?>/images/icons/add.png"> <?= $app->t('install') ?></a></li>
    <?php endif ?>
    </ul>
</div>
