# Secure Guestbook - a secure PHP app design principles technology demo by Gleb Andreev

## About

Secure Guestbook is a web application, written in PHP. Data persistence is implemented with MySQL.

This demo was written to demonstrate my approach in the situation "You've got nothing except PHP and MySQL. You need to write a secure app. Show what you can."

This could be also a challenge to you: deploy this app, torture it, hack it, prove there are app-level flaws. I'm 95% sure there are not. Show me I'm wrong.

## Important note

This app is not about reinventing the wheel. Yes, there are many good opensource frameworks and libs.

This is more about the challenge of understanding the inner nature of the frameworks we use every day, and how it affects the security, extendability and maintainability of the apps we create.

## The task

The task is to write an demo app in pure PHP with no third-party libs.

The app is a guestbook with the following opts: user signup, login and logout, message create, edit, delete, add reply.

### Discussion

The task is to write an demo app in pure PHP with no third-party libs to demonstrate that the right design is the best way to create secure applications.

Despite the obvious tempation to write a simple app in an oversimplified way, there is an even more obvious flaw of that approach: you loose control over the app and drown in the limitations of that alusive simplicity.

So yes, it worth to write a simple but functional MVC framework, than to write 10 non-extendable PHP scripts, mixed with HTML.

### Concepts and workflow

A boilerplate MVC-like environment was written from scratch for this demo.
Interface in pure unstyled ultra-simple HTML.

All processing is done around a lifecycle of a Request object which is being mutated during the following sequence:

1. Ingestion of a request
2. Authentication
3. Routing
4. Data preparation and injection into request by controller
5. HTML rendering in the View subsystem
6. Sending the response

As the challenge was primarily about writing an app, as secure as it even possible, the following list of mesaures taken to enforce the app:

1. Single point-of-entry MVC-like architecture
2. Isolated runtime scope, nothing is global
3. Errors are handled by pass-through exceptions
4. Indirect request parameters ingestion after sanity check
5. Full request parameters sanitization
6. Proactive reaction to overflowing attempts in coordiation with local Fail2Ban
7. Completely static view templates
8. Semi-transparent CSRF protection for POST requests
9. Token-based user authentication
10. User-tokens are bind to IP address
11. Semi-paranoid built-in Fail2Ban implementation, handling both errors and requests tempo
12. MySQL parameters are taken from env vars
13. XSS and content restrictions headers are being sent

### Fail2Ban

To limit the number of break-in attempts the local simplified Fail2Ban is buil-in.

The main features are:

1. Ban for 1 hour by IP, regardless the user.
2. Ban the user for 1 hour, regardless the IP.

Ban thresholds for errors and requests quantities are configurable.

### Testing

Very basic amount of testing provided for backbone subsystems.

### Configure local environment in order to run the application

Prerequisites: PHP 7 (pdo_mysql, openssl),composer (for phpunit only), http server

1. Set environment variables:
```
WEBAPP_BASEDIR - should point to src/ folder
WEBAPP_STORAGE_MYSQL_HOST
WEBAPP_STORAGE_MYSQL_USER
WEBAPP_STORAGE_MYSQL_PASSWORD
WEBAPP_STORAGE_MYSQL_DB
```
2. Config your web server's document root to point to `src/public` with default document index.php
3. Access the application in browser

### Unit tests

1. Run phpunit in the root project directory, ensure all tests passed

```phpunit --bootstrap tests/bootstrap.php tests/ApplicationTest.php```

----

### Notes

1. Error reporting is disabled in /src/public/index.php
2. See /src/classes/Configuration.php to learn about current config
3. Fail2Ban config is relatively paranoid, adjust `getErrorsTillBan` and `getRequestsTillBan` if necessary
