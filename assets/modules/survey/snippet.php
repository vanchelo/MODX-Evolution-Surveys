<?php defined('MODX_BASE_PATH') or die('Error');
/** @var DocumentParser $modx */
require_once 'surveys.class.php';

$survey = new Surveys($modx);

return $survey->handle();
