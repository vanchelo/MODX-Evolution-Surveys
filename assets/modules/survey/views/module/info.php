<?php defined('MODX_BASE_PATH') or die('Error'); ?>
<?php /** @var Survey $survey */ ?>
<?php /** @var Surveys $app */ ?>
<div class="survey_info">
    <h4><?= $survey->title ?></h4>
    <div class="survey_info_content">
        <?php foreach ($survey->options as $o): ?>
        <?php $t = $app->calculateRate($survey->votes, $o['votes']) ?>
        <div class="option">
            <div class="opt_title"><?= $o['title'] ?>: <span><?= $t ?>%</span> (<?= $o['votes'] ?>)</div>
            <div class="opt_progress">
                <span style="width:<?= $t ?>%"></span>
            </div>
            <?php if ($o['votes']): ?>
            <div class="opt_users">
                <?php if (isset($users[$o['id']])): ?>
                <ul>
                    <?php foreach ($users[$o['id']] as $u): ?>
                    <li>
                        <a href="http://evo.local/manager/index.php?a=88&id=<?= $u['id'] ?>">
                            <?= !empty($u['name']) ? $u['name'] : $u['username'] ?> (<?= $u['email'] ?>)
                        </a>
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

