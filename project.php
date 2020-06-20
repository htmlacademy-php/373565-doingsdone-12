<?php
require_once 'util.php';

session_start();

/*объявление переменных*/
if(isset($_SESSION['user'])) {
    $user_id = $_SESSION['user'];
    $projects = getProjects($con, $user_id);
    $tasksAll = array_reverse(getTasksAll($con, $user_id));
    $user_name = getUserName($con, $user_id);
    $errors = getErrors($projects);
}

/*функция для добавления проекта в БД*/
function addProject ($con, int $user_id, string $project_name) {

    $parameters = [$user_id, $project_name];
    $sql = 'INSERT INTO projects (user_id, name) VALUES (?, ?)';

    $stmt = db_get_prepare_stmt($con, $sql, $parameters);
    mysqli_stmt_execute($stmt);
}

function validateName ($projects)
{
    $name = trim(getPostVal('name'));

    if (empty($name)) {
        return 'Это поле должно быть заполнено';
    }

    if (isValueInArray($projects, 'name', $name)) {
        return 'Проект с таким названием уже существует';
    }

    return "";
}

/*функция, возвращающая массив ошибок*/
function getErrors ($projects)
{
    $errors = [];

    $rules = [
        'name' => function ($projects) {
            return validateName($projects);
        }
    ];

    foreach ($_POST as $key => $value) {

        if (isset($rules[$key])) {
            $rule = $rules[$key];
            $errors[$key] = $rule($projects);
        }
    }

    return array_filter($errors);
}

/*функция для обработки формы добавления проекта*/
function processingFormAddProject ($con, $user_id, $errors)
{
    $project_name = getPostVal('name');

    if (!count($errors)) {
        addProject($con, $user_id, $project_name);
        header('Location: index.php');
    }
}

/*проверка отправки формы*/
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    processingFormAddProject($con, $user_id, $errors);
}

/*подключение шаблона*/
if (isset($_SESSION['user'])) {
    $project_content = include_template('project.php', ['projects' => $projects, 'tasksAll' => $tasksAll, 'errors' => $errors]);

    $layout_content = include_template('layout.php', ['content' => $project_content, 'title' => 'Дела в порядке', 'user_name' => $user_name]);

    print($layout_content);
} else {
    header('Location: index.php');
    exit();
}
