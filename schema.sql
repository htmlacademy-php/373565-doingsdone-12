CREATE DATABASE doingsdone
    DEFAULT CHARACTER SET utf8
    DEFAULT COLLATE utf8_general_ci;

USE doingsdone;

CREATE TABLE users (
    id INT unsigned AUTO_INCREMENT PRIMARY KEY NOT NULL,
    datetime TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    email VARCHAR(128) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    password VARCHAR(64) NOT NULL
);

CREATE TABLE projects (
    id INT unsigned AUTO_INCREMENT PRIMARY KEY NOT NULL,
    name VARCHAR(255) UNIQUE NOT NULL,
    user_id INT unsigned NOT NULL
);

CREATE TABLE tasks (
    id INT unsigned AUTO_INCREMENT PRIMARY KEY NOT NULL,
    datetime TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    status TINYINT(1) DEFAULT 0 NOT NULL,
    name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NULL,
    date TIMESTAMP NULL,
    user_id INT unsigned NOT NULL,
    project_id INT unsigned NOT NULL
);

CREATE INDEX status ON tasks(status);
CREATE INDEX name ON tasks(name);
CREATE INDEX date ON tasks(date);
