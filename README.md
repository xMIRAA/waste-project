# CleanCity Waste Collection System

A simple and modern PHP-based web application for managing waste collection services for residents and administrators. The system allows users to log in, view schedules, request pickups, submit complaints, and manage basic operations through role-based access.

## ✨ Features

- Secure user login and logout
- Role-based access for residents and admins
- Resident dashboard for schedules, pickup requests, and complaints
- Admin dashboard for managing users, schedules, and reports
- Clean and responsive user interface
- Simple MySQL database structure using XAMPP

## 🛠️ Tech Stack

- PHP
- MySQL / MariaDB
- HTML5
- CSS3
- JavaScript
- XAMPP

## 📁 Project Structure

- auth/ - login, logout, and access protection
- database/ - database connection and schema
- admin/ - admin pages
- resident/ - resident pages
- shared/ - shared layout and common assets

## 🚀 Getting Started

1. Place the project in your XAMPP htdocs folder:
   - C:\xampp\htdocs\waste-project

2. Start Apache and MySQL in XAMPP.

3. Import the database schema from database/schema.sql.

4. Open the app in your browser:
   - Home page: http://localhost/waste-project/
   - Login page: http://localhost/waste-project

## 🔐 Default Login Accounts

The database schema creates these demo users:

- Resident account
  - Username: ucsc
  - Password: ucsc

- Admin account
  - Username: admin
  - Password: admin123

> If you only need to test the basic flow, the resident account with username ucsc and password ucsc is enough.

## 📌 Notes

- The database is intentionally kept simple and uses a users table for authentication.
- Passwords are stored as hashed values for security.

## 👨‍💻 Developed For

This project is designed as a practical web application for digital waste collection management and can be expanded with more features in the future.
