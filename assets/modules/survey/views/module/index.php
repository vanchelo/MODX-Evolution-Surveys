<?php defined('MODX_BASE_PATH') or die('Error'); ?>
<table style="width:100%" class="table table-striped table-bordered table-condensed">
    <thead>
    <tr>
        <th>#</th>
        <th>Название</th>
        <th>Кол-во голосовавших</th>
        <th>Дата создания</th>
        <th>Дата закрытия</th>
        <th>Действия</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($surveys as $s): ?>
    <tr>
        <td style="text-align:center;width:30px"><?= $s['id'] ?></td>
        <td><?= $s['title'] ?></td>
        <td style="text-align:center"><?= $s['votes'] ?></td>
        <td style="text-align:center"><?= $s['created_at'] ?></td>
        <td style="text-align:center"><?= $s['closed_at'] ? $s['closed_at'] : 'нет' ?></td>
        <td style="text-align:center" class="action_buttons">
            <a href="#" onclick="return Survey.delete(<?= $s['id'] ?>)">Удалить</a>
            <a href="index.php?a=112&id=<?= $id ?>&action=update&survey=<?= $s['id'] ?>">Изменить</a>
            <a href="#" onclick="return Survey.close(<?= $s['id'] ?>)"><?= $s['closed_at'] ? 'Открыть' : 'Закрыть' ?></a>
            <a href="#" onclick="return Survey.reset(<?= $s['id'] ?>)"><?= 'Сбросить' ?></a>
        </td>
    </tr>
    <?php endforeach ?>
    </tbody>
</table>
