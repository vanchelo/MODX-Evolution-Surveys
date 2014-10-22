<?php defined('MODX_BASE_PATH') or die('Error'); ?>
<?php /** @var Surveys $app */ ?>
<?php /** @var Survey $survey */ ?>
<?php /** @var SurveyOption $o */ ?>
<?php /** @var SurveyAnswer $a */ ?>
<?php /** @var SurveyUser $u */ ?>
<div class="survey_info">
    <h4><?= $survey->title ?></h4>
    <div class="survey_info_content">
        <?php foreach ($survey->options as $o): ?>
        <?php $t = $app->calculateRate($survey->votes, $o->votes) ?>
        <div class="option">
            <div class="opt_title"><?= e($o->title) ?>: <span><?= $t ?>%</span> (<?= $o->votes ?>)</div>
            <div class="opt_progress">
                <span style="width:<?= $t ?>%"></span>
            </div>
            <?php if ($o->votes): ?>
            <div class="opt_users">
                <?php if ($o->answers): ?>
                <ul>
                    <?php foreach ($o->answers as $a): ?>
                    <?php if (!$a->user) continue; ?>
                    <li>
                        <a href="http://evo.local/manager/index.php?a=88&id=<?= $a->user->id ?>"><?= $a->user->displayName() ?> (<?= $a->user->email ?>)</a>
                        : <?= $a->created_at ?>
                    </li>
                    <?php endforeach ?>
                </ul>
                <?php endif ?>
            </div>
            <?php endif ?>
        </div>
        <?php endforeach ?>
    </div>
</div>

