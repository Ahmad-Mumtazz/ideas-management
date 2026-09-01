# Ideas Management System

A modern Laravel-based web application for managing ideas, tracking their progress, and organizing all resources related to each idea in one place.

Users can create and manage their own ideas, add checkpoints, attach links and files, track progress, and organize ideas using statuses, priorities, categories, tags, and due dates.

## Features

### Authentication

* User registration
* Secure login and logout
* Password validation
* Session-based authentication
* Protected authenticated areas

### Idea Management

* Create ideas
* View all personal ideas
* View individual ideas
* Edit and update ideas
* Delete ideas
* Archive and restore ideas
* Search and filter ideas
* Filter ideas by status
* Assign priorities, categories, tags, and due dates
* Add a cover image to an idea

### Checkpoints & Progress

* Add multiple checkpoints to an idea
* Mark checkpoints as completed or incomplete
* Automatically calculate idea progress
* Display progress using a visual progress bar
* Automatically manage idea status based on checkpoint completion:

  * Pending — 0% progress
  * In Progress — some checkpoints completed
  * Completed — all checkpoints completed

### Files & Links

* Attach multiple links to an idea
* Upload files and documents related to an idea
* Upload and manage idea cover images
* Replace or remove uploaded images
* Validate file types and sizes
* Store uploaded files using Laravel Storage
* Keep files and links accessible only to the owner of the idea

### Profile Management

* Update name
* Update contact information
* Upload profile picture
* Replace profile picture
* Remove profile picture
* Email address remains unchanged from the profile section

### Dashboard

* Overview of personal ideas
* Idea statistics
* Progress information
* Recent activity
* Status and priority summaries

### Activity Tracking

The application records relevant idea activity such as:

* Idea creation
* Idea updates
* Checkpoint changes
* File uploads
* Link additions
* Status changes
* Other important idea actions

### User Interface

* Responsive design
* Mobile-friendly navigation
* Desktop navigation
* Mobile menu
* Light mode
* Dark mode
* Modern cards and forms
* Progress indicators
* Status and priority badges
* Flash messages
* Interactive UI elements

## Security

Security is enforced on the server side.

* Authentication-protected idea management
* Users can access only their own ideas
* Policies are used for authorization
* Checkpoints, files, and links are protected through ownership rules
* CSRF protection
* Server-side validation
* Secure password hashing
* Session regeneration and invalidation
* Controlled file uploads
* User-specific data access

## Technology Stack

* PHP
* Laravel
* MySQL
* Blade
* Tailwind CSS
* Alpine.js
* Vite
* Laravel Eloquent ORM
* Laravel Storage
* Laravel Policies
* Laravel Form Requests

## Project Structure

```text
app/
├── Enums/
├── Http/
│   ├── Controllers/
│   └── Requests/
├── Models/
├── Observers/
├── Policies/
├── Providers/
├── Support/
└── View/
    └── Components/

database/
├── factories/
├── migrations/
└── seeders/

resources/
├── css/
├── js/
└── views/

routes/
└── web.php
```

## Requirements

Before running the project, make sure the following are installed:

* PHP
* Composer
* Node.js and npm
* MySQL
* Laravel Herd or another PHP development environment

## Installation

Clone the repository:

```bash
git clone <repository-url>
```

Move into the project directory:

```bash
cd learn
```

Install PHP dependencies:

```bash
composer install
```

Install frontend dependencies:

```bash
npm install
```

Create the environment file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Configure the database credentials in `.env`.

Run the database migrations:

```bash
php artisan migrate
```

Create the public storage link:

```bash
php artisan storage:link
```

Build the frontend assets:

```bash
npm run build
```

Start the development server:

```bash
php artisan serve
```

For frontend development with Vite:

```bash
npm run dev
```

## Environment Configuration

Update the `.env` file with your local database configuration.

Example:

```env
APP_NAME="Ideas Management System"
APP_ENV=local
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Never commit your `.env` file or expose credentials, API keys, passwords, or other secrets.

## Storage

The application uses Laravel's filesystem/storage system for uploaded:

* Profile pictures
* Idea images
* Documents
* Files

Run the following command after installation:

```bash
php artisan storage:link
```

## Development

This project follows Laravel conventions and aims to keep application logic separated through:

* Controllers
* Form Requests
* Eloquent Models
* Policies
* Observers
* Enums
* Blade Components

The application is designed to remain simple and maintainable without unnecessary packages or complexity.

## License

This project is for personal/educational development and can be modified as needed.

```

One small recommendation: **don't put your actual database name, username, password, email credentials, or API keys in the README.** Use placeholders as above.
```

