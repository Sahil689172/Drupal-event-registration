# Event Registration Module (Drupal 10)

## Overview
This is a custom Drupal 10 module that allows users to register for events through a dynamic registration form.  
The module supports event configuration by admin, AJAX-based dependent dropdowns, validation, database storage, email notifications, and an admin listing with CSV export.

No contributed modules are used.

---

## System Requirements
- Drupal 10.x
- PHP 8.1+
- MySQL 5.7 / 8
- Apache (XAMPP)
- Composer

---

## Installation Steps

1. Install Drupal 10 using Composer:
composer create-project drupal/recommended-project event_portal

cpp
Copy code

2. Place the module inside:
web/modules/custom/event_registration

markdown
Copy code

3. Enable the module:
- Go to:
  ```
  /admin/modules
  ```
- Enable **Event Registration**

4. Clear cache:
/admin/config/development/performance

yaml
Copy code

---

## Module URLs

### Event Configuration (Admin)
/admin/config/event-registration/config

yaml
Copy code

Used by admin to configure:
- Event name
- Category
- Registration start date
- Registration end date
- Event date

---

### Event Registration Form (User)
/event/register

yaml
Copy code

Features:
- Available only during registration window
- AJAX-based category → date → event name
- Duplicate registration prevention

---

### Admin Settings
/admin/config/event-registration/settings

yaml
Copy code

Configuration options:
- Admin notification email
- Enable / disable admin email notifications

Uses Drupal Config API (no hard-coded values).

---

### Admin Registration Listing
/admin/event-registrations

yaml
Copy code

Displays:
- Name
- Email
- College
- Department
- Event Name
- Event Date

---

### CSV Export
/admin/event-registrations/export

yaml
Copy code

Downloads all registrations as a CSV file.

---

## Database Tables

### 1. event_config
Stores event configuration details.

Fields:
- id (PK)
- reg_start
- reg_end
- event_date
- event_name
- category

---

### 2. event_registration
Stores user registrations.

Fields:
- id (PK)
- full_name
- email
- college
- department
- event_config_id (FK)
- created (timestamp)

---

## Validation Logic

- Full Name:
  - Allows only alphabets and spaces
- Email:
  - Valid email format
- Duplicate Registration:
  - Prevented using combination of:
    ```
    email + event_config_id
    ```

User-friendly validation messages are displayed.

---

## AJAX Logic

1. Select Event Category  
   → Loads available Event Dates

2. Select Event Date  
   → Loads Event Names

AJAX callbacks query `event_config` table dynamically.

---

## Email Notifications

Implemented using **Drupal Mail API**.

- User receives confirmation email
- Admin receives notification email (if enabled)

Note:
On localhost, emails may fail due to missing SMTP configuration.  
Mail logic is fully implemented and verified via logs.

---

## Permissions

Admin-only access is enforced for:
- Event configuration
- Admin listing
- CSV export

(Custom permission prepared; admin role used for access.)

---

## Coding Standards Followed

- PSR-4 Autoloading
- Drupal Form API
- Drupal Database API
- Dependency Injection where applicable
- No contrib modules used

---

## GitHub Repository Structure

event_portal/
├── composer.json
├── composer.lock
├── web/
│ └── modules/
│ └── custom/
│ └── event_registration/
│ ├── src/
│ ├── config/
│ ├── *.yml
│ ├── *.install
│ └── README.md
└── database.sql

yaml
Copy code

---

## Author
Custom module developed as part of an academic assignment using Drupal 10.

Note: Email delivery on localhost may fail due to missing SMTP configuration.
Mail logic is fully implemented using Drupal Mail API and verified via logs.
