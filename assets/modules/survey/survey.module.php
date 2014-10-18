<?php defined('IN_MANAGER_MODE') or die('Error');

/** @var DocumentParser $modx */

require 'surveys.class.php';
$survey = new Surveys($modx);

$action = isset($_GET['action']) ? (string) $_GET['action'] : '';

$data = array(
    'app' => $survey
);

if ($action == 'create') {
    if ($survey->helper()->isMethod('post')) {
        echo $survey->createSurvey($_POST);

        return;
    }

    $tpl = 'module.update';
    $title = 'Создание опроса';
    $data['survey'] = array(
        'id' => 0,
        'title' => '',
        'description' => '',
        'options' => array()
    );
} elseif ($action == 'delete') {
    if (!isset($_GET['survey']) or !$id = (int) $_GET['survey']) {
        echo $survey->error('Ошибка');

        return;
    }

    echo $survey->deleteSurvey($id);

    return;
} elseif ($action == 'close') {
    if (!isset($_GET['survey']) or !$id = (int) $_GET['survey']) {
        echo $survey->error('Ошибка');

        return;
    }

    echo $survey->closeSurvey($id);

    return;
} elseif ($action == 'reset') {
    if (!isset($_GET['survey']) or !$id = (int) $_GET['survey']) {
        echo $survey->error('Ошибка');

        return;
    }

    echo $survey->resetSurvey($id);

    return;
} elseif ($action == 'update') {
    if ($survey->helper()->isMethod('post')) {
        echo $survey->updateSurvey($_POST);

        return;
    }

    if (!isset($_GET['survey']) or !$id = (int) $_GET['survey']) {
        echo 'Ошибка';

        return;
    }

    $tpl = 'module.update';
    $title = 'Редактирование опроса';

    $data['survey'] = $survey->getSurvey($id);
    $data['options'] = $survey->getSurveyOptions($id);
} elseif ($action == 'install') {
    $install = $survey->install();

    $tpl = 'module.install';
    $title = 'Установка';
    $data['message'] = $install;
} else {
    if ($survey->isInstalled()) {
        $tpl = 'module.index';
        $title = 'Управление опросами';

        $data['surveys'] = $survey->getAllSurveys();
    } else {
        $tpl = 'module.install';
        $title = 'Установка';
    }
}

$survey->view()->share('id', isset($_GET['id']) ? (int) $_GET['id'] : 0);

echo $survey->view('module.layout', array(
    'content' => $survey->view($tpl, $data),
    'title' => $title,
));
