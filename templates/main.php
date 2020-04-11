<?php
    $con = mysqli_connect("localhost", "root", "", "doingsdone");

    if (!$con) {
        print('Ошибка подключения: ' . mysqli_connect_error());
    }
    else {
        $sql = 'SELECT * FROM projects WHERE user_id = ?';
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $_GET['id']);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $projects = mysqli_fetch_all($res, MYSQLI_ASSOC);

        $sql = 'SELECT * FROM tasks WHERE user_id = ?';
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $_GET['id']);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $tasks = mysqli_fetch_all($res, MYSQLI_ASSOC);
    }
?>

<div class="content">
    <section class="content__side">
        <h2 class="content__side-heading">Проекты</h2>

        <nav class="main-navigation">
            <ul class="main-navigation__list">
                <?php foreach ($projects as $project): ?>
                <li class="main-navigation__list-item">
                    <a class="main-navigation__list-item-link" href="#"><?=htmlspecialchars($project['name']); ?></a>
                    <span class="main-navigation__list-item-count"><?=countProjectTasks($tasks, $project['id']); ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </nav>
            <a class="button button--transparent button--plus content__side-button"
            href="pages/form-project.html" target="project_add">Добавить проект</a>
    </section>

    <main class="content__main">
        <h2 class="content__main-heading">Список задач</h2>

        <form class="search-form" action="index.php" method="post" autocomplete="off">
            <input class="search-form__input" type="text" name="" value="" placeholder="Поиск по задачам">

            <input class="search-form__submit" type="submit" name="" value="Искать">
        </form>

        <div class="tasks-controls">
            <nav class="tasks-switch">
                <a href="/" class="tasks-switch__item tasks-switch__item--active">Все задачи</a>
                <a href="/" class="tasks-switch__item">Повестка дня</a>
                <a href="/" class="tasks-switch__item">Завтра</a>
                <a href="/" class="tasks-switch__item">Просроченные</a>
            </nav>

            <label class="checkbox">
                <!--добавить сюда атрибут "checked", если переменная $show_complete_tasks равна единице-->
                <input class="checkbox__input visually-hidden show_completed" type="checkbox" <?php if ($show_complete_tasks === 1): ?>checked <?php endif; ?>>
                <span class="checkbox__text">Показывать выполненные</span>
            </label>
        </div>

        <table class="tasks">
            <?php foreach ($tasks as $task): ?>
                <?php if (isset($task['status'])): ?>
                    <?php if ($show_complete_tasks === 0 && $task['status']): continue ?><?php endif; ?>
                    <tr class="tasks__item task<?php if ($task['status']): ?> task--completed<?php endif ?><?php if (isset($task['due_date']) && isDateDiffLess($task['due_date'])): ?> task--important<?php endif; ?>">
                <?php endif; ?>
                <td class="task__select">
                    <label class="checkbox task__checkbox">
                        <input class="checkbox__input visually-hidden task__checkbox" type="checkbox" value="1">
                        <span class="checkbox__text"><?php if (isset($task['name'])): print(htmlspecialchars($task['name'])) ?><?php endif; ?></span>
                    </label>
                </td>

                <td class="task__file">
                    <a class="download-link" href="#">Home.psd</a>
                </td>

                <td class="task__date"><?php if (isset($task['due_date'])): print(date('d.m.Y', strtotime($task['due_date']))) ?><?php endif; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </main>
</div>
