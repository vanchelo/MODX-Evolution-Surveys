<?php defined('MODX_BASE_PATH') or die('Error'); ?>
<?php /** @var array $surveys */ ?>
<table style="width:100%" class="table table-striped table-bordered table-condensed">
    <thead>
    <tr>
        <th style="text-align: center">#</th>
        <th>Название</th>
        <th style="text-align: center">Кол-во голосовавших</th>
        <th style="text-align: center">Дата создания</th>
        <th style="text-align: center">Дата закрытия</th>
        <th style="text-align: center">Статус</th>
        <th style="text-align: center">Действия</th>
    </tr>
    </thead>
    <tbody>
    <?php /** @var Survey $s */ ?>
    <?php foreach ($surveys as $s): ?>
    <tr>
        <td style="text-align:center;width:30px"><?= $s->id ?></td>
        <td><?= e($s->title) ?></td>
        <td style="text-align:center"><?= $s->votes ?></td>
        <td style="text-align:center"><?= $s->created_at ?></td>
        <td style="text-align:center"><?= $s->isClosed() ? $s->closed_at : 'нет' ?></td>
        <td style="text-align:center"><?= $s->isActive() ? 'Опубликован' : 'Не опубликован' ?></td>
        <td style="text-align:center" class="action_buttons">
            <a title="Удалить" href="#" onclick="return Survey.delete(<?= $s->id ?>)"><img alt="" src="media/style/<?= $modx->config['manager_theme'] ?>/images/icons/delete.png"></a>
            <a href="index.php?a=112&id=<?= $id ?>&action=update&survey=<?= $s->id ?>">Изменить</a>
            <a href="#" onclick="return Survey.close(<?= $s->id ?>)"><?= $s->isClosed() ? 'Открыть' : 'Закрыть' ?></a>
            <a href="#" onclick="return Survey.reset(<?= $s->id ?>)">Сбросить</a>
            <a href="index.php?a=112&id=<?= $id ?>&action=info&survey=<?= $s->id ?>">Инфо</a>
        </td>
    </tr>
    <?php endforeach ?>
    </tbody>
</table>
