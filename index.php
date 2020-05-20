<?php
require_once 'helpers.php';
require_once 'db.php';

$con = mysqli_connect($params['host'], $params['user'], $params['password'], $params['db_name']);

mysqli_set_charset($con, 'utf8');

if (!$con) {
    die('Ошибка подключения: ' . mysqli_connect_error());
}

/*функция, возвращающая количество задач в проекте*/
function countProjectTasks(array $task_list, $project_id)
{
    $count = 0;
    foreach ($task_list as $task) {
        if (isset($task['project_id']) && $task['project_id'] === $project_id) {
            $count++;
        }
    }
    return $count;
}

/*функция, проверяющая, осталось ли до выполнения задачи менее суток*/
function isDateDiffLess($date)
{
    $cur_date = time();
    $task_date = strtotime($date);
    $diff = floor(($task_date - $cur_date) / 3600);

    return $diff <= 24;
}

/*функция, проверяющая наличие значения в массиве по ключу*/
function isValueInArray($array, $key, $value)
{
    foreach ($array as $val) {
        if (isset($val[$key]) && $val[$key] == $value) {
            return true;
        }
    }
    return false;
}

/*функция, возвращающая значение массива по ключу при его наличии*/
function getValue ($array, $key)
{
    if (isset($array[$key])) {
        return $array[$key];
    }
}

/*функция, возвращающая url*/
function getUrl ($file_path)
{
    return str_replace($_SERVER['DOCUMENT_ROOT'], 'http://'.$_SERVER['HTTP_HOST'], $file_path);
}


/*функция, возвращающая массив проектов для конкретного пользователя*/
function getProjects($con, int $user_id)
{
    $sql = 'SELECT * FROM projects WHERE user_id = ?';
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $projects = mysqli_fetch_all($res, MYSQLI_ASSOC);

    return $projects;
}

/*функция, возвращающая массив всех задач для конкретного пользователя*/
function getTasksAll($con, int $user_id)
{
    $sql = 'SELECT * FROM tasks WHERE user_id = ?';
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $tasksAll = mysqli_fetch_all($res, MYSQLI_ASSOC);

    return $tasksAll;
}

/*функция, возвращающая массив задач для конкретного пользователя и проекта*/
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

/*объявление переменных*/
$user_id = 1;
$project_id = null;
$show_complete_tasks = rand(0, 1);
$projects = getProjects($con, $user_id);

/*проверка выбранного id проекта в адресной строке*/
if (isset($_GET['project_id'])) {
    $project_id = $_GET['project_id'];
    if (!isValueInArray($projects, 'id', $project_id)) {
        http_response_code(404);
        exit();
    }
}

/*формирование массивов задач*/
$tasks = array_reverse(getTasks($con, $user_id, $project_id));
$tasksAll = array_reverse(getTasksAll($con, $user_id));

/*подключение шаблона*/
if ($_SERVER['PHP_SELF'] == '/index.php') {
    $main_content = include_template('main.php', ['show_complete_tasks' => $show_complete_tasks, 'projects' => $projects, 'tasks' => $tasks, 'tasksAll' => $tasksAll]);
    $layout_content = include_template('layout.php', ['content' => $main_content, 'title' => 'Дела в порядке', 'user_name' => 'Константин']);
    print($layout_content);
}

