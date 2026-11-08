# 🏫 School Management System

A simple, functional and professional School Management System built with **native PHP**, **Vanilla JavaScript**, **HTML5**, **CSS3**, and **MySQL**.

## Features

- 🔐 Admin & Teacher login with role-based access
- 🎓 Student Management (register, edit, delete, search)
- 👩‍🏫 Teacher Management with auto login account creation
- 🏛️ Class & Subject Management
- ✅ Attendance Marking (Present/Absent per class per day)
- 📝 Examination & Results Management with auto grading
- 📊 Reports (Students, Attendance, Results) with print support
- 🔑 Password change with show/hide toggle

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8+ (Native, no frameworks) |
| Frontend | HTML5, CSS3, Vanilla JavaScript |
| Database | MySQL 8 |
| Server | Apache (Docker) |

## Grading System

| Marks % | Grade |
|---|---|
| 80 – 100 | A |
| 70 – 79 | B |
| 60 – 69 | C |
| 50 – 59 | D |
| Below 50 | E |

## Local Setup (XAMPP)

1. Copy `school_management/` to `C:\xampp\htdocs\`
2. Import `database/school_management.sql` in phpMyAdmin
3. Update `config/database.php` with your DB password
4. Visit `http://localhost/school_management`

## Deployment (Render)

See the hosting guide in the project documentation.

## Project Structure

```
school_management/
├── index.php
├── login.php / logout.php
├── dashboard.php
├── config/database.php
├── database/school_management.sql
├── includes/ (auth, functions, header, sidebar, footer)
├── css/style.css
├── js/main.js
├── admin/ (students, teachers, classes, subjects, attendance, exams, reports)
└── teacher/ (dashboard, attendance, exams)
```

---
Built as a school project — Patrick Mwanza
