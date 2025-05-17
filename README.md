# Indonet Analytics Hub Platform

<p align="center">
<img src="https://img.shields.io/badge/Laravel-10.x-red" alt="Laravel Version">
<img src="https://img.shields.io/badge/PHP-8.1+-blue" alt="PHP Version">
<img src="https://img.shields.io/badge/License-MIT-green" alt="License">
</p>

## Project Overview

The Indonet Analytics Hub Platform is a role-based web application with a Laravel backend that provides user management, role-based access control, terms & conditions management, menu/content management, system notifications, and email template functionality.

## Backend Features

### Authentication System
- JWT/Sanctum authentication
- User registration and login
- Password reset and change functionality
- Terms & conditions acceptance tracking

### User Management
- Complete CRUD operations for user accounts
- Role assignment and management
- User profile management

### Role-Based Access Control
- Role management with permission assignment
- Fine-grained permissions by module
- Permission checking in all controllers

### Content Management
- Support for multiple content types
- CRUD operations for content
- Publishing workflow with draft/published states
- Meta information for SEO

### Menu Management
- Hierarchical menu structure
- Permission-based menu visibility
- Drag-and-drop reordering capability
- Usage analytics tracking for menu interactions
- Popular menu items reporting

### Terms and Conditions
- Version tracking for terms
- Active terms management
- User acceptance recording

### Notification System
- System-wide and user-specific notifications
- Read/unread status tracking
- Expiration management

### Email Templates
- Customizable email templates
- Token-based replacement for variables
- HTML and plain text versions

## Frontend Features

### Modern UI with Vuetify
- Responsive design using Vuetify 3 components
- Dark mode support
- Custom theming and branding

### State Management
- Pinia stores for centralized state management
- Modular store design for scalability

### Dynamic Navigation
- Multi-level dropdown menu support
- Permission-based menu rendering
- Smooth GSAP animations for transitions
- Menu usage analytics with popularity tracking

### User Interface
- Profile management with avatar support
- Form validation and error handling
- Loading indicators and progress feedback

## Database Structure

The platform uses the following core tables:
- users
- roles
- permissions
- role_user (pivot)
- permission_role (pivot)
- terms_and_conditions
- system_notifications
- email_templates
- content_management
- menu_items

## Getting Started

### Prerequisites
- PHP 8.1 or higher
- MySQL/MariaDB
- Composer

### Installation

1. Clone the repository
```
git clone https://github.com/your-org/indonet-analytics-hub.git
cd indonet-analytics-hub
```

2. Install dependencies
```
composer install
```

3. Copy .env.example to .env and configure your database
```
cp .env.example .env
```

4. Generate application key
```
php artisan key:generate
```

5. Run migrations and seed the database
```
php artisan migrate --seed
```

6. Start the development server
```
php artisan serve
```

### Default Users

After seeding, the following users will be available:

1. Administrator:
   - Email: admin@indonet.com
   - Password: Admin@123

2. Regular User:
   - Email: user@indonet.com
   - Password: User@123

## API Documentation

The platform offers a comprehensive RESTful API:

### Authentication
- POST /api/auth/login
- POST /api/auth/register
- POST /api/auth/forgot-password
- POST /api/auth/reset-password
- POST /api/auth/logout
- POST /api/auth/change-password
- POST /api/auth/accept-terms

### User Management
- GET /api/users
- POST /api/users
- GET /api/users/{id}
- PUT /api/users/{id}
- DELETE /api/users/{id}
- PUT /api/user/profile

### Role Management
- GET /api/roles
- POST /api/roles
- GET /api/roles/{id}
- PUT /api/roles/{id}
- DELETE /api/roles/{id}
- POST /api/roles/{role}/permissions

### Permissions
- GET /api/permissions
- GET /api/permissions/{id}
- GET /api/permissions/by-module

### Terms and Conditions
- GET /api/terms
- POST /api/terms
- GET /api/terms/{id}
- PUT /api/terms/{id}
- DELETE /api/terms/{id}
- GET /api/terms/active
- GET /api/terms/user-acceptance

### Notifications
- GET /api/notifications
- POST /api/notifications
- GET /api/notifications/{id}
- PUT /api/notifications/{id}
- DELETE /api/notifications/{id}
- POST /api/notifications/{notification}/mark-read
- POST /api/notifications/mark-all-read
- GET /api/notifications/unread-count

### Email Templates
- GET /api/email-templates
- POST /api/email-templates
- GET /api/email-templates/{id}
- PUT /api/email-templates/{id}
- DELETE /api/email-templates/{id}

### Content Management
- GET /api/content
- POST /api/content
- GET /api/content/{id}
- PUT /api/content/{id}
- DELETE /api/content/{id}
- GET /api/content/by-slug/{slug}
- GET /api/content/by-type/{type}

### Menu Management
- GET /api/menu-items
- POST /api/menu-items
- GET /api/menu-items/{id}
- PUT /api/menu-items/{id}
- DELETE /api/menu-items/{id}
- GET /api/menu/structure
- POST /api/menu/track-click
- GET /api/menu/popular
- POST /api/menu-items/reorder

## License

The Indonet Analytics Hub Platform is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
