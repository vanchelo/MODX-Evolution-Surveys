<?php

class SurveyResponse
{
    protected $error;
    protected $message;
    protected $data;

    function __construct($message = '', $error = false, array $data = array())
    {
        $this->data = $data;
        $this->error = $error;
        $this->message = $message;
    }

    /**
     * @param bool $error
     *
     * @return self
     */
    public function isError($error = true)
    {
        $this->error = (bool) $error;

        return $this;
    }

    /**
     * @param string $message
     *
     * @return self
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @param array $data
     *
     * @return self
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param array $errors
     *
     * @return self
     */
    public function setErrors(array $errors)
    {
        $this->setData(array('errors' => $errors));
        $this->isError();

        return $this;
    }

    /**
     * @return string
     */
    function __toString()
    {
        return $this->toJson(JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'error' => $this->error,
            'message' => $this->message
        ) + $this->data;
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     *
     * @return string
     */
    public function toJson($options = JSON_UNESCAPED_UNICODE)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * @return $this
     */
    public function reset()
    {
        $this->data = array();
        $this->error = false;
        $this->message = '';

        return $this;
    }

    public function display()
    {
        header('Content-Type: application/json; charset=UTF-8');
        echo $this->toJson();
        return null;
    }
}
