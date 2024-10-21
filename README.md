This guide is a great step-by-step resource for setting up a PHP development environment that’s both powerful and flexible. It walks you through installing Composer (to manage PHP dependencies), Docker (to run your app in containers), and Visual Studio Code (for writing and debugging your code). It’s designed to make sure everything runs smoothly on any machine.

## 1. **Requirements**

In this section, you'll set up the necessary tools to install and configure your development environment: Composer (PHP dependency manager), Docker (for containerization), and Visual Studio Code (your code editor).

### 1.1 Composer Install

- Download the latest Thread Safe ZIP version of PHP [here](https://windows.php.net/download/).
- Extract the files to `C:\php`.
- Download and install the latest version of Composer from [here](https://getcomposer.org/download/).
- Open the command prompt (`cmd`) and type `composer -v` to verify that Composer has been installed successfully.

### 1.2 Docker Install

Docker allows you to create containerized environments for your application, ensuring that it runs the same way in different environments.

- Install the latest version of Docker from [here](https://docs.docker.com/get-started/get-docker).
- Open Docker Desktop and verify that it is running.
- Verify Docker setup by running:
```bash
docker info
```

### 1.3 Visual Studio Code Install

Visual Studio Code will be used for writing and debugging your code.

- Install the latest version of Visual Studio Code from [here](https://code.visualstudio.com/).

---

## 2. **Initialize the Project**

Here, you will initialize a new project by setting up a basic structure and installing dependencies via Composer.

### 2.1 Create a Project Directory

Create a directory for your project and navigate into it:
```bash
mkdir myapp && cd myapp
```

### 2.2 Create a Composer File

This initializes a Composer project and creates a `composer.json` file for managing dependencies.
```bash
composer init -y
```

### 2.3 Create a `src` Directory

Create a directory named `src` to store your source code:
```bash
mkdir src
```

### 2.4 Create a `public` Directory Inside `src`

Create a `public` directory within `src` to hold publicly accessible files:
```bash
mkdir src/public
```

---

## 3. **Create Source Files**

You will now create essential PHP and configuration files to run your application.

### 3.1 Create `index.php` in `src/public`

The `index.php` file serves as the entry point for your application. Add the following code to display PHP information and handle errors.
```php
<?php

namespace Example;

use Exception;
use TypeError;
use Throwable;
use ErrorException;
use Error;

# Custom error handler for warnings and errors
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}, E_ERROR | E_WARNING);

try {
    # Path to Composer's autoloader relative to public/index.php
    $composerPath = sprintf("%s/vendor/autoload.php", dirname(__DIR__, 2));

    # Verify Composer autoload file exists
    if (!file_exists($composerPath)) {
        throw new Exception("Please run `composer install` in the root directory");
    }

    require_once($composerPath);

    phpinfo(); // Displays PHP info
} catch (Exception $ex) {
    error_log("Exception: " . $ex->getMessage());
    echo "An error occurred. Please check the logs.";
}
```

### 3.2 Create `robots.txt` in `src/public`

The `robots.txt` file will tell search engines not to index this application:
```txt
User-agent: *
Disallow: /
```

### 3.3 Create `example.php` in `src`

The `example.php` file is a placeholder to allow Composer to work. This file can be removed later:
```php
<?php

namespace Src;

class Example {}
```

---

## 4. **Docker Setup**

Docker will be used to run your PHP application and other services (MySQL, phpMyAdmin) in a containerized environment.

### 4.1 Create a `development` Folder

Create a folder for Docker-related files:
```bash
mkdir development
```

### 4.2 Create a Dockerfile

In the `development` folder, create a `Dockerfile` to set up a PHP environment with MySQL and Xdebug.
```dockerfile
FROM php:8.2-fpm

# Install required system packages and Xdebug
RUN apt-get update && apt-get install -y zip unzip wget     && pecl install xdebug && docker-php-ext-enable xdebug

# Install MySQLi, PDO, and PDO_MYSQL extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql
```

### 4.3 Create a `docker-compose.yml` File

This file defines services (Nginx, PHP, MySQL, and phpMyAdmin) to run in containers.
```yaml
services:
  nginx:
    image: nginx:latest
    container_name: nginx_server
    volumes:
      - ./nginx.conf:/etc/nginx/conf.d/default.conf
    ports:
      - "${APACHE_PORT}:80"
    depends_on:
      - php

  php:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: php_fpm
    volumes:
      - ../src:/var/www/html/src
      - ../vendor:/var/www/html/vendor
      - ./logs:/var/www/html/logs
      - ./php.ini:/usr/local/etc/php/php.ini
      - ./xdebug.ini:/usr/local/etc/php/conf.d/xdebug.ini
    expose:
      - "9000"

  mysql:
    image: mysql:8.0
    container_name: mysql_db
    environment:
      MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
      MYSQL_DATABASE: ${MYSQL_DATABASE}
      MYSQL_USER: ${MYSQL_USER}
      MYSQL_PASSWORD: ${MYSQL_PASSWORD}
    volumes:
      - db_data:/var/lib/mysql
    ports:
      - "${MYSQL_PORT}:3306"

  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    container_name: phpmyadmin
    environment:
      PMA_HOST: mysql
      MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
    ports:
      - "${PMA_PORT}:80"
volumes:
  db_data:
```

---

## 5. **Nginx, Xdebug, and PHP Setup**

Here, you will configure Nginx as the web server and set up Xdebug for debugging.

### 5.1 Create `nginx.conf`

This file configures Nginx to handle PHP requests and secure sensitive files:
```nginx
server {
    listen 80;
    server_name localhost;
    root /var/www/html/src/public;
    index index.php;

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass php_fpm:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.(ht|git|env|json|lock|md) {
        deny all;
    }
}
```

### 5.2 Create `php.ini`

This file configures PHP settings like error reporting and file size limits:
```ini
upload_max_filesize=100M
post_max_size=100M
display_errors=On
error_reporting=E_ALL
log_errors=On
error_log=/var/www/html/logs/php_error.log
```

### 5.3 Create `xdebug.ini`

This file sets up Xdebug for remote debugging:
```ini
zend_extension=xdebug
xdebug.mode=debug
xdebug.start_with_request=yes
xdebug.client_host=host.docker.internal
xdebug.client_port=9003
xdebug.log=/var/www/html/logs/xdebug.log
```

---

## 6. **VS Code Setup**

Configure Visual Studio Code for debugging PHP with Xdebug.

### 6.1 Create `.vscode` Directory

Create a `.vscode` folder for configuration files:
```bash
mkdir .vscode
```

### 6.2 Create `launch.json`

This file configures VS Code to use Xdebug:
```json
{
  "version": "0.2.0",
  "configurations": [
    {
      "name": "Listen for XDebug",
      "type": "php",
      "request": "launch",
      "port": 9003,
      "pathMappings": {
        "/var/www/html": "${workspaceFolder}/src"
      },
      "log": true
    }
  ]
}
```

---

## 7. **Running the Project**

### 7.1 Run Composer

Install project dependencies using Composer:
```bash
composer install
```

### 7.2 Run Docker

Open your terminal and run the Docker task to build and start the server:
```bash
docker-compose up --build
```

---

## 8. **Links**

- Access the server at: [localhost:8080](localhost:8080)
- Access phpMyAdmin at: [localhost:8081](localhost:8081) (The MySQL credentials are in the `.env` file).
