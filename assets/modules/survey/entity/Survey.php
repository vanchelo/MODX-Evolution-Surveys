<?php

class Survey
{
    public $id;
    public $title = '';
    public $description = '';
    public $votes = 0;
    public $active = 0;
    public $created_at;
    public $updated_at;
    public $closed_at;
    public $voted = false;
    public $options = array();

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

    public function isClosed()
    {
        return !empty($this->closed_at);
    }

    public function isActive()
    {
        return (bool) $this->active;
    }
}
