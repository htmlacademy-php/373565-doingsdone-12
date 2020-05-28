<?php
require_once 'util.php';

$errors = getErrors($con);

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

    if (empty(getUser($con, $email))) {
        return 'Неверный email';
    }
}

/*функция для валидации пароля*/
function validatePassword ($con, $name)
{
    $password = getPostVal($name);

    if (empty($password)) {
        return 'Это поле должно быть заполнено';
    }

    $user = getUser($con, getPostVal('email'));

    if (!empty($user) && !password_verify($password, $user['password'])) {
        return 'Неверный пароль';
    }
}

/*функция, возвращающая массив ошибок*/
function getErrors ($con)
{
    $errors = [];

    $rules = [
        'email' => function ($con) {
            return validateEmail($con, 'email');
        },

        'password' => function ($con) {
            return validatePassword($con, 'password');
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

/*функция, формирующая сообщение об ошибках*/
function getErrorMessage ($errors)
{
    if (getValue($errors, 'email') == 'Неверный email' || getValue($errors, 'password') == 'Неверный пароль') {
        return 'Вы ввели неверный email/пароль';
    } else {
        return 'Пожалуйста, исправьте ошибки в форме';
    }
}

/*функция для обработки формы аутентификации*/
function processingFormAuth ($con, $errors)
{
    $email = getPostVal('email');

    if (!count($errors)) {
        session_start();
        $_SESSION['user'] = getUser($con, $email)['id'];
        header('Location: index.php');
    }
}

/*проверка отправки формы*/
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    processingFormAuth($con, $errors);
}

/*подключение шаблона*/
$auth_content = include_template('auth.php', ['errors' => $errors]);

$layout_content = include_template('layout.php', ['content' => $auth_content, 'title' => 'Дела в порядке']);

print($layout_content);
