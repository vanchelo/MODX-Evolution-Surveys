<?php defined('IN_MANAGER_MODE') or die('Error');
/** @var DocumentParser $modx */

require __DIR__ . '/surveys.class.php';
require __DIR__ . '/surveymodulecontroller.class.php';

$survey = new Surveys($modx);
$controller = new SurveyModuleController($survey);
$action = isset($_GET['action']) ? (string) $_GET['action'] : 'index';

echo $controller->run($action);
