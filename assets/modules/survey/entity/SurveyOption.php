<?php

class SurveyOption
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $title = '';
    /**
     * @var int
     */
    public $votes = 0;
    /**
     * @var int
     */
    public $survey_id = 0;
    /**
     * @var int
     */
    public $sort = 0;
    /**
     * @var array
     */
    public $answers = array();

    /**
     * SurveyOption constructor.
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
