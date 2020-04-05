/* Добавляем пользователей */
INSERT INTO users (name, email, password) VALUES ('Ирина', '8129p@mail.ru', 'irina19'),
                                                 ('Константин', 'kostya77@mail.ru', 'kos15');

/* Добавляем список проектов */
INSERT INTO projects (name, user_id) VALUES ('Учеба', 1), ('Работа', 1), ('Домашние дела', 1),
                                            ('Входящие', 2), ('Работа', 2), ('Домашние дела', 2), ('Авто', 2);

/* Добавляем задачи */
INSERT INTO tasks (status, name, due_date, user_id, project_id)
        VALUES (0, 'Собеседование в IT компании', '2019-12-01', 1, 2),
               (0, 'Выполнить тестовое задание', '2019-12-25', 1, 2),
               (1, 'Сделать задание первого раздела', '2019-12-21', 1, 1),
               (0, 'Встреча с другом', '2019-12-22', 2, 4),
               (0, 'Купить корм для кота', NULL, 2, 6),
               (0, 'Заказать пиццу', NULL, 1, 3);

/* Получаем список из всех проектов для одного пользователя*/
/* По идентификатору */
SELECT name FROM projects WHERE user_id = 1;
/* По имени пользователя*/
SELECT p.name, u.name FROM projects p JOIN users u ON u.id = p.user_id WHERE u.name = 'Ирина';

/* Получаем список из всех задач для одного проекта*/
/* По идентификатору */
SELECT name FROM tasks WHERE projects_id = 3
/* По названию проекта для конкретного пользователя */
SELECT p.name, t.name, u.name
    FROM projects p
        JOIN tasks t ON p.id = t.project_id
        JOIN users u ON u.id = t.user_id WHERE p.name = 'Домашние дела' AND u.name = 'Ирина';

/*Помечаем задачу, как выполненную*/
UPDATE tasks SET status = 1 WHERE name = 'Купить корм для кота';

/*Обновляем название задачи по её идентификатору*/
UPDATE tasks SET name = 'Сделать уборку' WHERE id = 6;
