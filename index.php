<?php
require_once 'helpers.php';

$con = mysqli_connect("localhost", "root", "", "doingsdone");
mysqli_set_charset($con, 'utf8');

if (!$con) {
    die('Ошибка подключения: ' . mysqli_connect_error());
}

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

function isDateDiffLess($date)
{
    $cur_date = time();
    $task_date = strtotime($date);
    $diff = floor(($task_date - $cur_date) / 3600);

  return $diff <= 24;
}

function isValueInArray($array, $key, $value)
{
    foreach ($array as $val) {
        if ($val[$key] == $value) {
            return true;
        }
    }
    return false;
}

function getProjects ($con, int $user_id)
{
    $sql = 'SELECT * FROM projects WHERE user_id = ?';
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $projects = mysqli_fetch_all($res, MYSQLI_ASSOC);

    return $projects;
}

function getTasksAll ($con, int $user_id)
{
    $sql = 'SELECT * FROM tasks WHERE user_id = ?';
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $tasksAll = mysqli_fetch_all($res, MYSQLI_ASSOC);

    return $tasksAll;
}

function getTasks ($con, int $user_id, int $project_id=null)
{
    $parameters = [];
    $sql = 'SELECT * FROM tasks WHERE user_id = ?';
    $parameters[] = $user_id;
    if (! is_null($project_id)) {
        $sql .= " and project_id = ?";
        $parameters[] = $project_id;
    }

    $stmt = db_get_prepare_stmt($con, $sql, $parameters);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $tasks = mysqli_fetch_all($res, MYSQLI_ASSOC);

    return $tasks;
}

$user_id = 1;
$project_id = null;
$show_complete_tasks = rand(0, 1);
$projects = getProjects($con, $user_id);

if (isset($_GET['project_id']))
{
    $project_id = $_GET['project_id'];
    if (!isValueInArray($projects, 'id', $project_id)) {
        http_response_code(404);
        exit();
    }
}

$tasks = getTasks($con, $user_id, $project_id);
$tasksAll = getTasksAll($con, $user_id);

$main_content = include_template('main.php', ['show_complete_tasks' => $show_complete_tasks, 'projects' => $projects, 'tasks' => $tasks, 'tasksAll' => $tasksAll]);

$layout_content = include_template('layout.php', ['content' => $main_content, 'title' => 'Дела в порядке', 'user_name' => 'Константин']);

print($layout_content);
