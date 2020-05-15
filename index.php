<?php
require_once 'helpers.php';

$con = mysqli_connect("localhost", "root", "", "doingsdone");
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

/*функция для добавления задачи в БД*/
function addTask ($con, int $user_id, string $task_name, int $project_id, string $due_date, string $file_path) {

    $parameters = [$user_id, $task_name, $project_id];
    $sql = 'INSERT INTO tasks (user_id, name, project_id';

    if (!empty($due_date)) {
        $parameters[] = $due_date;
        $sql .= ', due_date';
    }
    if (!empty($file_path)) {
        $parameters[] = $file_path;
        $sql .= ', file_path';
    }

    $sql .= ') VALUES (';
    for ($i = 0; $i < count($parameters); $i++) {
        $sql .= '?, ';
    }
    $sql = substr($sql, 0, -2).')';

    $stmt = db_get_prepare_stmt($con, $sql, $parameters);
    return mysqli_stmt_execute($stmt);
}

/*функция, возвращающая url*/
function getUrl ($file_path)
{
    return str_replace($_SERVER['DOCUMENT_ROOT'], 'http:\\\\'.$_SERVER['HTTP_HOST'], $file_path);
}

/*функция, возвращающая значение поля формы*/
function getPostVal($name)
{
    return $_POST[$name] ?? '';
}

/*функция, возвращающая имя файла из формы*/
function getFilesVal($name)
{
    if (isset ($_FILES[$name])) {
        return $_FILES[$name]['name'] ?? '';
    }
}

/*функция, возвращающая значение массива по ключу при его наличии*/
function getValue ($array, $key)
{
    if (isset($array[$key])) {
        return $array[$key];
    }
}

/*функция для проверки заполненности поля формы*/
function validateFilled($name)
{
    if (empty($_POST[$name])) {
        return 'Это поле должно быть заполнено';
    }
}

/*функция для валидации проекта*/
function validateRealProject($projects)
{
    if (!isValueInArray($projects, 'id', $_POST['project'])) {
        return 'Проект должен быть реально существующим';
    }
}

/*функция для валидации даты*/
function validateDate()
{
    $date = $_POST['date'];
    if (!empty($date)) {
        if (is_date_valid($date)) {
            $cur_date = time();
            $task_date = strtotime($date);
            if (floor(($cur_date - $task_date) / 3600) >= 24) {
                return 'Дата должна быть больше или равна текущей';
            }
        } else {
            return 'Дата должна быть в формате ГГГГ-ММ-ДД';
        }
    }
}

/*функция, возвращающая класс для поля с ошибкой*/
function getClassError ($errors, $name)
{
    if (isset($errors[$name])){
        return 'form__input--error';
    }
    return '';
}

/*функция для добавления атрибута выбранному селекту*/
function getSelected ($name, $id)
{
   if(isset($_POST[$name]) && getPostVal($name) == $id) {
        return 'selected';
    }
}

/*функция, возвращающая массив ошибок*/
function getErrors ($projects)
{
    $errors = [];

    $rules = [
        'name' => function () {
            return validateFilled('name');
        },

        'project' => function($projects) {
            return validateRealProject($projects);
        },

        'date' => function() {
            return validateDate();
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

/*функция для обработки формы добавления задачи*/
function processingFormAddTask ($con, $user_id, $errors)
{
    $task_name = getPostVal('name');
    $project_id = getPostVal('project');
    $due_date = getPostVal('date');
    $file_name = getFilesVal('file');
    $file_path = '';

    if (!count($errors)) {
        if (!empty($file_name) && isset($_FILES['file'])) {
            move_uploaded_file($_FILES['file']['tmp_name'], $_SERVER['DOCUMENT_ROOT'].'\\'.$file_name);
            $file_path = $_SERVER['DOCUMENT_ROOT'].'\\'.$file_name;
        }
        addTask($con, $user_id, $task_name, $project_id, $due_date, $file_path);
        header('Location: index.php');
    }
}

/*объявление переменных*/
$user_id = 1;
$project_id = null;
$show_complete_tasks = rand(0, 1);
$projects = getProjects($con, $user_id);
$errors = getErrors($projects);

/*проверка выбранного id проекта в адресной строке*/
if (isset($_GET['project_id'])) {
    $project_id = $_GET['project_id'];
    if (!isValueInArray($projects, 'id', $project_id)) {
        http_response_code(404);
        exit();
    }
}

/*формирование массивов задач*/
$tasks = getTasks($con, $user_id, $project_id);
$tasksAll = getTasksAll($con, $user_id);


/*шаблоны*/
$add_content = include_template('add.php', ['projects' => $projects, 'tasksAll' => $tasksAll, 'con' => $con, 'user_id' => $user_id, 'errors' => $errors]);

$main_content = include_template('main.php', ['show_complete_tasks' => $show_complete_tasks, 'projects' => $projects, 'tasks' => $tasks, 'tasksAll' => $tasksAll]);

$layout_content = include_template('layout.php', ['content' => $main_content, 'title' => 'Дела в порядке', 'user_name' => 'Константин']);

if (isset($_GET['add'])) {
    $layout_content = include_template('layout.php', ['content' => $add_content, 'title' => 'Дела в порядке', 'user_name' => 'Константин']);
}

print($layout_content);
