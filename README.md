## Project Overview
This is the PHP backend for my application.

## Requirements
- PHP 8.3 or higher
- MySQL database

## Setup
1. Install dependencies using Composer:
   ```
   composer install
   ```
2. Copy `.env.example` to `.env` and configure your database settings:
   ```
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=task
   DB_USERNAME=root
   DB_PASSWORD=password
   ```

## Dependencies
- vlucas/phpdotenv: ^5.6
- laminas/laminas-diactoros: ^3.3
- laminas/laminas-httphandlerrunner: ^2.10

## Project Structure
- `src/`: Contains the application source code
- `vendor/`: Composer dependencies (not tracked in Git)

## Autoloading
PSR-4 autoloading is configured for the `App\` namespace, pointing to the `src/` directory.

## Environment Variables
The project uses `vlucas/phpdotenv` to manage environment variables. Ensure all necessary variables are set in your `.env` file.

## Additional Notes
- The backend is configured to use PHP 8.3.
- Composer is set to sort packages.