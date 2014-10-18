<?php

require_once 'libs/component.class.php';

class Surveys extends Component
{
    /**
     * ID групп
     */
    const ADMINS_GROUP = 2;
    const MANAGERS_GROUP = 3;

    protected $lang;
    /**
     * Таблица опросов
     */
    protected $surveys_tbl;
    /**
     * Таблица ответов на опросы
     */
    protected $answers_tbl;
    /**
     * Таблица вариантов ответов для опросов
     */
    protected $options_tbl;
    protected $config = [];

    function __construct(DocumentParser & $modx)
    {
        parent::__construct($modx);

        $this->lang = require dirname(__FILE__) . '/lang/ru.php';

        $this->surveys_tbl = $modx->getFullTableName('surveys');
        $this->answers_tbl = $modx->getFullTableName('survey_answers');
        $this->options_tbl = $modx->getFullTableName('survey_options');

        $this->config['path'] = __DIR__ . '/';
        $this->config['relativePath'] = 'assets/snippets/survey/';
        $this->config['assetsUrl'] = MODX_BASE_URL . $this->config['relativePath'];
        $this->config['viewsDir'] = __DIR__ . '/views/';
    }

    /**
     * Вывод активных опросов во фронте
     *
     * @param string $tpl
     * @param int $limit
     * @param bool $random
     *
     * @return string
     */
    public function render($tpl = null, $limit = 10, $random = false)
    {
        $surveys = !$this->isAdmin()
            ? $this->getAllActiveSurveys((int) $limit, $random)
            : $this->getAllSurveys();

        if (!$surveys) return '';

        $ids = array_keys($surveys);

        $options = $this->getSurveyOptions($ids);

        foreach ($surveys as &$s) {
            $s['options'] = count($ids) > 1 ? $options[$s['id']] : $options;
            $s['voted'] = false;
        }

        if (($user_id = $this->help->getUserId()) && $ids) {
            $query = $this->db->select('survey_id', $this->answers_tbl, "user_id = {$user_id} AND survey_id IN (" . implode(',', $ids) . ")");

            while ($answer = $this->db->getRow($query)) {
                $surveys[$answer['survey_id']]['voted'] = true;
            }
        }

        if ($this->modx->documentObject['id'] == $this->modx->config['site_start']) {
            $action = '/';
        } else {
            $action = $this->modx->makeUrl($this->modx->documentObject['id']);
        }

        $this->modx->regClientHTMLBlock('<script>window.jQuery || document.write(\'<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js">\x3C/script>\');</script>');
        $this->modx->regClientStartupHTMLBlock('<link rel="stylesheet" href="/assets/modules/survey/assets/survey.css"/>');
        $this->modx->regClientHTMLBlock('<script src="/assets/modules/survey/assets/survey.js"></script>');
        $this->modx->regClientHTMLBlock('<script>var Survey = new Surveys("' . $action . '");</script>');

        $tpl = !empty($tpl) && is_string($tpl) ? $tpl : 'surveys';

        return $this->view($tpl, [
            'surveys' => $surveys,
            'options' => $options,
            'action' => $action
        ]);
    }

    public function handle()
    {
        if ($this->help->isAjax()) {
            echo $this->vote(
                isset($_GET['survey']) ? $_GET['survey'] : null,
                isset($_GET['option']) ? $_GET['option'] : null
            );
            die;
        }

        return $this->render();
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
            return $this->error($this->t('need_auth'));
        }

        if (!$survey_id = (int) $survey_id) {
            return $this->error($this->t('survey_id_error'));
        }

        if (!$option_id = (int) $option_id) {
            return $this->error($this->t('option_id_error'));
        }

        if (!$this->checkActiveSurvey($survey_id, 'id')) {
            return $this->error($this->t('survey_error'));
        }

        if ($this->checkForAnswered($survey_id, $user_id)) {
            return $this->error($this->t('already_answered'));
        }

        if (!$option = $this->getOption($option_id)) {
            return $this->error($this->t('option_error'));
        }

        if (!$answer_id = $this->addVote($survey_id, $option_id)) {
            return $this->error($this->t('add_vote_error'));
        }

        return $this->success($this->t('success_voted'), [
            'html' => $this->view('survey', [
                'survey' => $this->getActiveSurvey($survey_id, 'id, votes'),
                'options' => $this->getSurveyOptions($survey_id)
            ])
        ]);
    }

    public function resetSurvey($id)
    {
        if (!$survey = $this->checkSurvey($id, 'id, closed_at')) {
            return $this->error($this->t('not_exist'));
        }

        $this->db->delete($this->answers_tbl, "survey_id = {$id}");
        $this->db->update(['votes' => 0], $this->options_tbl, "survey_id = {$id}");
        $this->db->update([
            'votes' => 0,
            'updated_at' => $this->getTimestamp()
        ], $this->surveys_tbl, "id = {$id}");

        return $this->success($this->t('success_reset'));
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

        $id = $this->db->insert([
            'survey_id' => $survey_id,
            'user_id' => $user_id,
            'option_id' => $option_id,
            'created_at' => $this->getTimestamp(),
        ], $this->answers_tbl);

        if (!$id) {
            return null;
        }

        $this->db->update('votes = votes + 1', $this->surveys_tbl, 'id=' . $survey_id);
        $this->db->update('votes = votes + 1', $this->options_tbl, 'id=' . $option_id);

        return $id;
    }

    /**
     * Получение активного опроса по ID
     *
     * @param int $id ID пороса
     * @param string $select Список полей которые необходимо получить
     *
     * @return array|null
     */
    public function getActiveSurvey($id, $select = '*')
    {
        if (!$id = (int) $id) return null;

        $query = $this->db->select($select, $this->surveys_tbl, 'id=' . $id . ' and active=1 and closed_at IS NULL');

        $survey = $this->db->getRow($query);

        return $survey != false ? $survey : null;
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
        return $this->getActiveSurvey($id, 'id') ? true : false;
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
        return $this->getSurvey($id, 'id') ? true : false;
    }

    /**
     * Получение опроса по ID
     *
     * @param int $id
     * @param string $select
     *
     * @return array|null
     */
    public function getSurvey($id, $select = '*')
    {
        if (!$id = (int) $id) return null;

        $query = $this->db->select($select, $this->surveys_tbl, 'id=' . $id);

        $survey = $this->db->getRow($query);

        return $survey != false ? $survey : null;
    }

    /**
     * Получение варианта ответа на опрос по ID варианта
     *
     * @param int $id
     * @param string $select
     *
     * @return array|null
     */
    public function getOption($id, $select = '*')
    {
        if (!$id = (int) $id) return null;

        $query = $this->db->select($select, $this->options_tbl, 'id=' . $id);

        $option = $this->db->getRow($query);

        return $option != false ? $option : null;
    }

    /**
     * @param int $survey_id
     * @param int $user_id
     * @param string $select
     * @return mixed|null
     */
    public function getAnswerBySurveyAndUserId($survey_id, $user_id, $select = '*')
    {
        if (!$survey_id = (int) $survey_id) return null;
        if (!$user_id = (int) $user_id) return null;

        $query = $this->db->select($select, $this->answers_tbl, 'survey_id=' . $survey_id . ' and user_id=' . $user_id);

        $answer = $this->db->getRow($query);

        return $answer != false ? $answer : null;
    }

    /**
     * Проверка голосовал пользователь за опрос или нет
     *
     * @param int $survey_id ID опроса
     * @param int $user_id ID пользователя
     *
     * @return bool
     */
    protected function checkForAnswered($survey_id, $user_id)
    {
        if (!$survey_id = (int) $survey_id) return null;
        if (!$user_id = (int) $user_id) return null;

        $query = $this->db->query('SELECT id from ' . $this->answers_tbl . ' WHERE survey_id=' . $survey_id . ' and user_id=' . $user_id . ' LIMIT 1');

        return $this->db->getRecordCount($query) > 0 ? true : false;
    }

    /**
     * Получение всех опросов
     *
     * @return array
     */
    public function getAllSurveys()
    {
        $query = $this->db->select('*', $this->surveys_tbl);

        $surveys = [];
        while ($row = $this->db->getRow($query)) {
            $surveys[] = $row;
        }

        return $surveys;
    }

    /**
     * Получение активных опросов
     *
     * @param int $limit Кол-во поросов для получения
     * @param bool $random
     *
     * @return array
     */
    protected function getAllActiveSurveys($limit = 4, $random = false)
    {
        $order = $random ? 'RAND()' : 'created_at DESC';
        $query = $this->db->select('*', $this->surveys_tbl, 'active=1 and closed_at IS NULL', $order, (int) $limit);

        $surveys = [];
        while ($survey = $this->db->getRow($query)) {
            $surveys[$survey['id']] = $survey;
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
    public function getSurveyOptions($ids = [])
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $query = $this->db->select('*', $this->options_tbl, 'survey_id IN (' . implode(',', $ids) . ')', 'sort ASC');

        $options = [];
        if (count($ids) === 1) {
            while ($row = $this->db->getRow($query)) {
                $options[] = $row;
            }
        } else {
            while ($row = $this->db->getRow($query)) {
                if (!isset($options[$row['survey_id']])) {
                    $options[$row['survey_id']] = [];
                }
                $options[$row['survey_id']][] = $row;
            }
        }

        return $options;
    }

    /**
     * Создание опроса
     *
     * @param array $data
     *
     * @return string
     */
    public function createSurvey($data = [])
    {
        $this->help->cleanArray($data);

        $errors = [];
        if (empty($data['title'])) {
            $errors[] = 'Введите название опроса';
        }

        if (empty($data['new_option']) || !is_array($data['new_option']) || count($data['new_option']) < 2) {
            $errors[] = 'Создайте хотя бы 2 варинта ответа';
        }

        if ($errors) {
            return $this->error('Исправьте ошибки и повторите попытку', $errors);
        }

        $id = $this->db->insert([
            'title' => $data['title'],
            'description' => !empty($data['description']) ? $data['description'] : null,
            'active' => isset($data['active']) ? 1 : 0,
            'created_at' => $this->getTimestamp(),
            'updated_at' => $this->getTimestamp()
        ], $this->surveys_tbl);

        foreach ($data['new_option'] as $k => $o) {
            $this->db->insert([
                'title' => $o,
                'sort' => isset($data['new_option_sort'][$k]) ? (int) $data['new_option_sort'][$k] : $k,
                'survey_id' => $id
            ], $this->options_tbl);
        }

        return $this->success('Опрос успешно создан');
    }

    /**
     * Обновление опроса
     *
     * @param array $data
     *
     * @return string
     */
    public function updateSurvey($data = [])
    {
        $this->help->cleanArray($data);

        $errors = [];

        if (empty($data['survey']) || !$id = (int) $data['survey']) {
            $errors[] = 'Не корректный ID опроса';
        }

        if (empty($data['title'])) {
            $errors[] = 'Введите название опроса';
        }

        $options = [];
        if (isset($data['option']) && is_array($data['option'])) {
            $options = $data['option'];
        }

        $new_options = [];
        if (isset($data['new_option']) && is_array($data['new_option'])) {
            $new_options = $data['new_option'];
        }

        if (count($options) + count($new_options) < 2) {
            $errors[] = 'Опрос должен содержать не менее 2-х варинтов ответов';
        }

        if ($errors) {
            return $this->error('Исправьте ошибки и повторите попытку', $errors);
        }

        if (!$survey = $this->getSurvey($id)) {
            return $this->error($this->t('not_exist'));
        }

        $this->db->update([
            'title' => $data['title'],
            'description' => $data['description'],
            'active' => isset($data['active']) ? 1 : 0,
            'updated_at' => $this->getTimestamp(),
        ], $this->surveys_tbl, "id={$id}");

        $options = $this->getSurveyOptions($id);

        foreach ($options as $o) {
            $oid = 'id_' . $o['id'];
            if (isset($data['option'][$oid])) {
                $this->db->update([
                    'title' => $data['option'][$oid],
                    'sort' => isset($data['option_sort'][$oid]) ? $data['option_sort'][$oid] : $o['sort']
                ], $this->options_tbl, "id={$o['id']}");
            } else {
                $this->db->delete($this->options_tbl, "id={$o['id']}");
                $this->db->delete($this->answers_tbl, "option_id={$o['id']}");
                if ($o['votes'] > 0) {
                    $this->db->update("votes = votes - {$o['votes']}", $this->surveys_tbl, "id={$id}");
                }
            }
        }

        if (isset($data['new_option']) && count($data['new_option'])) {
            foreach ($data['new_option'] as $k => $o) {
                $this->db->insert([
                    'title' => $o,
                    'sort' => isset($data['new_option_sort'][$k]) ? (int) $data['new_option_sort'][$k] : $k,
                    'survey_id' => $id
                ], $this->options_tbl);
            }
        }

        return $this->success($this->t('success_updated'));
    }

    /**
     * Закрытие опроса по ID
     *
     * @param int $id
     *
     * @return string
     */
    public function closeSurvey($id)
    {
        if (!$survey = $this->getSurvey($id, 'id, active, closed_at')) {
            return $this->error($this->t('not_exist'));
        }

        $this->db->update(
            'active = ' . ( $survey['active'] == 0 ? 1 : 0 ) .
            ',closed_at = ' . ( $survey['closed_at'] ? 'NULL' : "'{$this->getTimestamp()}'" ) .
            ',updated_at = ' . ( "'{$this->getTimestamp()}'" )
        , $this->surveys_tbl, "id={$id}");

        return $this->success(
            $this->t($survey['closed_at'] ? 'success_opened' : 'success_closed')
        );
    }

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
        if (!$survey = $this->getSurvey((int) $id, 'id')) {
            return $this->error($this->t('not_exist'));
        }

        $this->db->delete($this->surveys_tbl, "id={$id}");
        $this->db->delete($this->options_tbl, "survey_id={$id}");
        $this->db->delete($this->answers_tbl, "survey_id={$id}");

        return $this->success($this->t('success_deleted'));
    }

    /**
     * Получение информации об опросе для менеджера по ID
     *
     * @param int $id
     *
     * @return string
     */
    public function getSurveyInfo($id)
    {
        // Только администраторам, членам группы ADMINS_GROUP
        if (!$this->isAdmin()) {
            return $this->error($this->t('not_allowed'));
        }

        if (!intval($id)) {
            return $this->error('Error');
        }

        $survey = $this->getSurvey($id);
        $options = $this->getSurveyOptions($id);
        $users = $this->getSurveyUsersById($id);

        return $this->success('', [
            'html' => $this->view('info', compact('survey', 'options', 'users'))
        ]);
    }

    /**
     * Получение всех пользователей голосовавших за опрос
     *
     * @param $id
     *
     * @return array
     */
    protected function getSurveyUsersById($id)
    {
        $users = [];

        $query = $this->db->query("SELECT a.user_id, a.option_id, u.fullname as name FROM {$this->answers_tbl} as a LEFT JOIN {$this->modx->getFullTableName('web_user_attributes')} as u ON u.internalKey = a.user_id WHERE a.survey_id = {$id}");

        while ($row = $this->db->getRow($query)) {
            if (!isset($users[$row['option_id']])) {
                $users[$row['option_id']] = [];
            }

            $users[$row['option_id']][$row['user_id']] = $row['name'];
        }

        return $users;
    }

    public function isAdmin()
    {
        if (isset($this->isAdmin)) {
            return $this->isAdmin;
        }

        $this->isAdmin = $this->help->isMemberOfGroup(self::ADMINS_GROUP);

        return $this->isAdmin;
    }

    public function getUserId()
    {
        return $this->help->getUserId();
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
     * @param null $tpl
     * @param array $data
     *
     * @return string|View
     */
    public function view($tpl = null, $data = [])
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

    public function calculateRate($survetVotes, $optionVotes)
    {
        return $survetVotes > 0 ? floor((100 / $survetVotes) * $optionVotes) : 0;
    }

    public function isInstalled()
    {
        return file_exists($this->config['path'] . 'setup.sql') ? false : true;
    }

    public function install()
    {
        $tbl_prefix = $this->db->config['table_prefix'];

        $sqlfile = $this->config['path'] . 'setup.sql';

        if (!file_exists($sqlfile) || !is_readable($sqlfile)) {
            return 'Не найден установочный файл setup.sql или он недоступен для чтения';
        }

        $sql = str_replace('{prefix}', $tbl_prefix, file_get_contents($sqlfile));

        $matches = [];
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
}
