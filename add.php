<?php
require_once 'util.php';

$errors = getErrors($projects);

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
    mysqli_stmt_execute($stmt);
}

/*функция, возвращающая имя файла из формы*/
function getFilesVal($name)
{
    if (isset ($_FILES[$name])) {
        return $_FILES[$name]['name'] ?? '';
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

/*функция для проверки ошибки загрузки файла*/
function errorsFile ($name) {
    if (isset ($_FILES[$name]) && $_FILES[$name]['error'] > 0) {
        return 'Ошибка загрузки файла';
    }
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
        },

        'file' => function() {
            return errorsFile('file');
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
            if(move_uploaded_file($_FILES['file']['tmp_name'], $_SERVER['DOCUMENT_ROOT'].'/'.$file_name))
            {
                $file_path = $_SERVER['DOCUMENT_ROOT'].'/'.$file_name;
            }
        }
        addTask($con, $user_id, $task_name, $project_id, $due_date, $file_path);
        header('Location: index.php');
    }
}

/*проверка отправки формы*/
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    processingFormAddTask($con, $user_id, $errors);
}

/*подключение шаблона*/
$add_content = include_template('add.php', ['projects' => $projects, 'tasksAll' => $tasksAll, 'errors' => $errors]);

$layout_content = include_template('layout.php', ['content' => $add_content, 'title' => 'Дела в порядке', 'user_name' => 'Константин']);

print($layout_content);
