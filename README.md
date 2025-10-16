# Greenlife-Wellness
GreenLife Wellness is a responsive web-based appointment system for a wellness center, built using HTML, CSS, JavaScript, PHP, and MySQL. It allows users to register, book appointments, and explore services, while admins manage bookings and services through a clean, user-friendly interface.

## What You Can Do
- Clients: Register, log in, browse services, book appointments, send questions
- Therapists: Log in, update availability, see bookings, reply to clients
- Admins: Log in, manage users and services, view appointments

## Quick Start 
1. Open the XAMPP Control Panel
2. Start both `Apache` and `MySQL`
3. Open your browser and go to: `http://localhost/greenlife-wellness/`

## One-Time Setup: Load the Database
You already have the database file: `c:\xampp\htdocs\greenlife-wellness\greenlife_wellness.sql`

1. Go to `http://localhost/phpmyadmin`
2. Click “Databases” → create a database named `greenlife_wellness` (or select your preferred name)
3. Click “Import”
4. Choose `greenlife_wellness.sql` from the project folder
5. Click “Go” and wait for the success message

## Logging In
- Sample usernames and passwords are in:
  `c:\xampp\htdocs\greenlife-wellness\Usernames and Passwords.txt`
- Login page: `http://localhost/greenlife-wellness/pages/login.php`
- Register page (for new clients): `http://localhost/greenlife-wellness/pages/register.php`

## Booking an Appointment (Client)
1. Log in
2. Go to “Appointments”
3. Pick a service and time
4. Confirm your booking

## Sending a Question (Client)
1. Go to “Contact” or “Messages”
2. Write your question
3. Submit — a therapist/admin will respond

## Where Things Are (Simple)
- Website pages: `index.php` and the `pages` folder
- Shared parts (header, footer, login, etc.): `includes` folder
- Styles, images, scripts: `assets` folder
- Database file: `greenlife_wellness.sql`
- Sample login details: `Usernames and Passwords.txt`

## If Something Doesn’t Work
- “Website not opening” → Make sure `Apache` and `MySQL` are both running in XAMPP
- “Page not found” → Check the folder name is exactly `c:\xampp\htdocs\greenlife-wellness`
- “Database error” → The site usually uses:
  - Host: `localhost`
  - User: `root`
  - Password: (empty)
  If you changed your MySQL password, update `includes/db.php` or ask your admin.
- “Login not working” → Use the sample accounts in `Usernames and Passwords.txt`, or register a new client account
- “Styles look broken” → Make sure the `assets` folder is present and unchanged

## About the Website
- Built with HTML, CSS, JavaScript, PHP, and MySQL
- Mobile-friendly and easy to use
- Designed for clients, therapists, and admins
