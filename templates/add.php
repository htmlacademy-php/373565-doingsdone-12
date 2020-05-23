<div class="content">
    <section class="content__side">
        <h2 class="content__side-heading">Проекты</h2>

        <nav class="main-navigation">
            <ul class="main-navigation__list">
                <?php foreach ($projects as $project): ?>
                    <li class="main-navigation__list-item <?php if($_SERVER['REQUEST_URI'] == '/index.php?project_id=' . $project['id']): ?> main-navigation__list-item--active<?php endif; ?>">
                        <a class="main-navigation__list-item-link" href="index.php?project_id=<?=$project['id'] ?>"><?=htmlspecialchars($project['name']); ?></a>
                        <span class="main-navigation__list-item-count"><?=countProjectTasks($tasksAll, $project['id']); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <a class="button button--transparent button--plus content__side-button" href="form-project.html">Добавить проект</a>
    </section>

    <main class="content__main">
        <h2 class="content__main-heading">Добавление задачи</h2>

        <form class="form"  action="add.php" method="post" autocomplete="off" enctype="multipart/form-data">
            <div class="form__row">
                <label class="form__label" for="name">Название <sup>*</sup></label>

                <input class="form__input <?=getClassError($errors, 'name'); ?>" type="text" name="name" id="name" value="<?=getPostVal('name'); ?>" placeholder="Введите название">

                <?php if(isset($errors['name'])): ?>
                <p class="form__message"><?=$errors['name'] ?></p>
                <?php endif; ?>
            </div>

            <div class="form__row">
                <label class="form__label" for="project">Проект <sup>*</sup></label>

                <select class="form__input form__input--select <?=getClassError($errors, 'project'); ?>" name="project" id="project">
                <?php if(!count($projects)): ?>
                   <option value=""></option>
                <?php endif; ?>
                <?php foreach ($projects as $project): ?>
                   <option value="<?=getValue($project, 'id'); ?>"<?=getSelected('project', $project['id'])?>><?=htmlspecialchars(getValue($project, 'name')); ?></option>
                <?php endforeach; ?>
                </select>

                <?php if(isset($errors['project'])): ?>
                <p class="form__message"><?=$errors['project'] ?></p>
                <?php endif; ?>
            </div>

            <div class="form__row">
                <label class="form__label" for="date">Дата выполнения</label>

                <input class="form__input form__input--date <?=getClassError($errors, 'date'); ?>" type="text" name="date" id="date" value="<?=getPostVal('date'); ?>" placeholder="Введите дату в формате ГГГГ-ММ-ДД">

                <?php if(isset($errors['date'])): ?>
                <p class="form__message"><?=$errors['date'] ?></p>
                <?php endif; ?>
            </div>

            <div class="form__row">
                <label class="form__label" for="file">Файл</label>

                <div class="form__input-file">
                    <input class="visually-hidden" type="file" name="file" id="file" value="<?=getFilesVal('file'); ?>">

                    <label class="button button--transparent" for="file">
                        <span>Выберите файл</span>
                    </label>

                    <?php if(isset($errors['file'])): ?>
                        <p class="form__message"><?=$errors['file'] ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form__row form__row--controls">
                <input class="button" type="submit" name="" value="Добавить">
            </div>
        </form>
    </main>
</div>
