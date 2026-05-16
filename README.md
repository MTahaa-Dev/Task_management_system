# Premium Task Management System - Final Deliverable & Documentation

Welcome to your brand new, production-ready Task Management System built entirely with pure PHP, MySQL, and modern frontend technologies.

---

## 2. Step-by-Step Installation Guide

1. **Environment Setup**: Ensure you have a local web server running (XAMPP, WAMP, MAMP, or Laragon) with PHP 7.4+ and MySQL.
2. **Move Files**: Copy the entire `demo` folder into your web server's document root (e.g., `htdocs` for XAMPP).
3. **Database Import**: 
   - Open phpMyAdmin or your preferred SQL client.
   - Create a new database named `task_management_system`.
   - Import the provided `database.sql` file. This script will automatically create all tables and insert the default admin credentials.
4. **Configuration**: If your MySQL credentials differ from the default (`root` with no password), edit the `config/database.php` file to match your local setup.
5. **Launch**: Open your browser and navigate to `http://localhost/demo/`. You will be automatically routed to the login page.

---

## 3. Default Admin Credentials

- **Username**: `admin`
- **Email**: `admin@example.com`
- **Password**: `admin123`
*(Ensure you change this immediately after your first login via the Profile settings!)*

---

## 4. Folder & File Architecture Explanation

- `config/`
  - `database.php`: Handles the secure PDO database connection and strict error reporting.
- `auth/`
  - `login.php` & `register.php`: Split-screen UI pages for session creation and user generation.
  - `logout.php`: Safely destroys sessions and cookies.
  - `forgot_password.php` & `reset_password.php`: The password recovery flow.
- `admin/`
  - `dashboard.php`: The visual hub containing Chart.js graphs, animated stat counters, and recent activity timelines.
  - `tasks.php`: The advanced table view with multi-dimensional filtering, global search, PDF/CSV exports, and Quick Add Modals.
  - `create_task.php` & `edit_task.php`: Detailed task management forms handling file uploads, subtask checklists, and deadline countdowns.
  - `kanban.php`: The interactive drag-and-drop board.
  - `calendar.php`: The FullCalendar.js implementation.
  - `users.php`: User CRUD operations allowing bans/deletions.
  - `activity_log.php`: A paginated master list of all system actions.
- `user/`
  - `dashboard.php`: Personalized stats and productivity scores.
  - `my_tasks.php`: Restricted view for the user's assigned tasks and a detailed view for discussions/attachments.
  - `profile.php`: Avatar uploads and bio modifications.
  - `calendar.php`: Personal deadline viewer.
- `includes/`
  - `functions.php`: Core security utility functions (CSRF token generation, XSS sanitization) and UI helpers.
  - `auth_check.php`: A security middleware script ensuring users cannot access protected routes without valid sessions.
  - `header.php`, `footer.php`, `navbar.php`, `sidebar.php`: The global layout shell.
- `assets/`
  - Contains all `css`, `js`, and dynamically generated `uploads` folders.

---

## 5. How Every Feature Works

**Advanced Task Filtering & Search**:
Located in `admin/tasks.php`, the frontend form passes GET parameters to the URL. The PHP backend dynamically builds an SQL `WHERE` clause array based on which fields (Status, Priority, Category, Search String) are populated, executing a secure PDO prepared statement.

**Secure File Uploads**:
When adding/editing tasks or updating profiles, the backend explicitly checks the `$_FILES` array against allowed MIME types (e.g., `image/jpeg`, `application/pdf`). It enforces a 5MB size limit and renames the file using `uniqid()` before moving it to the `uploads` folder to prevent overwriting and executing malicious scripts.

**Task Subtasks / Checklists**:
In `admin/edit_task.php`, admins can add granular subtasks. When an admin checks/unchecks a subtask, the form auto-submits, updating the boolean in the database. A custom PHP function (`updateTaskProgress()`) calculates the ratio of completed subtasks to total subtasks, updating the parent task's overall progress bar instantly.

---

## 6. How Dark Mode Works

Dark mode is entirely CSS-variable driven. 
1. `style.css` defines the core variables (`--bg-color`, `--card-bg`, etc.).
2. `darkmode.css` contains an override block `[data-theme="dark"]` that reassigns those core variables to dark slate/blue hex codes.
3. In `main.js`, clicking the moon icon toggles the `data-theme` attribute on the root `<html>` tag and saves the state as a string in the browser's `localStorage`.
4. A tiny inline `<script>` tag in the `<head>` of `header.php` instantly reads this `localStorage` value before the page renders, preventing the dreaded "white flash" when navigating between pages.

---

## 7. How Animations Are Implemented

1. **AOS Library**: Included in the footer, `AOS.init()` scans the DOM for elements with `data-aos` attributes. We utilized `fade-up`, `zoom-in`, and `fade-right` across all dashboard cards to make them slide into place gracefully.
2. **CSS Transitions**: Defined in `style.css` (`--transition-normal`), any element with a hover state (buttons, sidebar links, cards) uses CSS `transform: translateY(-5px)` to create a smooth lifting effect.
3. **Confetti JS**: When a task's status changes to "Completed" via a form submission, a temporary `$_SESSION['trigger_confetti'] = true;` flag is set. Upon page reload, PHP reads this flag, unsets it, and executes a javascript block invoking the Canvas Confetti library to simulate an explosive celebration.

---

## 8. How Kanban and Calendar Work

**Kanban Board**:
Utilizes `Sortable.js`. We grouped the tasks in PHP by their status into 4 distinct HTML columns. SortableJS listens to drag events. When a card is dropped into a new column, the JS captures the Task ID and the New Status, sending an invisible `fetch()` AJAX POST request to the same page. The PHP backend intercepts this POST request, updates the database, and returns a JSON success response, triggering a localized UI toast notification without ever refreshing the page.

**Calendar**:
Utilizes `FullCalendar.js`. The calendar initializes via JS and looks to the backend URL `calendar.php?action=get_events` for its data. The PHP backend intercepts this GET parameter, fetches all tasks with deadlines, maps their priorities to specific hex colors, and outputs a pure JSON string. The calendar consumes this string and renders the blocks. Clicking an event triggers a JS listener that populates a Bootstrap Modal with the JSON `extendedProps`.

---

## 9. How Notifications Work

We implemented a proactive notification engine.
Whenever a critical action occurs (e.g., an Admin assigns a task to a User, or a user registers), the `create_notification()` helper function inserts a row into the `notifications` table containing the User ID, Message, and an `is_read = 0` flag.
In `includes/navbar.php`, the bell icon queries this table for unread alerts to generate the red badge counter. The dropdown loops through the 5 most recent alerts. If the user clicks "Mark all read", an inline GET request flips the `is_read` flag to 1, instantly clearing the badge. Real-time feedback (success/error popups) is handled globally via session flash messages piped into the `Toastify.js` library upon page load.

---

## Security Audit Summary

- **CSRF Tokens**: Every single `<form>` contains `<?php csrf_field(); ?>`, which injects a cryptographically secure hidden token. The backend verifies this token before processing POST requests.
- **SQL Injection**: Every query utilizes PDO `prepare()` and `execute([$params])`. No user input is directly concatenated into SQL strings.
- **XSS Prevention**: Whenever user input is displayed back to the screen (like Task Titles or Comments), it is wrapped in `htmlspecialchars()` via our custom `sanitize()` function.
- **Access Control**: `includes/auth_check.php` prevents non-logged-in users from viewing dashboards, and explicitly checks if `$required_role == 'admin'` before allowing access to administrative scripts.
