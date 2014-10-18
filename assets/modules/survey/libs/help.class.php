<?php

class Help {

    protected $chunks = array();
    protected $encoding = 'UTF-8';
    /**
     * @var DocumentParser $modx
     */
    protected $modx;

    protected static $instance;

    public static function instance(DocumentParser & $modx = null)
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        return self::$instance = new self($modx);
    }

    protected function __construct($modx)
    {
        $this->modx = $modx;
    }

    /**
     * Очистка строки от нежелательных символов
     *
     * @param  string $value
     * @param  bool   $stripTags Удалять или нет теги
     *
     * @return string
     */
    public function clean($value, $stripTags = true)
    {
        if ($value === null)
        {
            return null;
        }

        if ($stripTags)
        {
            $value = strip_tags((string) $value);
        }

        return $this->entities(trim($value));
    }

    /**
     * Очистка и обеззаражевание массива
     *
     * @param array $array      Исходный массив
     * @param bool  $stripTags  Удалять или нет теги
     * @param bool  $unsetEmpty Удалять или нет пустые ключи
     * @param bool  $unique     Оставлять только уникальные значения
     *
     * @return void
     */
    public function cleanArray(array &$array, $stripTags = true, $unsetEmpty = true, $unique = false)
    {
        foreach ($array as $k => &$e)
        {
            if (is_array($e))
            {
                $this->cleanArray($e, $stripTags, $unsetEmpty);
                continue;
            }
            if ($stripTags)
            {
                $e = strip_tags($e);
            }

            $e = $this->entities(trim($e));

            if ($unsetEmpty and $e == '')
            {
                unset($array[$k]);
            }

            if ($unique)
            {
                $array = array_unique($array);
            }
        }
    }

    /**
     * Кодирует символы в HTML сущности
     *
     * @param  string $value
     *
     * @return string
     */
    public function entities($value)
    {
        return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * Алиас для entities()
     */
    public function e($value)
    {
        return $this->entities($value);
    }

    /**
     * Вычисляет длину строки
     *
     * @param $value
     *
     * @return int
     */
    public function strlen($value)
    {
        return MB_STRING ? mb_strlen((string) $value, $this->encoding) : strlen((string) $value);
    }

    public function title($value)
    {
        if (MB_STRING)
        {
            return mb_convert_case((string) $value, MB_CASE_TITLE, $this->encoding);
        }

        return ucwords(strtolower((string) $value));
    }

    /**
     * Проверяет пришел запрос через AJAX или нет
     *
     * @return bool
     */
    public function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ? true : false;
    }

    /**
     * Проверяет каким методом пришел запрос
     *
     * @param string $method
     *
     * @return bool
     */
    public function isMethod($method = 'GET')
    {
        return $_SERVER['REQUEST_METHOD'] === strtoupper($method);
    }

    /**
     * Чистим строку от нежелательных символов, оставляем, латиницу, цифры, ._@-
     *
     * @param $string
     *
     * @return mixed
     */
    public function sanitizeString($string)
    {
        return preg_replace('/[^\w_\d@.-]/', '', (string) $string);
    }

    public function isMemberOfGroup($id = 0)
    {
        if ( ! $userId = $this->getUserId())
        {
            return false;
        }

        $tbl = $this->modx->getFullTableName('web_groups');

        $query = $this->modx->db->query("SELECT id FROM {$tbl} WHERE webgroup={$id} AND webuser={$userId} LIMIT 1");

        return $this->modx->db->getRow($query) ? true : false;
    }

    public function getUserId()
    {
        if ( ! isset($_SESSION['webValidated']) or ! $_SESSION['webValidated'])
        {
            return false;
        }

        return (int) $_SESSION['webInternalKey'];
    }

    public function getUserInfo($select = '*')
    {
        if ( ! $id = $this->getUserId())
        {
            return null;
        }

        $db =& $this->modx->db;

        $tbl = $this->modx->getFullTableName("web_user_attributes");

        $query = $db->select($select, $tbl, "internalKey={$id}", '', 1);

        $data = $db->getRow($query);

        return $data ? $data : null;
    }


    public function getGroupsByUserId($id = null)
    {
        if (is_null($id) and ! $id = $this->getUserId())
        {
            return null;
        }

        $db =& $this->modx->db;

        $web_groups = $this->modx->getFullTableName("web_groups");
        $webgroup_names = $this->modx->getFullTableName("webgroup_names");

        $query = $db->query("SELECT g.id, g.name FROM {$webgroup_names} as g INNER JOIN {$web_groups} as wg ON g.id = wg.webgroup WHERE wg.webuser = {$id}");

        $groups = array();
        while ($row = $db->getRow($query))
        {
            $groups[] = $row;
        }

        return $groups ? $groups : null;
    }
}
