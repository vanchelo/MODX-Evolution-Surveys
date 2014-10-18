<?php

require_once 'help.class.php';

abstract class Component {
    /**
     * @var DocumentParser
     */
    protected $modx;
    /**
     * @var Help
     */
    protected $help;
    /**
     * @var DBAPI
     */
    protected $db;

    function __construct(DocumentParser & $modx)
    {
        $this->modx =& $modx;
        $this->db   =& $modx->db;
        $this->help = Help::instance($modx);
    }

    /**
     * Экранирование строки
     *
     * @param $value
     *
     * @return string
     */
    public function e($value = '')
    {
        return $this->help->entities($value);
    }

    protected function response($data = array(), $option = 0)
    {
        return json_encode($data, $option);
    }

    public function error($message, $errors = array())
    {
        return $this->response(array(
            'error'   => true,
            'message' => $message,
            'errors'  => $errors
        ));
    }

    public function success($message, $data = null)
    {
        $response = array(
            'error'   => false,
            'message' => $message
        );

        if ($data !== null)
        {
            $response['data'] = $data;
        }

        return $this->response($response);
    }

    /**
     * @return \Help
     */
    public function helper()
    {
        return $this->help;
    }

    /**
     * @return \DocumentParser
     */
    public function getModx()
    {
        return $this->modx;
    }

    /**
     * @return \DBAPI
     */
    public function getDB()
    {
        return $this->db;
    }

}
