CREATE DATABASE c3015_lab4;
USE c3015_lab4;

CREATE TABLE inventory (
    id INT UNSIGNED auto_increment,
    item_name VARCHAR(255),
    quantity INT UNSIGNED,
    PRIMARY KEY(id)
);