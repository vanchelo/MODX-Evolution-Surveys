<?php defined('MODX_BASE_PATH') or die('Error'); ?>
<?php /** @var Surveys $app */ ?>
<?php /** @var array $surveys */ ?>
<?php /** @var Survey $s */ ?>
<table style="width:100%;border-collapse:collapse" class="table table-striped table-bordered table-condensed">
    <thead>
    <tr>
        <th style="text-align: center">#</th>
        <th><?= $app->t('title') ?></th>
        <th style="text-align: center"><?= $app->t('voters') ?></th>
        <th style="text-align: center"><?= $app->t('created_at') ?></th>
        <th style="text-align: center"><?= $app->t('closed_at') ?></th>
        <th style="text-align: center"><?= $app->t('status') ?></th>
        <th style="text-align: center"></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($surveys as $s): ?>
    <tr>
        <td style="text-align:center;width:30px"><?= $s->id ?></td>
        <td><?= e($s->title) ?></td>
        <td style="text-align:center"><?= $s->votes ?></td>
        <td style="text-align:center"><?= $s->created_at ?></td>
        <td style="text-align:center"><?= $s->isClosed() ? $s->closed_at : 'нет' ?></td>
        <td style="text-align:center"><?= $app->t($s->isActive() ? 'published' : 'unpublished') ?></td>
        <td style="text-align:center" class="action_buttons">
            <a title="<?= $app->t('remove') ?>" href="#" onclick="return Survey.delete(<?= $s->id ?>)"><span class="icon-trash"></span></a>
            <a title="<?= $app->t('edit') ?>" href="index.php?a=112&id=<?= $id ?>&action=update&survey=<?= $s->id ?>"><span class="icon-edit"></span></a>
            <a title="<?= $app->t($s->isClosed() ? 'close' : 'open') ?>" href="#" onclick="return Survey.close(<?= $s->id ?>)"><span class="icon-<?= $s->isClosed() ? 'lock-closed' : 'lock-open' ?>-outline"></span></a>
            <a title="<?= $app->t('reset') ?>" href="#" onclick="return Survey.reset(<?= $s->id ?>)"><span class="icon-arrow-sync-outline"></span></a>
            <a title="<?= $app->t('info') ?>" href="index.php?a=112&id=<?= $id ?>&action=info&survey=<?= $s->id ?>"><span class="icon-info-large-outline"></span></a>
        </td>
    </tr>
    <?php endforeach ?>
    </tbody>
</table>
