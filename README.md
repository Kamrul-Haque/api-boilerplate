# API Boilerplate

A Rest API backend development boilerplate using Laravel framework.

## Features Included

- JWT token based Authentication using [Laravel Sanctum](https://laravel.com/docs/12.x/sanctum).
- API Documentation using [Scramble](https://scramble.dedoc.co/).
- CRUD Generation Helper using Action/Command Pattern
  using [action-crud-helper](https://packagist.org/packages/kamrul-haque/action-crud-helper).
- Efficient PHP Backed Enum Handling using Cast & Trait.
- Auto Created by User Log using Trait and Database Table.
- Global Data Time Format and Timezone Conversion using Cast & Service Class (Facade Pattern).
- Auto Slug Generation using Trait
- Duration calculation from Datetime or Timestamps using Trait
- Custom Format Unique ID generation using Service Class
- Realtime Route Prefix Lists using Service Class

## Project Setup

- clone the repository in your local machine:

```
git clone https://github.com/Kamrul-Haque/api-boilerplate.git
```

- install PHP dependencies via [composer](https://getcomposer.org/):

```
composer install
```

- copy .env.example file and create a new .env file using the terminal:

```
cp .env.example .env
```

- generate an application key:

```
php artisan key:generate
```

- set project configurations in `.env` file.
- create a MySQL database named `pr_school` or change the name in `.env` later.
- create tables in the database and seed default data:

```
php artisan migrate --seed
```

- use [Herd](https://herd.laravel.com/windows), [Valet](https://laravel.com/docs/12.x/valet) etc. or `php artisan serve`
  command to use run the application in localhost.

*Note: the above steps are for running the application in local environment. For deploying the application in server,
please refer to the deployment guide in the [deployment guideline](docs/deployment-guideline.md)*.

## Project Structure

```text
pr-sms-backend/
├── app/
│   ├── Actions/         # Business logic implementing the Command pattern
│   ├── Casts/           # Custom Eloquent attribute casting
│   ├── Enums/           # PHP backed enums for status, roles, etc.
│   ├── Events/          # Application event classes
│   ├── Exceptions/      # Custom exception classes
│   ├── Http/            # Controllers, Middleware, Requests, Resources
│   ├── Imports/         # Excel/CSV import logic
│   ├── Jobs/            # Queued jobs for async processing
│   ├── Listeners/       # Event listeners
│   ├── Mail/            # Mailable classes
│   ├── Models/          # Eloquent models
│   ├── Notifications/   # Notification classes (Email, Database, etc.)
│   ├── Providers/       # Service providers
│   ├── Rules/           # Custom validation rules
│   ├── Services/        # Reusable business logic and integrations
│   └── Traits/          # Reusable traits for models and classes
├── bootstrap/           # Framework bootstrapping and exception handling
├── config/              # Application configuration files
├── database/
│   ├── factories/       # Model factories for testing data
│   ├── migrations/      # Database schema migrations
│   └── seeders/         # Database seeders
├── docs/                # Technical documentation and feature specifications
├── lang/                # Localization files (en, ja)
├── public/              # Web server root and static assets
├── resources/
│   ├── css/             # Stylesheets (Tailwind)
│   ├── js/              # JavaScript files
│   └── views/           # Blade templates (mail, layouts)
├── routes/              # Application route definitions
├── storage/             # Logs, compiled views, and file storage
├── stubs/               # Custom code generation stubs
├── tests/
│   ├── Feature/         # Feature tests (HTTP requests, database)
│   └── Unit/            # Unit tests (isolated logic)
└── vendor/              # Composer dependencies
```
