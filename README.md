# Attendance Management System - XAMPP Setup Guide

## Prerequisites
- Download and install [XAMPP](https://www.apachefriends.org/download.html)
- Ensure MySQL and Apache are running in XAMPP

## Database Setup
1. Open **phpMyAdmin** by visiting:
   ```
   http://localhost/phpmyadmin/
   ```
2. Click on **Import** and upload `attendance_db.sql` file.
3. Ensure that the database name is **attendance_db**.
4. The default MySQL port should be **3306** (make sure it matches your XAMPP configuration).

## Running the Project
1. Place the project folder inside the `htdocs` directory of XAMPP.
2. Start Apache and MySQL in the XAMPP Control Panel.
3. Access the project in the browser using:
   ```
   http://localhost/<your_project_folder>/
   ```
   Replace `<your_project_folder>` with the actual folder name.

## Troubleshooting
- If MySQL is not starting, ensure that port **3306** is not in use by another application.
- Check `config.php` (or equivalent configuration file) to ensure it has the correct database credentials:
   ```php
   $servername = "localhost";
   $username = "root";
   $password = ""; // Default is empty in XAMPP
   $dbname = "attendance_db";
   ```

## Additional Notes
- Modify `.htaccess` if necessary for URL rewriting.
- Ensure necessary extensions are enabled in `php.ini` if facing errors.

### Happy Coding! 🚀

