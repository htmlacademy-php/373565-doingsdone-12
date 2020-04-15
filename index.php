<?php
require_once 'helpers.php';

$show_complete_tasks = rand(0, 1);

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

function isParamRequest($param_name)
{
    return isset($_GET[$param_name]);
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


$main_content = include_template('main.php', ['show_complete_tasks' => $show_complete_tasks]);

$layout_content = include_template('layout.php', ['content' => $main_content, 'title' => 'Дела в порядке', 'user_name' => 'Константин']);

print($layout_content);
