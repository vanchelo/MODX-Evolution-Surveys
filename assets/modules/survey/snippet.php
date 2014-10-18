<?php
/** @var DocumentParser $modx */
require_once __DIR__ . '/surveys.class.php';

$survey = new Surveys($modx);

return $survey->handle();
