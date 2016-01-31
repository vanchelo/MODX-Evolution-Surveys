<?php

require_once 'helpers.php';

class Surveys
{
    /**
     * @var DocumentParser
     */
    protected $modx;
    /**
     * @var DBAPI
     */
    protected $db;
    /**
     * @var array
     */
    protected $lang;
    /**
     * Таблица опросов
     *
     * @var string
     */
    protected $surveysTbl;
    /**
     * Таблица ответов на опросы
     *
     * @var string
     */
    protected $answersTbl;
    /**
     * Таблица вариантов ответов для опросов
     *
     * @var string
     */
    protected $optionsTbl;
    /**
     * Конфиг
     *
     * @var array
     */
    protected $config = array();

    /**
     * Surveys constructor.
     *
     * @param DocumentParser $modx
     * @param array          $config
     */
    function __construct(DocumentParser & $modx, array $config = array())
    {
        $this->modx =& $modx;
        $this->db =& $modx->db;

        $this->surveysTbl = $modx->getFullTableName('surveys');
        $this->answersTbl = $modx->getFullTableName('survey_answers');
        $this->optionsTbl = $modx->getFullTableName('survey_options');

        $configFile = require 'config.php';

        $this->config = $config + $configFile;

        $this->lang = require dirname(__FILE__) . '/lang/' . $this->config['lang'] . '.php';
    }

    /**
     * Вывод активных опросов во фронтэнде или одного опроса по ID
     *
     * @param int    $id     ID опроса
     * @param string $tpl    Шаблон
     * @param int    $limit  Кол-во опросов
     * @param bool   $random Случайная выборка опросов
     *
     * @return string
     */
    public function render($id = null, $tpl = null, $limit = 10, $random = false)
    {
        if ($id !== null) {
            $surveys = array($id => $this->getActiveSurvey($id));
        } else {
            $surveys = $this->getAllActiveSurveys((int) $limit, $random);
        }

        if (!$surveys) {
            return '';
        }

        $ids = array_keys($surveys);

        $options = $this->getSurveyOptions($ids);

        foreach ($surveys as $s) {
            $s->options = count($ids) > 1 ? $options[$s->id] : $options;
        }

        if ($user_id = $this->getUserId()) {
            $query = $this->db->select('survey_id', $this->answersTbl,
                "user_id = {$user_id} AND survey_id IN (" . implode(',', $ids) . ")");

            while ($answer = $this->db->getRow($query)) {
                $surveys[$answer['survey_id']]->voted = true;
            }
        }

        $this->modx->regClientHTMLBlock('<script>window.jQuery || document.write(\'<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js">\x3C/script>\');</script>');
        $this->modx->regClientStartupHTMLBlock('<link rel="stylesheet" href="/assets/modules/survey/assets/survey.css"/>');
        $this->modx->regClientHTMLBlock('<script src="/assets/modules/survey/assets/survey.js"></script>');
        $this->modx->regClientHTMLBlock('<script>var Survey = new Surveys("' . $this->config['connectorUrl'] . '");</script>');

        return $this->view($tpl, array(
            'surveys' => $surveys,
        ));
    }

    /**
     * Обработчик голосования за опрос
     *
     * @param $survey_id
     * @param $option_id
     *
     * @return string
     */
    public function vote($survey_id, $option_id)
    {
        if (!$user_id = $this->modx->getLoginUserID()) {
            return $this->t('need_auth');
        }

        if (!$survey_id = (int) $survey_id) {
            return $this->t('survey_id_error');
        }

        if (!$option_id = (int) $option_id) {
            return $this->t('option_id_error');
        }

        $survey = $this->getActiveSurvey($survey_id);

        if (!$survey) {
            return $this->t('survey_error');
        }

        if ($survey->isClosed()) {
            return $this->t('survey_closed');
        }

        if (!$survey->isActive()) {
            return $this->t('survey_error');
        }

        if ($this->checkForAnswer($survey_id, $user_id)) {
            return $this->t('already_answered');
        }

        if (!$option = $this->getOption($option_id)) {
            return $this->t('option_error');
        }

        if (!$this->addVote($survey_id, $option_id)) {
            return $this->t('add_vote_error');
        }

        $survey->options = $this->getSurveyOptions($survey_id);
        $survey->votes++;

        return array(
            'message' => $this->t('success_voted'),
            'html' => $this->view('survey', array(
                'survey' => $survey,
            )),
        );
    }

    /**
     * Сброс голосов опроса
     *
     * @param $id
     *
     * @return string
     */
    public function resetSurvey($id)
    {
        if (!$this->checkSurvey($id)) {
            return false;
        }

        $this->db->delete($this->answersTbl, "survey_id = {$id}");
        $this->db->update(array('votes' => 0), $this->optionsTbl, "survey_id = {$id}");
        $this->db->update(array(
            'votes' => 0,
            'updated_at' => $this->getTimestamp(),
        ), $this->surveysTbl, "id = {$id}");

        return true;
    }

    /**
     * Добавление голоса на опрос
     *
     * @param int $survey_id
     * @param int $option_id
     *
     * @return int|null
     */
    protected function addVote($survey_id, $option_id)
    {
        $user_id = $this->modx->getLoginUserID();

        $id = $this->db->insert(array(
            'survey_id' => $survey_id,
            'user_id' => $user_id,
            'option_id' => $option_id,
            'created_at' => $this->getTimestamp(),
        ), $this->answersTbl);

        if (!$id) {
            return null;
        }

        $this->db->update('votes = votes + 1', $this->surveysTbl, "id = {$survey_id}");
        $this->db->update('votes = votes + 1', $this->optionsTbl, "id = {$option_id}");

        return $id;
    }

    /**
     * Получение активного опроса по ID
     *
     * @param int $id ID опроса
     *
     * @return null|Survey
     */
    public function getActiveSurvey($id)
    {
        $survey = $this->getSurvey($id);

        return $survey->active ? $survey : null;
    }

    /**
     * Проверка сущестования активного опроса по ID
     *
     * @param int $id ID пороса
     *
     * @return bool
     */
    public function checkActiveSurvey($id)
    {
        return $this->getActiveSurvey($id) ? true : false;
    }

    /**
     * Проверка сущестования опроса по ID
     *
     * @param int $id ID пороса
     *
     * @return bool
     */
    public function checkSurvey($id)
    {
        return $this->db->getRecordCount($this->db->select('count(*)', $this->surveysTbl,
            "id = {$id}")) > 0 ? true : false;
    }

    /**
     * Получение опроса по ID
     *
     * @param int $id
     *
     * @return null|Survey
     */
    public function getSurvey($id)
    {
        if (!$id = (int) $id) {
            return null;
        }

        $query = $this->db->select('*', $this->surveysTbl, "id = {$id}");

        if (!$survey = $this->db->getRow($query)) {
            return null;
        }

        return $this->survey($survey);
    }

    /**
     * @param $id
     *
     * @return null|Survey
     */
    public function getSurveyWithRelations($id)
    {
        if (!$id = (int) $id) {
            return null;
        }

        $query = $this->db->select('*', $this->surveysTbl, "id = {$id}");

        if (!$survey = $this->db->getRow($query)) {
            return null;
        }

        $survey = $this->survey($survey);

        $query = $this->db->select('*', $this->optionsTbl, "survey_id = {$id}");

        while ($row = $this->db->getRow($query)) {
            $survey->options[$row['id']] = $this->option($row);
        }

        $query = $this->db->select('*', $this->answersTbl, "survey_id = {$id}");

        while ($row = $this->db->getRow($query)) {
            $survey->options[$row['option_id']]->answers[$row['id']] = $this->answer($row);
        }

        $query = $this->db->query("
        SELECT a.user_id as id, a.id as answer_id, a.option_id, u.fullname as name, u.email, us.username FROM {$this->answersTbl} as a
        LEFT JOIN {$this->modx->getFullTableName('web_user_attributes')} as u ON u.internalKey = a.user_id
        LEFT JOIN {$this->modx->getFullTableName('web_users')} as us ON u.internalKey = us.id
        WHERE a.survey_id = {$id}
        ");

        while ($row = $this->db->getRow($query)) {
            $survey
                ->options[$row['option_id']]
                ->answers[$row['answer_id']]
                ->user = $this->user($row);
        }

        return $survey;
    }

    /**
     * Получение опроса по ID
     *
     * @param int $id
     *
     * @return array|null
     */
    public function getSurveyWithOptions($id)
    {
        if (!$survey = $this->getSurvey($id)) {
            return null;
        }

        $survey->options = $this->getSurveyOptions($id);

        return $survey;
    }

    /**
     * Получение варианта ответа на опрос по ID варианта
     *
     * @param int $id
     *
     * @return array|null
     */
    public function getOption($id)
    {
        if (!$id = (int) $id) {
            return null;
        }

        $query = $this->db->select('*', $this->optionsTbl, "id = {$id}");

        if (!$option = $this->db->getRow($query)) {
            return null;
        }

        return $option;
    }

    /**
     * @param int    $survey_id
     * @param int    $user_id
     * @param string $select
     *
     * @return mixed|null
     */
    public function getAnswerBySurveyAndUserId($survey_id, $user_id, $select = '*')
    {
        if (!$survey_id = (int) $survey_id) {
            return null;
        }

        if (!$user_id = (int) $user_id) {
            return null;
        }

        $query = $this->db->select($select, $this->answersTbl, "'survey_id = {$survey_id} AND user_id = {$user_id}");

        if (!$answer = $this->db->getRow($query)) {
            return null;
        }

        return $answer;
    }

    /**
     * Проверка голосовал пользователь за опрос или нет
     *
     * @param int $survey_id ID опроса
     * @param int $user_id   ID пользователя
     *
     * @return bool
     */
    protected function checkForAnswer($survey_id, $user_id)
    {
        if (!$survey_id = (int) $survey_id) {
            return null;
        }
        if (!$user_id = (int) $user_id) {
            return null;
        }

        $query = $this->db->query('SELECT id from ' . $this->answersTbl . ' WHERE survey_id=' . $survey_id . ' and user_id=' . $user_id . ' LIMIT 1');

        return $this->db->getRecordCount($query) > 0 ? true : false;
    }

    /**
     * Получение всех опросов
     *
     * @return array
     */
    public function getAllSurveys()
    {
        $query = $this->db->select('*', $this->surveysTbl);

        $surveys = array();
        while ($row = $this->db->getRow($query)) {
            $surveys[$row['id']] = $this->survey($row);
        }

        return $surveys;
    }

    /**
     * Получение активных опросов
     *
     * @param int  $limit Кол-во поросов для получения
     * @param bool $random
     *
     * @return array
     */
    protected function getAllActiveSurveys($limit = 10, $random = false)
    {
        $order = $random ? 'RAND()' : 'created_at DESC';
        $query = $this->db->select('*', $this->surveysTbl, 'active = 1', $order, (int) $limit);

        $surveys = array();
        while ($survey = $this->db->getRow($query)) {
            $surveys[$survey['id']] = $this->survey($survey);
        }

        return $surveys;
    }

    /**
     * Получение вариантов ответа опроса по ID
     *
     * @param mixed $ids
     *
     * @return array
     */
    public function getSurveyOptions($ids = array())
    {
        if (!is_array($ids)) {
            $ids = array($ids);
        }

        $query = $this->db->select('*', $this->optionsTbl, 'survey_id IN (' . implode(',', $ids) . ')', 'sort ASC');

        $options = array();
        if (count($ids) === 1) {
            while ($row = $this->db->getRow($query)) {
                $options[] = $this->option($row);
            }
        } else {
            while ($row = $this->db->getRow($query)) {
                if (!isset($options[$row['survey_id']])) {
                    $options[$row['survey_id']] = array();
                }

                $options[$row['survey_id']][] = $this->option($row);
            }
        }

        return $options;
    }

    /**
     * Создание опроса
     *
     * @param array $data
     *
     * @return array|bool
     */
    public function createSurvey($data = array())
    {
        cleanArray($data);

        $errors = array();
        if (empty($data['title'])) {
            $errors[] = $this->t('empty_title');
        }

        if (!isset($data['new_option']) || !is_array($data['new_option'])) {
            $errors[] = $this->t('options_error');
        } else {
            $data['new_option'] = array_unique($data['new_option']);
            if (count($data['new_option']) < 2) {
                $errors[] = $this->t('min_options');
            }
        }

        if ($errors) {
            return $errors;
        }

        $id = $this->db->insert(array(
            'title' => $data['title'],
            'description' => !empty($data['description']) ? $data['description'] : null,
            'active' => isset($data['active']) ? 1 : 0,
            'created_at' => $this->getTimestamp(),
            'updated_at' => $this->getTimestamp(),
        ), $this->surveysTbl);

        foreach ($data['new_option'] as $k => &$o) {
            $this->db->insert(array(
                'title' => $o,
                'sort' => isset($data['new_option_sort'][$k]) ? (int) $data['new_option_sort'][$k] : $k,
                'survey_id' => $id,
            ), $this->optionsTbl);
        }

        return true;
    }

    /**
     * Обновление опроса
     *
     * @param array $data
     *
     * @return string
     */
    public function updateSurvey($data = array())
    {
        cleanArray($data);

        $errors = array();

        $id = isset($data['survey']) ? (int) $data['survey'] : 0;
        if (!$id) {
            $errors[] = $this->t('survey_id_error');
        }

        if (empty($data['title'])) {
            $errors[] = $this->t('empty_title');
        }

        $options = array();
        if (isset($data['option']) && is_array($data['option'])) {
            $options = $data['option'];
        }

        $newOptions = array();
        if (isset($data['new_option']) && is_array($data['new_option'])) {
            $newOptions = array_diff($options, $data['new_option']);
        }

        if (count($options) + count($newOptions) < 2) {
            $errors[] = $this->t('min_options');
        }

        if ($errors) {
            return $errors;
        }

        if (!$survey = $this->getSurvey($id)) {
            return false;
        }

        $this->db->update(array(
            'title' => $data['title'],
            'description' => isset($data['description']) ? $data['description'] : '',
            'active' => isset($data['active']) ? 1 : 0,
            'updated_at' => $this->getTimestamp(),
        ), $this->surveysTbl, "id = {$id}");

        $options = $this->getSurveyOptions($id);
        $optionsTitles = array();

        foreach ($options as $o) {
            $oid = 'id_' . $o->id;
            if (isset($data['option'][$oid])) {
                $this->db->update(array(
                    'title' => $data['option'][$oid],
                    'sort' => isset($data['option_sort'][$oid]) ? $data['option_sort'][$oid] : $o->sort,
                ), $this->optionsTbl, "id = {$o->id}");
                $optionsTitles[] = $o->title;
            } else {
                $this->db->delete($this->optionsTbl, "id = {$o->id}");
                $this->db->delete($this->answersTbl, "option_id = {$o->id}");

                if ($o->votes > 0) {
                    $this->db->update("votes = votes - {$o->votes}", $this->surveysTbl, "id = {$id}");
                }
            }
        }

        if (isset($data['new_option']) && count($data['new_option'])) {
            foreach ($data['new_option'] as $k => $o) {
                if (in_array($o, $optionsTitles)) {
                    continue;
                }

                $this->db->insert(array(
                    'title' => $o,
                    'sort' => isset($data['new_option_sort'][$k]) ? (int) $data['new_option_sort'][$k] : $k,
                    'survey_id' => $id,
                ), $this->optionsTbl);
            }
        }

        return true;
    }

    /**
     * Закрытие опроса по ID
     *
     * @param int $id
     *
     * @return Survey|bool
     */
    public function closeSurvey($id)
    {
        if (!$survey = $this->getSurvey($id, 'id, active, closed_at')) {
            return false;
        }

        $this->db->update(
            'closed_at = ' . ($survey->isClosed() ? 'NULL' : "'{$this->getTimestamp()}'") .
            ',updated_at = ' . ("'{$this->getTimestamp()}'"),
            $this->surveysTbl, "id={$id}");

        return $survey;
    }

    /**
     * @return bool|string
     */
    protected function getTimestamp()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Удаление опроса по ID
     *
     * @param int $id
     *
     * @return string
     */
    public function deleteSurvey($id)
    {
        if (!$survey = $this->checkSurvey((int) $id)) {
            return false;
        }

        $this->db->delete($this->surveysTbl, "id={$id}");
        $this->db->delete($this->optionsTbl, "survey_id={$id}");
        $this->db->delete($this->answersTbl, "survey_id={$id}");

        return true;
    }

    /**
     * Получение всех пользователей голосовавших за опрос
     *
     * @param $id
     *
     * @return array
     */
    public function getSurveyUsersById($id)
    {
        $users = array();

        $query = $this->db->query("
        SELECT a.user_id as id, a.option_id, u.fullname as name, u.email, us.username FROM {$this->answersTbl} as a
        LEFT JOIN {$this->modx->getFullTableName('web_user_attributes')} as u ON u.internalKey = a.user_id
        LEFT JOIN {$this->modx->getFullTableName('web_users')} as us ON u.internalKey = us.id
        WHERE a.survey_id = {$id}
        ");

        while ($row = $this->db->getRow($query)) {
            if (!isset($users[$row['option_id']])) {
                $users[$row['option_id']] = array();
            }

            $users[$row['option_id']][] = $this->user($row);
        }

        return $users;
    }

    /**
     * Получение языковой строки
     *
     * @param string $key
     * @param string $default
     *
     * @return string
     */
    public function t($key, $default = '')
    {
        return isset($this->lang[$key]) ? $this->lang[$key] : $default;
    }

    /**
     * @param null  $tpl
     * @param array $data
     *
     * @return string|View
     */
    public function view($tpl = null, $data = array())
    {
        static $view;
        if (!isset($view)) {
            require_once 'libs/view.class.php';
            $view = new View($this->config['viewsDir']);
            $view->share('app', $this);
            $view->share('modx', $this->modx);
        }

        if ($tpl !== null) {
            return $view->fetchPartial($tpl, $data);
        }

        return $view;
    }

    /**
     * @param $surveyVotes
     * @param $optionVotes
     *
     * @return float|int
     */
    public function calculateRate($surveyVotes, $optionVotes)
    {
        return $surveyVotes > 0 ? floor((100 / $surveyVotes) * $optionVotes) : 0;
    }

    /**
     * @return bool
     */
    public function isInstalled()
    {
        return file_exists($this->config['path'] . 'setup.sql') ? false : true;
    }

    /**
     * @return string
     */
    public function install()
    {
        $tbl_prefix = $this->db->config['table_prefix'];

        $sqlfile = $this->config['path'] . 'setup.sql';

        if (!file_exists($sqlfile) || !is_readable($sqlfile)) {
            return $this->t('setup.sql_does_not_exists');
        }

        $sql = str_replace('{prefix}', $tbl_prefix, file_get_contents($sqlfile));

        $matches = array();
        preg_match_all('/CREATE TABLE.*?;/ims', $sql, $matches);

        $this->db->query('SET AUTOCOMMIT=0;');
        $this->db->query('START TRANSACTION;');
        $errors = false;

        foreach ($matches[0] as $sqlcmd) {
            $rs = $this->db->query($sqlcmd);
            if (!$rs) {
                $errors = true;
                break;
            }
        }

        if ($errors) {
            $this->db->query('ROLLBACK;');
        } else {
            $this->db->query('COMMIT;');
        }

        $this->db->query('SET AUTOCOMMIT=1;');

        if ($errors) {
            return 'Ошибка установки модуля';
        }

        unlink($sqlfile);

        return 'Модуль успешно установлен';
    }

    /**
     * @return bool|int
     */
    public function getUserId()
    {
        if (!isset($_SESSION['webValidated']) || !$_SESSION['webValidated']) {
            return false;
        }

        return (int) $_SESSION['webInternalKey'];
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function isMemberOfGroup($id = 0)
    {
        if (!$userId = $this->getUserId()) {
            return false;
        }

        $tbl = $this->modx->getFullTableName('web_groups');

        $query = $this->db->query("SELECT id FROM {$tbl} WHERE webgroup={$id} AND webuser={$userId} LIMIT 1");

        return $this->db->getRow($query) ? true : false;
    }

    /**
     * @param array $properties
     *
     * @return Survey
     */
    public function survey($properties = array())
    {
        if (!class_exists('Survey')) {
            require_once 'entity/Survey.php';
        }

        return new Survey($properties);
    }

    /**
     * @param array $properties
     *
     * @return SurveyUser
     */
    public function user($properties = array())
    {
        if (!class_exists('SurveyUser')) {
            require_once 'entity/SurveyUser.php';
        }

        return new SurveyUser($properties);
    }

    /**
     * @param array $properties
     *
     * @return SurveyOption
     */
    public function option($properties = array())
    {
        if (!class_exists('SurveyOption')) {
            require_once 'entity/SurveyOption.php';
        }

        return new SurveyOption($properties);
    }

    /**
     * @param array $properties
     *
     * @return SurveyAnswer
     */
    public function answer($properties = array())
    {
        if (!class_exists('SurveyAnswer')) {
            require_once 'entity/SurveyAnswer.php';
        }

        return new SurveyAnswer($properties);
    }

    /**
     * @param string $message
     * @param bool   $error
     * @param array  $data
     *
     * @return SurveyResponse
     */
    public function ajaxResponse($message = '', $error = false, $data = array())
    {
        if (!class_exists('SurveyResponse')) {
            require_once 'surveyresponse.class.php';
        }

        $response = new SurveyResponse($message, $error, $data);

        return $response;
    }

    /**
     * @return DocumentParser
     */
    public function getModx()
    {
        return $this->modx;
    }

    /**
     * @return DBAPI
     */
    public function getDB()
    {
        return $this->db;
    }
}
