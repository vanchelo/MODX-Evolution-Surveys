<?php
/** @var DocumentParser $modx */
define('MODX_API_MODE', true);

include_once dirname(dirname(dirname(dirname(__FILE__)))) . '/index.php';
require_once 'surveys.class.php';
require_once 'surveyresponse.class.php';

$surveys = new Surveys($modx);
$response = new SurveyResponse();

$modx->db->connect();
if (empty($modx->config)) {
    $modx->getSettings();
}

$modx->invokeEvent('OnWebPageInit');

if (!isMethod('get') || !isAjax()) {
    return $response->isError()->setMessage($surveys->t('method_not_allowed'))->display();
}

$vote = $surveys->vote(
    isset($_GET['survey']) ? $_GET['survey'] : null,
    isset($_GET['option']) ? $_GET['option'] : null
);

if (!is_array($vote)) {
    $response->isError()->setMessage($vote);
} else {
    $response->setMessage($vote['message'])->setData($vote);
}

return $response->display();
