
# Loan Management API

## Features
- Admin login with JWT authentication
- Modular MVC structure

## Setup
1. Run `composer install`.
2. Set up the database in `.env`.
3. Use `php -S localhost:8000` to start the server.

Enjoy building!

## .htaccess for shared hosting

<IfModule mod_headers.c>
    Header Set Access-Control-Allow-Origin "*"
    Header Set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
    Header Set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With"
    Header Set Access-Control-Allow-Credentials "true"
</IfModule>
