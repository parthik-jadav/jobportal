# Job Portal

A PHP and MySQL job portal with student registration, job browsing and saved jobs, profile management, feedback, and an administrator area.

## Requirements

- PHP 7.4 or later with the PDO MySQL extension enabled
- MySQL or MariaDB
- A local web server such as Laragon, XAMPP, or Apache

## Local setup

1. Place the project in your web-server directory. With Laragon, the default location is:

   ```text
   C:\laragon\www\jobportal
   ```

2. Start Apache and MySQL from Laragon (or start your equivalent web server and database service).

3. Create and populate the database by importing [`database/jobportal.sql`](database/jobportal.sql). In Laragon, open **Database** / phpMyAdmin, select **Import**, and choose that file. The script creates a database named `jobportal`.

4. Check the database settings in [`config/database.php`](config/database.php). The defaults are:

   ```php
   $db_host = 'localhost';
   $db_name = 'jobportal';
   $db_user = 'root';
   $db_pass = '';
   ```

   Update them if your MySQL username or password differs.

5. Open the site in a browser:

   ```text
   http://localhost/jobportal/
   ```

## Default administrator account

The imported database creates this administrator account:

```text
Email:    admin@jobportal.test
Password: admin123
```

Sign in at `http://localhost/jobportal/admin/login.php`.

For any non-demo deployment, change this password immediately and use secure database credentials.

## Project structure

```text
actions/     Form handlers for authentication, saved jobs, feedback, and profiles
admin/       Administrator pages
config/      Database configuration
database/    Database schema and seed data
includes/    Shared header, footer, and authentication helpers
student/     Student dashboard, jobs, profile, and saved-jobs pages
css/, js/    Front-end assets
```

## Troubleshooting

- **Database connection failed**: import `database/jobportal.sql`, confirm MySQL is running, then verify `config/database.php`.
- **404 / site does not load**: confirm the folder is inside your web server's document root and use the matching local URL.
- **Login does not work**: re-import the database to restore the default demo account, or check the `users` table in the `jobportal` database.
