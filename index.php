<?php
require_once 'util.php';

session_start();

/**
 * функция, проверяющая, осталось ли до выполнения задачи менее суток
 * @param string $date дата выполнения задачи
 *
 * @return integer разница между датами в часах
 */
function isDateDiffLess($date)
{
    $cur_date = time();
    $task_date = strtotime($date);
    $diff = floor(($task_date - $cur_date) / 3600);

    return $diff <= 24;
}

/**
 * функция, возвращающая url
 * @param string $file_path путь к файлу
 *
 * @return string url файла
 */
function getUrl($file_path)
{
    return str_replace($_SERVER['DOCUMENT_ROOT'], 'http://' . $_SERVER['HTTP_HOST'], $file_path);
}

/**
 * функция, возвращающая массив задач для конкретного пользователя и проекта
 * @param resource $con ресурс соединения
 * @param integer $user_id идентификатор пользователя
 * @param integer $project_id идентификатор проекта
 *
 * @return array массив задач для конкретного пользователя и проекта
 */
function getTasks($con, int $user_id, int $project_id = null)
{
    $parameters = [];
    $sql = 'SELECT * FROM tasks WHERE user_id = ?';
    $parameters[] = $user_id;
    if (!is_null($project_id)) {
        $sql .= " and project_id = ?";
        $parameters[] = $project_id;
    }

    $stmt = db_get_prepare_stmt($con, $sql, $parameters);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $tasks = mysqli_fetch_all($res, MYSQLI_ASSOC);

    return $tasks;
}

/**
 * функция, возвращающая массив задач из строки поиска
 * @param resource $con ресурс соединения
 * @param string $search текст строки поиска
 * @param integer $user_id идентификатор пользователя
 *
 * @return array массив задач из строки поиска
 */
function getSearchTasks($con, $search, int $user_id)
{
    $sql = 'SELECT * FROM tasks WHERE user_id = ? AND MATCH(name) AGAINST (? IN BOOLEAN MODE)';

    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'is', $user_id, $search);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    $tasks = mysqli_fetch_all($res, MYSQLI_ASSOC);

    return $tasks;
}

/**
 * функция, возвращающая задачу по идентификатору
 * @param resource $con ресурс соединения
 * @param integer $task_id идентификатор задачи
 *
 * @return array массив задач по идентификатору
 */
function getTaskWhereId($con, int $task_id)
{
    $sql = 'SELECT * FROM tasks WHERE id = ?';
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $task_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $tasks = mysqli_fetch_all($res, MYSQLI_ASSOC);
    $task = null;

    foreach ($tasks as $value) {
        $task = $value;
    }

    return $task;
}

/**
 * функция, инвертирующая статус задачи
 * @param resource $con ресурс соединения
 * @param array $task массив задач
 */
function changeStatus($con, $task)
{
    $status = 1 - getValue($task, 'status');

    $parameters = [$status, getValue($task, 'id')];
    $sql = 'UPDATE tasks SET status = ? WHERE id = ?';

    $stmt = db_get_prepare_stmt($con, $sql, $parameters);
    mysqli_stmt_execute($stmt);
}

/**
 * функция для добавления параметра к строке запроса
 * @param string $name_params имя параметра из массива $_GET
 * @param string $value_params значение параметра
 *
 * @return string url с новым параметром
 */
function getNewURL($name_params, $value_params)
{
    $params = $_GET;
    $params[$name_params] = $value_params;

    return pathinfo(__FILE__, PATHINFO_BASENAME) . '?' . http_build_query($params);
}

/**
 * функция, возвращающая массив задач на сегодня
 * @param array $tasks массив задач
 *
 * @return array массив задач на сегодня
 */
function getTasksToday($tasks)
{
    $tasks_new = [];
    $cur_date = time();
    foreach ($tasks as $task) {
        $task_date = strtotime(getValue($task, 'due_date'));

        if ($task_date != 0) {
            $diff = floor(($cur_date - $task_date) / 3600);
            if ($diff < 24 && $diff > 0) {
                $tasks_new[] = $task;
            }
        }
    }
    return $tasks_new;
}

/**
 * функция, возвращающая массив задач на завтра
 * @param array $tasks массив задач
 *
 * @return array массив задач на завтра
 */
function getTaskTomorrow($tasks)
{
    $tasks_new = [];
    $cur_date = time();
    foreach ($tasks as $task) {
        $task_date = strtotime(getValue($task, 'due_date'));

        if ($task_date != 0) {
            $diff = floor(($task_date - $cur_date) / 3600);
            if ($diff < 24 && $diff > 0) {
                $tasks_new[] = $task;
            }
        }
    }
    return $tasks_new;
}

/**
 * функция, возвращающая массив просроченных задач
 * @param array $tasks массив задач
 *
 * @return array массив просроченных задач
 */
function getTaskOverdue($tasks)
{
    $tasks_new = [];
    $cur_date = time();
    foreach ($tasks as $task) {
        $task_date = strtotime(getValue($task, 'due_date'));
        if ((int)$task_date !== 0 && getValue($task, 'status') !== 1) {
            $diff = floor(($cur_date - $task_date) / 3600);
            if ($diff >= 24) {
                $tasks_new[] = $task;
            }
        }
    }
    return $tasks_new;
}

/*объявление переменных*/
$project_id = null;
if (isset($_SESSION['user'])) {
    $user_id = $_SESSION['user'];
    $projects = getProjects($con, $user_id);
    $tasksAll = array_reverse(getTasksAll($con, $user_id));
    $user_name = getUserName($con, $user_id);
    $tasks_filter = ['Все задачи', 'Повестка дня', 'Завтра', 'Просроченные'];
}

/*проверка выбранного id проекта в адресной строке*/
if (isset($_SESSION['user']) && isset($_GET['project_id'])) {
    $project_id = (int)$_GET['project_id'];
    if (!isValueInArray($projects, 'id', $project_id)) {
        http_response_code(404);
        exit();
    }
}

/*формирование массива задач для конкретного пользователя и проекта*/
if (isset($_SESSION['user'])) {
    $tasks = array_reverse(getTasks($con, $user_id, $project_id));
}

/*проверка наличия запроса на инвертирование статуса задачи*/
if (isset($_GET['task_completed'])) {
    $task = getTaskWhereId($con, $_GET['task_completed']);
    changeStatus($con, $task);
    header('Location: index.php');
}

/*проверка выбора фильтра задач*/
if (isset($_GET['filter'])) {
    switch ($_GET['filter']) {
        case 1:
            $tasks = getTasksToday($tasks);
            break;
        case 2:
            $tasks = getTaskTomorrow($tasks);
            break;
        case 3:
            $tasks = getTaskOverdue($tasks);
            break;
        default:
            break;
    }
}

/*проверка отправки запроса поиска задач*/
if (isset($_GET['search'])) {
    $search = trim(filter_input(INPUT_GET, 'search', FILTER_SANITIZE_SPECIAL_CHARS));

    if (!empty($search)) {
        $tasks = getSearchTasks($con, $search, $user_id);
    }
}

/*подключение шаблона*/
if (isset($_SESSION['user'])) {
    $main_content = include_template('main.php',
        ['projects' => $projects, 'tasks' => $tasks, 'tasksAll' => $tasksAll, 'tasks_filter' => $tasks_filter]);
    $layout_content = include_template('layout.php',
        ['content' => $main_content, 'title' => 'Дела в порядке', 'user_name' => $user_name]);
} else {
    $guest_content = include_template('guest.php');

    $layout_content = include_template('layout.php', ['content' => $guest_content, 'title' => 'Дела в порядке']);
}

print($layout_content);
