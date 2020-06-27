<?php
require_once 'util.php';

$errors = getErrors($con);

/*функция для добавления пользователя*/
function addUser($con, string $email, string $name, string $password)
{
    $parameters = [$email, $name, $password];
    $sql = 'INSERT INTO users (email, name, password) VALUES (?, ?, ?)';
    $stmt = db_get_prepare_stmt($con, $sql, $parameters);
    mysqli_stmt_execute($stmt);
}

/*функция для валидации email*/
function validateEmail($con, $name)
{
    $email = getPostVal($name);

    if (empty($email)) {
        return 'Это поле должно быть заполнено';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'E-mail введён некорректно';
    }

    if (!empty(getUser($con, $email))) {
        return 'E-mail уже используется другим пользователем';
    }

    return "";
}

/*функция, возвращающая массив ошибок*/
function getErrors($con)
{
    $errors = [];

    $rules = [
        'email' => function ($con) {
            return validateEmail($con, 'email');
        },

        'password' => function () {
            return validateFilled('password');
        },

        'name' => function () {
            return validateFilled('name');
        },
    ];

    foreach ($_POST as $key => $value) {

        if (isset($rules[$key])) {
            $rule = $rules[$key];
            $errors[$key] = $rule($con);
        }
    }

    return array_filter($errors);
}

/*функция для обработки формы регистрации*/
function processingFormRegister($con, $errors)
{
    $email = getPostVal('email');
    $password = getPostVal('password');
    $user_name = getPostVal('name');

    if (!count($errors)) {
        $password = password_hash($password, PASSWORD_DEFAULT);
        addUser($con, $email, $user_name, $password);
        session_start();
        $_SESSION['user'] = getUser($con, $email)['id'];
        header('Location: index.php');
    }
}

/*проверка отправки формы*/
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    processingFormRegister($con, $errors);
}

/*подключение шаблона*/
$register_content = include_template('register.php', ['errors' => $errors]);

$layout_content = include_template('layout.php', ['content' => $register_content, 'title' => 'Дела в порядке']);

print($layout_content);
