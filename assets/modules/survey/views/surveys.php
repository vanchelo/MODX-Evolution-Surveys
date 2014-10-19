<?php defined('MODX_BASE_PATH') or die('Error'); ?>
<?php /** @var Surveys $app */ ?>
<?php /** @var array $surveys */ ?>
<div class="surveys">
    <?php foreach ($surveys as $s): ?>
    <div id="survey_<?= $s['id'] ?>" class="survey">
        <div class="survey__header"><?= $s['title'] ?></div>
        <?php if (!empty($s['description'])): ?>
        <div class="survey__desc"><?= $s['description'] ?></div>
        <?php endif ?>
        <div class="survey__content">
            <?php if (!$s['voted'] && !$s['closed_at']): ?>
            <form action="<?= $action ?>" method="get" onsubmit="return Survey.vote(this)">
                <input type="hidden" name="survey" value="<?= $s['id'] ?>"/>
                <div class="survey__options">
                    <ul class="survey__options_list">
                    <?php foreach ($s['options'] as $o): ?>
                        <li class="survey__options_list_item">
                            <label>
                                <input type="radio" name="option" value="<?= $o['id'] ?>"/><span><?= $o['title'] ?></span>
                            </label>
                        </li>
                    <?php endforeach ?>
                    </ul>
                </div>
                <div class="survey__buttons">
                    <button type="submit" class="survey__button survey__button--vote">Голосовать</button>
                </div>
            </form>
            <?php else: ?>
            <div class="survey__options">
                <?php foreach ($s['options'] as $o): ?>
                <?php $t = $app->calculateRate($s['votes'], $o['votes']) ?>
                <div class="survey__option">
                    <div class="survey__option_title"><?= $o['title'] ?>: <span><?= $t ?>% (<?= $o['votes'] ?>)</span></div>
                    <div class="survey__option_progress"><span style="width:<?= $t ?>%"></span></div>
                </div>
                <?php endforeach ?>
            </div>
            <?php endif ?>
        </div>
        <?php if ($s['closed_at']): ?>
        <div class="survey__status">Голосвание окончено</div>
        <?php endif ?>
    </div>
    <?php endforeach ?>
</div>
