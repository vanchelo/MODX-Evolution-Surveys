<?php

class SurveyAnswer
{
    public $id;
    public $survey_id = 0;
    public $option_id = 0;
    public $user_id = 0;
    public $created_at;
    /** @var SurveyUser */
    public $user;

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
