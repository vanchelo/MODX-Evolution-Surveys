<?php defined('MODX_BASE_PATH') or die('Error'); ?>
<?php /** @var Surveys $app */ ?>
<?php /** @var array $surveys */ ?>
<div class="surveys_container">
    <?php foreach ($surveys as $k => $s): ?>
    <div id="survey_<?= $s['id'] ?>" class="survey">
        <h4><?= $s['title'] ?></h4>
        <?php if (!empty($s['description'])): ?>
        <div class="survey_desc"><?= $s['description'] ?></div>
        <?php endif ?>
        <div class="survey_content">
            <?php if (!$s['voted']): ?>
            <form action="<?= $action ?>" method="get" onsubmit="return Survey.vote(this)">
                <input type="hidden" name="survey" value="<?= $s['id'] ?>"/>
                <div class="survey_options">
                    <ul>
                    <?php foreach ($s['options'] as $o): ?>
                    <li>
                        <label>
                            <input type="radio" name="option" value="<?= $o['id'] ?>"/><span><?= $o['title'] ?></span>
                        </label>
                    </li>
                    <?php endforeach ?>
                    </ul>
                </div>
                <div class="survey_buttons">
                    <button type="submit" class="button survey_vote">Голосовать</button>
                </div>
            </form>
            <?php else: ?>
            <div class="survey_options">
                <?php foreach ($s['options'] as $o): ?>
                <?php $t = $app->calculateRate($s['votes'], $o['votes']) ?>
                <div class="option">
                    <div class="opt_title"><?= $o['title'] ?>: <span><?= $t ?>% (<?= $o['votes'] ?>)</span>
                    </div>
                    <div class="opt_progress">
                        <span style="width:<?= $t ?>%"></span>
                    </div>
                </div>
                <?php endforeach ?>
            </div>
            <?php if ($app->isAdmin()): ?>
            <div class="survey_buttons">
                <button type="button" class="button survey_vote" onclick="Survey.info(<?= $s['id'] ?>, this)">
                    Информация
                </button>
            </div>
            <?php endif ?>
            <?php endif ?>
        </div>
    </div>
    <?php endforeach ?>
</div>
