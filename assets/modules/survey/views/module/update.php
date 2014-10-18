<?php defined('MODX_BASE_PATH') or die('Error'); ?>
<form method="POST" action="index.php?a=112&id=<?= $id ?>&action=<?php echo $survey['id'] ? 'update' : 'create' ?>" onsubmit="return Survey.<?php echo $survey['id'] ? 'update' : 'create' ?>(this)">
    <input type="hidden" name="survey" value="<?= $survey['id'] ?>"/>

    <table class="fields">
        <tr>
            <td>
                <div>
                <label for="title">Заголовок</label>
                <input required id="title" type="text" name="title" value="<?= $app->e($survey['title']) ?>"/>
                </div>

                <div>
                <label for="description">Описание</label>
                <textarea name="description" id="description" cols="30" rows="10"><?= $survey['description'] ?></textarea>
                </div>

                <div>
                <label for="active"><input id="active" type="checkbox" name="active" value="1" <?php echo $survey['active'] ? 'checked' : '' ?>/> Опубликован</label>
                </div>
            </td>
            <td>
                <div class="options">
                    <label>Варианты ответов</label>
                    <ul>
                        <?php if (isset($options) and is_array($options) and $options): ?>
                        <?php foreach($options as $o): ?>
                            <li><input required type="text" name="option[id_<?= $o['id'] ?>]" value="<?= $o['title'] ?>"/><input type="text" name="option_sort[id_<?= $o['id'] ?>]" value="<?= $o['sort'] ?>" /><span onclick="Survey.removeOption(this)">&times;</span></li>
                        <?php endforeach ?>
                        <?php endif ?>
                        <?php if (!$survey['id']): ?>
                            <li><input required type="text" name="new_option[]" value=""/><input type="text" name="new_option_sort[]" value="0" /><span onclick="Survey.removeOption(this)">&times;</span></li>
                        <?php endif ?>
                        <li><button type="button" onclick="Survey.addOption(this)"><img alt="" src="media/style/<?= $modx->config['manager_theme'] ?>/images/icons/add.png"> Добавить вариант</button></li>
                    </ul>
                </div>
            </td>
        </tr>
    </table>


    <div class="buttons">
        <button type="submit"><img alt="icons_resource_duplicate" src="media/style/<?= $modx->config['manager_theme'] ?>/images/icons/save.png"> Сохранить</button>
    </div>
</form>
