<?php
require_once 'helpers.php';

$show_complete_tasks = rand(0, 1);
$projects = ['Входящие', 'Учеба', 'Работа', 'Домашние дела', 'Авто'];
$tasks = [
[
'title' => 'Собеседование в IT компании',
'date' => '01.12.2019',
'project' => 'Работа',
'status' => false
],
[
'title' => 'Выполнить тестовое задание',
'date' => '25.12.2019',
'project' => 'Работа',
'status' => false
],
[
'title' => 'Сделать задание первого раздела',
'date' => '21.12.2019',
'project' => 'Учеба',
'status' => true
],
[
'title' => 'Встреча с другом',
'date' => '22.12.2019',
'project' => 'Входящие',
'status' => false
],
[
'title' => 'Купить корм для кота',
'date' => null,
'project' => 'Домашние дела',
'status' => false
],
[
'title' => 'Заказать пиццу',
'date' => null,
'project' => 'Домашние дела',
'status' => false
]
];

function countProjectTasks(array $task_list, $project_name)
{
    $count = 0;
    foreach ($task_list as $task) {
        if (isset($task['project']) && $task['project'] === $project_name) {
            $count++;
        }
    }
    return $count;
}

function isDateDiffLess ($date)
{
    $cur_date = strtotime(date('d.m.Y H:i:s'));
    $task_date = strtotime($date);
    $diff = floor(($task_date - $cur_date) / 3600);

  return $diff <= 24;
}

$main_content = include_template('main.php', ['projects' => $projects, 'tasks' => $tasks, 'show_complete_tasks' => $show_complete_tasks]);

$layout_content = include_template('layout.php', ['content' => $main_content, 'title' => 'Дела в порядке', 'user_name' => 'Константин']);

print($layout_content);
