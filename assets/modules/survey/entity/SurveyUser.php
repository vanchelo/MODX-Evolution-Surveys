<?php

class SurveyUser
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $name = '';
    /**
     * @var string
     */
    public $username = '';
    /**
     * @var string
     */
    public $email = '';

    /**
     * SurveyUser constructor.
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

    /**
     * @return string
     */
    public function displayName()
    {
        return !empty($this->name) ? $this->name : $this->username;
    }
}
