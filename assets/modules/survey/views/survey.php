<?php defined('MODX_BASE_PATH') or die('Error'); ?>
<?php /** @var array $survey */ ?>
<?php /** @var array $options */ ?>
<?php /** @var Surveys $app */ ?>
<div class="survey_options">
    <?php foreach ($options as $o): ?>
    <?php $t = $app->calculateRate($survey['votes'], $o['votes']) ?>
    <div class="option">
        <div class="opt_title"><?= $o['title'] ?>: <span><?= $t ?>% (<?= $o['votes'] ?>)</span>
        </div>
        <?php if ($o['votes']): ?>
            <div class="opt_progress">
                <span style="width:<?= $t ?>%"></span>
            </div>
        <?php endif ?>
    </div>
    <?php endforeach ?>
</div>
