# BEST PROGRAMMING PRACTICES AND DESIGN PATTERNS - PROJECT DOCUMENTATION

## PHASE 1: SYSTEM ANALYSIS AND DESIGN

### 1. Topic and Case Study
**Topic**: Design and Development of an Automated Travel and Tour Booking System.
**Case Study**: "Wanderlust Travels Ltd" (A fictitious travel agency).

### 2. Functional Diagram 
*(Note for Student: Insert the "Component Diagram" here to show the internal working. You can render the `Diagrams/component_diagram.mermaid` to an image and place it here).*

### 3. Problem Statement
**Problem faced by the company**: 
Wanderlust Travels Ltd currently relies on manual booking processes using phone calls, emails, and Excel spreadsheets. This leads to severe inefficiencies, double-bookings, delayed payment tracking, and poor customer satisfaction. They lack a centralized system to manage tour availability, accept online bookings, and generate automated revenue reports.

### 4. Object-Oriented System Analysis and Design Diagrams
*(Note for Student: Render the following Mermaid diagrams from the `Diagrams` folder and paste the images into your PowerPoint)*:
1. **Use case Diagram**: Refer to `Diagrams/use_case_diagram.mermaid`
2. **Class Diagram**: Refer to `Diagrams/class_diagram.mermaid`
3. **Activity Diagram**: Refer to `Diagrams/activity diagram.mermaid`
4. **Sequence Diagram**: Refer to `Diagrams/sequence diagram.mermaid`
5. **Component Diagram**: Refer to `Diagrams/component_diagram.mermaid`


---

## PHASE 2: SOFTWARE DEVELOPMENT PROTOTYPE

### 1. Prototype Overview
A fully functional prototype has been developed using PHP, MySQL, HTML, and CSS.
- **Layout & Design**: Responsive design with clean navigation menus, consistent color scheme, and structured layouts.
- **Input Processing**: Forms for Registration, Login, and Booking are validated. 
- **Basic Workflows**: A user can browse tours, click "Book", select dates/participants, and confirm booking.
- **User Journeys**: `Login -> Dashboard -> Browse Tours -> Book Tour -> Logout`.
- **Database**: Actual MySQL database integration simulates real-time availability and user session management.

### 2. Programming Best Practices Used
1. **Meaningful Naming Convention**: Variables like `$tour_id`, `$total_price` and classes like `TourRepository` are used instead of vague names like `$x` or `$obj`.
2. **Proper Indentation**: Consistent spacing and indentation enhance readability.
3. **Single Responsibility Principle**: Logic is separated (e.g., `config.php` only handles DB connection, `TourRepository` handles database queries).
4. **Commenting**: Complex logic and configuration blocks have clear, concise comments.

### 3. Software Design Pattern Used
**Repository Design Pattern**: 
- **How it is used**: The application uses a `TourRepository` class to encapsulate the logic required to access the database. Instead of writing raw SQL queries inside the HTML/UI files, the frontend calls methods like `getAllTours()` or `getTourById()`. This separates the Data Access Layer from the Business/Presentation Layer, making the software easier to maintain and test.


---

## PHASE 3: DOCKERIZING AND VERSION CONTROL

### 1. Dockerizing the Application
**How it was dockerized**:
The application uses Docker to containerize the environment, eliminating "it works on my machine" issues.
- A `Dockerfile` is used to build the PHP and Apache environment.
- A `docker-compose.yml` file is configured to run multiple containers together:
  - `web`: The PHP/Apache server hosting the application.
  - `db`: The MySQL 8.0 database container.
  - `phpmyadmin`: For database management.
- Dependencies and environment variables are injected using a `.env` file.

### 2. Version Control System
- **System Used**: Git & GitHub/GitLab.
- **Configuration**: A `.git` repository is initialized. A `.gitignore` file is configured to prevent pushing sensitive files like `.env` and `vendor/` to the remote repository.


---

## PHASE 4: SOFTWARE TEST PLAN

### 1. Goals of Testing
The primary goal is to ensure the booking system functions flawlessly, preventing double bookings, securing user data, and guaranteeing accurate financial calculations. This aligns directly with the requirement to replace the manual, error-prone system.

### 2. Features to be Tested
- User Authentication (Registration, Login, Session Timeout).
- Tour Management (CRUD operations by Admin).
- Booking Engine (Availability calculation, price multiplication).

### 3. Test Cases (Normal & Edge Cases)
| Test ID | Feature | Scenario (Normal Case) | Expected Result |
|---|---|---|---|
| TC01 | Auth | User logs in with correct credentials | Redirected to User Dashboard |
| TC02 | Auth | User logs in with wrong password | Error message displayed |
| TC03 | Booking | User books a tour with available slots | Booking confirmed, availability updated |
| TC04 | Booking | User tries to book more slots than available | System rejects booking with error |

### 4. Tools and Methods for Tracking Issues
- **PHPUnit**: Used for writing and running automated unit tests (e.g., `tests/TourRepositoryTest.php`).
- **GitLab Issues / GitHub Issues**: Used as a Kanban board to log bugs, assign tasks, and track issue resolution during development.
