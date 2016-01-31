<?php

class SurveyAnswer
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var int
     */
    public $survey_id = 0;
    /**
     * @var int
     */
    public $option_id = 0;
    /**
     * @var int
     */
    public $user_id = 0;
    /**
     * @var string
     */
    public $created_at;
    /**
     * @var SurveyUser
     */
    public $user;

    /**
     * SurveyAnswer constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = array())
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
