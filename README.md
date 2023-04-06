# Steps to run the project
$ `composer install`
$ `cp .env.example .env`
$ `php artisan key:generate`
Create a database to import the sql file to
$ `mysql -u root -p`
```
CREATE DATABASE laravel;
CREATE USER 'laravel'@'localhost' IDENTIFIED BY '1234';
GRANT ALL PRIVILEGES ON laravel.* TO 'laravel'@'localhost';
FLUSH PRIVILEGES;
exit;
````
Import the sql file
$ `mysql -u laravel -p laravel < myproject_database.sql`
```
1234
```
$ `php artisan serve`