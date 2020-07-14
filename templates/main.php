<section class="content__side">
    <h2 class="content__side-heading">Проекты</h2>

    <nav class="main-navigation">
        <ul class="main-navigation__list">
            <?php foreach ($projects as $project): ?>
                <li class="main-navigation__list-item <?php if ((int)getValue($_GET,
                        'project_id') === $project['id']): ?> main-navigation__list-item--active<?php endif; ?>">
                    <a class="main-navigation__list-item-link"
                       href="index.php?project_id=<?= $project['id'] ?>"><?= htmlspecialchars($project['name']); ?></a>
                    <span class="main-navigation__list-item-count"><?= countProjectTasks($tasksAll,
                            $project['id']); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <a class="button button--transparent button--plus content__side-button"
       href="project.php" target="project_add">Добавить проект</a>
</section>

<main class="content__main">
    <h2 class="content__main-heading">Список задач</h2>

    <form class="search-form" action="index.php" method="get" autocomplete="off">
        <input class="search-form__input" type="text" name="search"
               value="<?= trim(filter_input(INPUT_GET, 'search', FILTER_SANITIZE_SPECIAL_CHARS)) ?>"
               placeholder="Поиск по задачам">

        <input class="search-form__submit" type="submit" name="" value="Искать">
    </form>

    <div class="tasks-controls">
        <nav class="tasks-switch">
            <?php for ($index = 0; $index < count($tasks_filter); $index++): ?>
                <a href="<?= getNewURL('filter', $index) ?>"
                   class="tasks-switch__item <?php if (getValue($_GET,
                           'filter') == $index): ?> tasks-switch__item--active<?php endif; ?>"><?= $tasks_filter[$index]; ?></a>
            <?php endfor; ?>
        </nav>

        <label class="checkbox">
            <!--добавить сюда атрибут "checked", если переменная $show_complete_tasks равна единице-->
            <input class="checkbox__input visually-hidden show_completed" type="checkbox"
                   <?php if (getValue($_GET, 'show_completed') === '1'): ?>checked <?php endif; ?>>
            <span class="checkbox__text">Показывать выполненные</span>
        </label>
    </div>

    <table class="tasks">
        <?php foreach ($tasks as $task): ?>
            <?php if (isset($task['status'])): ?>
                <?php if (getValue($_GET, 'show_completed') == 0 && $task['status']): continue ?><?php endif; ?>
                <tr class="tasks__item task<?php if ($task['status']): ?> task--completed<?php endif ?><?php if (isset($task['due_date']) && isDateDiffLess($task['due_date']) && $task['status'] != 1): ?> task--important<?php endif; ?>">
            <?php endif; ?>
            <td class="task__select">
                <label class="checkbox task__checkbox">
                    <input class="checkbox__input visually-hidden task__checkbox" type="checkbox"
                           onchange="changeTask(this.value)"
                           value="<?= getValue($task, 'id'); ?>"<?php if (getValue($task,
                        'status')): ?> checked<?php endif; ?>>
                    <span class="checkbox__text"><?= htmlspecialchars(getValue($task, 'name')); ?></span>
                </label>
            </td>

            <td class="task__file">
                <?php if (isset($task['file_path'])): ?>
                    <a class="download-link"
                       href="<?= getUrl($task['file_path']); ?>"><?= htmlspecialchars(basename($task['file_path'])); ?></a>
                <?php endif; ?>
            </td>

            <td class="task__date"><?php if (isset($task['due_date'])): print(date('d.m.Y',
                    strtotime($task['due_date'])));
                else: print ("Нет"); ?><?php endif; ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!empty($_GET['search']) && empty($tasks)): ?>
            <p class="error-message">Ничего не найдено по вашему запросу</p>
        <?php endif; ?>
    </table>
</main>
