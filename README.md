# 🚀 TravelTourBooking System

[![MIT License](https://img.shields.io/badge/License-MIT-green.svg)](https://choosealicense.com/licenses/mit/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)](https://www.mysql.com/)
[![Docker](https://img.shields.io/badge/Docker-Enabled-blue.svg)](https://www.docker.com/)

Welcome to the **TravelTourBooking System** – a modern, web-based platform designed to streamline travel and tour bookings! 🌍✈️ This system automates booking processes, enhances user experience, and provides powerful admin tools for efficient management. Built with clean, maintainable code following Google's coding standards (meaningful names, short functions, clear comments, and consistent formatting).

## 📋 Project Overview

Travel agencies often struggle with manual processes leading to inefficiencies, errors, and poor customer satisfaction. This system solves these issues by offering:

- Automated tour browsing and booking
- Real-time availability checks
- Secure online payments
- Comprehensive admin dashboards
- Data-driven reports and analytics

**Target Users**:

- **Customers** 👥: Browse tours, book trips, make payments, and view personal reports.
- **Administrators** 🛡️: Manage tours, users, bookings, and generate system-wide reports.

**Business Benefits**:

- Reduced operational costs 💰
- Improved accuracy and efficiency ⚙️
- Enhanced customer satisfaction 😊
- Better decision-making through analytics 📊

## ✨ Features

- **User Authentication** 🔐: Secure registration, login, and role-based access (Admin vs. User).
- **Tour Management** 🗺️:
  - Admins: CRUD operations (Create, Read, Update, Delete) for tours.
  - Users: View all available tours, check availability, and book/cancel.
- **Booking System** 📅: Real-time booking with options to pay now or later.
- **Payment Processing** 💳: Integrated online payments (simulated or via gateways).
- **Admin Dashboard** 📈: View users, manage access, generate reports on bookings/revenue.
- **User Dashboard** 📝: Personal booking history, reports, and account management.
- **Reporting & Analytics** 📉: Automated reports for admins and personalized ones for users.
- **Additional Enhancements**:
  - Email notifications for bookings 📧
  - Search and filter tours by location, price, or date 🔍
  - Responsive design for mobile users 📱

## 🛠️ Technology Stack

- **Backend** ⚙️: PHP 7.4+ (following best practices), MySQL for database.
- **Frontend** 🎨: HTML5, CSS3, JavaScript (for dynamic interactions).
- **Database** 🗄️: MySQL 5.7+ with enhanced schema for tours, users, bookings, and payments.
- **Server** 🌐: Apache via XAMPP/WAMP/MAMP.
- **Version Control** 📂: GitHub for collaborative development.
- **Containerization** 🐳: Docker for easy deployment.
- **Other Tools**: Composer for dependencies (optional), PHPUnit for testing.

## ⚙️ Installation

### Prerequisites

- XAMPP/WAMP/MAMP installed.
- PHP 7.4 or higher.
- MySQL 5.7 or higher.
- Git for version control.
- Docker (for containerized setup).
- Composer (optional for dependencies).

### Setup Steps

1. **Clone the Repository** 📥:

   ```bash
   git clone https://github.com/yourusername/ttbooking.git
   ```

2. **Import Database Schema** 🗄️:  
   Use phpMyAdmin or MySQL CLI to import `database/schema.sql`.
3. **Configure Database** 🔧:  
   Edit `config.php` with your database credentials (host, username, password, dbname).
4. **Setup Database** ⚙️:  
   Run `php setup_database.php` to initialize tables and seed data.
5. **Run Locally** 🌐:  
   Start XAMPP, navigate to `http://localhost/ttbooking`.

## 🐳 Docker Configuration

This repository includes a `Dockerfile` and `docker-compose.yml` for a local / dev container setup.

Recommended workflow (docker-compose):

1. Copy `.env.example` to `.env` and edit values (do NOT commit `.env` into source control):

```bash
cp .env.example .env
# edit .env to set secrets
```

2. Start the app and database:

```bash
docker-compose up -d --build
```

3. Initialize the database (one-time):

```bash
docker-compose run --rm init
```

(Alternative: `docker-compose run --rm web php setup_database.php`)

4. Run tests (optional):

```bash
docker-compose run --rm web php tests/TourRepositoryTest.php
```

5. Access the services:

- App: http://localhost:8000 ✅ (or http://localhost:${WEB_PORT} if you changed `WEB_PORT` in `.env`)
- phpMyAdmin: http://localhost:8081 (user: `ttuser`, password: `secret`) ✅

Demo admin user (convenience):

- A demo admin account can be created automatically by running the seed script:

```bash
docker-compose run --rm web php scripts/create_admin.php
```

Default demo admin credentials (if created with no env overrides): `username=admin`, `password=password` — change these in production.

If you'd like, I ran the script and created a demo admin for you on the current environment.

Default DB credentials are set in `.env.example` and copied into `.env` by you.

Notes & Troubleshooting:

- If you see a DB connection error, ensure the `db` container is healthy (`docker ps` + `docker logs <db>`).
- This compose file maps the DB container to host port **3307** (host:3307 -> container:3306) to avoid conflicts with local MySQL servers that commonly use 3306. If you prefer to use 3306, stop your local MySQL service (e.g., XAMPP) or change the mapping in `docker-compose.yml`.
- On Windows, if `mysqladmin` isn't found in the web container, run the init step using the `init` service as documented.
- To re-run the DB init script: `docker-compose run --rm init`.
- For production, use stronger passwords, move secrets to an env file or secret manager, and tune MySQL settings.

Change these values for production use and **do not** commit secrets to source control.

If you prefer a single container you can also build and run the image directly (less convenient during development):

```bash
docker build -t ttbooking .
docker run -p 8000:80 -d ttbooking
```

Notes:

- The app reads DB configuration from environment variables (`DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) so it works with Docker out of the box.
- For persistent DB data the compose file mounts a named volume (`db_data`).
- For production, use stronger passwords, move secrets to an env file or secret manager, and tune MySQL settings.

## 🔍 How the System Works

### Admin Privileges 🛡️

- Perform CRUD on tours (add new tours with details like location, price, dates).
- Manage users: View registered users, control access.
- Generate reports: Revenue, booking stats, user analytics.
- View comprehensive dashboard.

### User Privileges 👥

- Register/login to an account.
- Browse and search available tours.
- Book a tour: Select, check availability, proceed to payment (pay now or later).
- Cancel bookings.
- View personal reports and booking history.

The system uses the **Repository Design Pattern** in `/Repository/` for clean data access, separating business logic from data storage.

## 🧪 Testing Plan & Test Cases

We follow a comprehensive testing strategy:

- **Unit Tests**: For individual components (e.g., TourRepository).
- **Integration Tests**: For end-to-end flows (e.g., booking process).
- **Tools**: PHPUnit.

Run tests:  

```bash
php tests/TourRepositoryTest.php
```

Sample Test Cases:

- **Authentication**: Test login with valid/invalid credentials.
- **Booking**: Simulate tour booking and verify database updates.
- **Payment**: Mock payment gateway responses.
- **Edge Cases**: Handle sold-out tours, invalid inputs.

## 🚀 Deployment

For production:

1. Update `config.php` with production DB credentials.
2. Set file permissions (e.g., 755 for directories).
3. Configure `.htaccess` for URL rewriting and security.
4. Enable HTTPS via SSL certificate.
5. Deploy to a server (e.g., AWS, Heroku) or use Docker for cloud deployment.

## 📚 Case Study

### Problem Statement

Manual tour management leads to inefficiencies, errors, and poor tracking.

### Solution

This automated system provides real-time booking, payments, and reports.

### Software Design

- **Approach**: Object-Oriented Design with MVC pattern.
- **Diagrams** (Summarized from PowerPoint):
  - **Activity Diagram** 📊: Shows user flow from browsing to booking.
  - **Data Flow Diagram** 🔄: Illustrates data movement between user, system, and DB.
  - **Sequence Diagram** ⏳: Depicts interactions for booking a tour.

(For full diagrams, refer to `docs/design.pptx`.)

### Design Pattern Implementation

- **Repository Pattern**: Used in `/Repository/` for abstracting data access, improving testability and maintainability.

### Database Schema Enhancement

- Added tables for payments, reports, and user roles.
- Indexes for faster queries on tours and bookings.

### GitHub Version Control Setup

- Repository: Initialized with `.gitignore` for ignoring sensitive files.
- Branches: `main` for production, `develop` for features.
- Commit often with meaningful messages (e.g., "feat: add tour CRUD").

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details. 📜

---

Built by Thoti. Contributions welcome! Fork and submit PRs on GitHub. If you encounter issues, open a ticket. Happy traveling!🌟
