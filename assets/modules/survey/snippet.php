<?php defined('MODX_BASE_PATH') or die('Error');
/** @var DocumentParser $modx */
require_once 'surveys.class.php';

$id = isset($id) ? (int) $id : null;
$tpl = isset($tpl) ? $tpl : 'surveys';
$limit = isset($limit) ? (int) $limit : 10;
$random = isset($random) ? (bool) $random : false;

$survey = new Surveys($modx, array(
    'lang' => isset($lang) ? $lang : 'ru'
));

return $survey->render($id, $tpl, $limit, $random);
