Event Registration Module — Drupal 10
📌 Project Overview

This project implements a custom Event Registration system in Drupal 10 using only Drupal core APIs.
Administrators can configure events, and users can register for events through a dynamic form with AJAX-based filters.
All registrations are stored in custom database tables and managed via an admin listing page.

No contributed modules are used.

⚙️ Installation Steps

Clone the repository

git clone https://github.com/Sahil689172/Drupal-event-registration.git
cd event_portal


Install dependencies

composer install


Create database

CREATE DATABASE event_portal;


Import database tables

mysql -u root -p event_portal < database.sql


Open Drupal site

http://localhost/event_portal/web


Enable the module

Admin → Extend → Enable “Event Registration”


Clear cache

/admin/config/development/performance

🔗 Application URLs
User Page

Event Registration Form

http://localhost/event_portal/web/event/register

Admin Pages

Event Configuration Page

http://localhost/event_portal/web/admin/config/event-registration/config


Admin Settings (Email Configuration)

http://localhost/event_portal/web/admin/config/event-registration/settings


Admin Registration Listing

http://localhost/event_portal/web/admin/event-registrations


CSV Export

http://localhost/event_portal/web/admin/event-registrations/export


Permissions

http://localhost/event_portal/web/admin/people/permissions

🗄️ Database Tables
1️⃣ event_config

Stores event details configured by admin.

Column	Description
id	Primary key
reg_start	Registration start date
reg_end	Registration end date
event_date	Event date
event_name	Event name
category	Event category
2️⃣ event_registration

Stores user registrations.

Column	Description
id	Primary key
full_name	User full name
email	User email
college	College name
department	Department
event_config_id	Foreign key to event_config
created	Submission timestamp
🔄 Form & AJAX Logic

Category selection → loads event dates (AJAX)

Event date selection → loads event names (AJAX)

Registration form is shown only between configured start & end dates

✅ Validation Logic

Email format validation (Drupal Form API)

Text fields allow only letters and spaces

Duplicate registrations prevented using:

Email + Event


User-friendly error messages displayed on form

✉️ Email Notification Logic

Implemented using Drupal Mail API

Emails sent to:

Registered user (confirmation)

Admin (optional, configurable)

Email content includes:

Name

Event name

Event date

Category

⚠️ Local Email Limitation

Email sending does not work on localhost because:

No SMTP server is configured in XAMPP

Drupal Mail API requires SMTP / MTA

The email logic is implemented correctly and works in environments with proper SMTP configuration.

🔐 Permissions

Custom permission:

view event registrations


Used to restrict access to the admin registration listing page.

📁 Project Structure
event_portal/
├── composer.json
├── composer.lock
├── database.sql
├── web/
│   └── modules/
│       └── custom/
│           └── event_registration/
│               ├── config/
│               │   └── install/
│               │       └── event_registration.settings.yml
│               ├── src/
│               │   ├── Form/
│               │   │   ├── EventConfigForm.php
│               │   │   ├── EventRegistrationForm.php
│               │   │   ├── EventSettingsForm.php
│               │   │   └── AdminRegistrationListForm.php
│               │   ├── Controller/
│               │   │   └── EventRegistrationAdminController.php
│               │   └── Service/
│               │       ├── EventRegistrationMailer.php
│               │       └── EventRegistrationStorage.php
│               ├── event_registration.info.yml
│               ├── event_registration.routing.yml
│               ├── event_registration.permissions.yml
│               ├── event_registration.services.yml
│               ├── event_registration.install
│               └── README.md

🏁 Conclusion

This project demonstrates:

Custom Drupal Form API usage

AJAX-based dependent dropdowns

Custom database schema

Config API for admin settings

Dependency Injection

Secure access control

✅ Ready for evaluation
✅ Ready for deployment (SMTP-enabled environments)

