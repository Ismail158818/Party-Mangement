# Party Management System

## Overview
A comprehensive party management system built with Laravel that helps users organize, manage, and participate in events. The system includes features for event management, user interactions, comments, and PayPal payment integration.

## Key Features
- **User Authentication & Authorization**
  - Secure registration and login system
  - Role-based access control (Admin/User)
  - Password reset functionality
  - Email verification

- **Event Management**
  - Create, view, update, and delete events
  - Event categories and tags
  - Event search and filtering
  - Event capacity management
  - Event status tracking (Upcoming/Ongoing/Completed)
  - Event location mapping
  - Event date and time management

- **User Management**
  - User profiles with customizable information
  - User search and filtering
  - User event participation tracking
  - User activity history
  - User preferences and settings
  - Profile picture management

- **Comment System**
  - Real-time comments on events
  - Comment moderation
  - Nested replies
  - Comment notifications
  - Comment editing and deletion

- **Payment Integration**
  - Secure PayPal payment processing
  - Multiple payment methods
  - Invoice generation
  - Payment history tracking
  - Refund management
  - Payment status notifications

- **Additional Features**
  - Responsive design for all devices
  - Real-time notifications
  - Event reminders
  - Social media sharing
  - Export functionality for reports
  - Multi-language support
  - Dark/Light theme

## Technical Stack
- **Backend:**
  - Laravel Framework
  - PHP 8.x
  - RESTful API Architecture
  - Laravel Sanctum for Authentication
  - Rate Limiting
  - Caching System

- **Frontend:**
  - Blade Templates
  - Tailwind CSS
  - JavaScript/jQuery
  - Responsive Design
  - Progressive Web App (PWA)

- **Database:**
  - MySQL
  - Database Migrations
  - Seeding
  - Backup System

- **Security:**
  - CSRF Protection
  - XSS Prevention
  - SQL Injection Protection
  - Input Validation
  - Secure Password Hashing

- **DevOps:**
  - Git Version Control
  - Composer Package Management
  - NPM Package Management
  - Environment Configuration

## Installation
1. Clone the repository
```bash
git clone https://github.com/Ismail158818/Party-Mangement.git
```

2. Install dependencies
```bash
composer install
npm install
```

3. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

4. Set up database
```bash
php artisan migrate
php artisan db:seed
```

5. Start the server
```bash
php artisan serve
```

## Requirements
- PHP >= 8.0
- Composer
- Node.js & NPM
- MySQL >= 5.7
- Web Server (Apache/Nginx)

## Contributing
We welcome contributions! Please follow these steps:
1. Fork the project
2. Create a feature branch
3. Submit a Pull Request

## License
This project is licensed under the MIT License. See the LICENSE file for more information. 