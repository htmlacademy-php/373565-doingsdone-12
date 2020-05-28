<?php
require_once 'util.php';

session_start();

/*функция, проверяющая, осталось ли до выполнения задачи менее суток*/
function isDateDiffLess($date)
{
    $cur_date = time();
    $task_date = strtotime($date);
    $diff = floor(($task_date - $cur_date) / 3600);

    return $diff <= 24;
}

/*функция, возвращающая url*/
function getUrl ($file_path)
{
    return str_replace($_SERVER['DOCUMENT_ROOT'], 'http://'.$_SERVER['HTTP_HOST'], $file_path);
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
$project_id = null;
$show_complete_tasks = rand(0, 1);
if(isset($_SESSION['user'])) {
    $user_id = $_SESSION['user'];
    $projects = getProjects($con, $user_id);
    $tasksAll = array_reverse(getTasksAll($con, $user_id));
    $user_name = getUserName($con, $user_id);
}

/*проверка выбранного id проекта в адресной строке*/
if (isset($_SESSION['user']) && isset($_GET['project_id'])) {
    $project_id = $_GET['project_id'];
    if (!isValueInArray($projects, 'id', $project_id)) {
        http_response_code(404);
        exit();
    }
}

/*формирование массива задач для конкретного пользователя и проекта*/
if (isset($_SESSION['user'])) {
    $tasks = array_reverse(getTasks($con, $user_id, $project_id));
}

/*подключение шаблона*/
if (isset($_SESSION['user'])) {
    $main_content = include_template('main.php', ['show_complete_tasks' => $show_complete_tasks, 'projects' => $projects, 'tasks' => $tasks, 'tasksAll' => $tasksAll]);

    $layout_content = include_template('layout.php', ['content' => $main_content, 'title' => 'Дела в порядке', 'user_name' => $user_name]);
} else {
    $guest_content = include_template('guest.php');

    $layout_content = include_template('layout.php', ['content' => $guest_content, 'title' => 'Дела в порядке']);
}

print($layout_content);
