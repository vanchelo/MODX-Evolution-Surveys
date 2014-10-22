<?php

class SurveyUser
{
    public $id;
    public $name = '';
    public $username = '';
    public $email = '';

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

    public function displayName()
    {
        return !empty($this->name) ? $this->name : $this->username;
    }
}
