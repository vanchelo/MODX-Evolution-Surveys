<?php

class Survey
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
     * @var string
     */
    public $description = '';
    /**
     * @var int
     */
    public $votes = 0;
    /**
     * @var int
     */
    public $active = 0;
    /**
     * @var string
     */
    public $created_at;
    /**
     * @var string
     */
    public $updated_at;
    /**
     * @var string
     */
    public $closed_at;
    /**
     * @var bool
     */
    public $voted = false;
    /**
     * @var array
     */
    public $options = array();

    /**
     * Survey constructor.
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
     * @return bool
     */
    public function isClosed()
    {
        return !empty($this->closed_at);
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return (bool) $this->active;
    }
}
