<?php defined('MODX_BASE_PATH') or die('Error'); ?>
<div class="survey_info_modal">
    <h4><?= $survey['title'] ?></h4>
    <div class="arcticmodal-close">×</div>
    <div class="survey_info">
        <?php foreach ($options as $o): ?>
        <?php $t = $survey['votes'] > 0 ? floor((100 / $survey['votes']) * $o['votes']) : 0 ?>
        <div class="option">
            <div class="opt_title"><?= $o['title'] ?>: <span><?= $t ?>%</span></div>
            <?php if ($o['votes']): ?>
            <div class="opt_progress">
                <span style="width:<?= $t ?>%"></span>
            </div>
            <div class="opt_users">
                <?php if (isset($users[$o['id']])): ?>
                <ul>
                <?php foreach ($users[$o['id']] as $u): ?>
                    <li><?= $u ?></li>
                <?php endforeach ?>
                </ul>
                <?php endif ?>
            </div>
            <?php endif ?>
        </div>
        <?php endforeach ?>
    </div>
</div>

