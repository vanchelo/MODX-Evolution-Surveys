<?php

class SurveyOption
{
    public $id;
    public $title = '';
    public $votes = 0;
    public $survey_id = 0;
    public $sort = 0;
    public $answers = array();

    function __construct(array $attributes = array())
    {
        if ($attributes) {
            foreach ($attributes as $name => $value) {
                if (property_exists($this, $name)) {
                    $this->{$name} = $value;
                }
            }
        }
    }
}
