# Field Booking System API

A modern RESTful API built with Laravel for managing and booking sports fields.

This project is a secure, fully tested API with role-based access, advanced business logic, and comprehensive documentation.

---

## Key Features

- **RESTful API:** Clean, resource-oriented endpoints for all operations.
- **User Authentication:** Secure registration and login using Laravel Passport (OAuth2).
- **Role-Based Access Control:** Two roles: `Admin` (full management) and `User` (personal bookings only).
- **Advanced Booking Logic:** Prevents overlapping bookings, calculates prices based on field rates and duration.
- **Statistics:** Admin endpoints for revenue and field performance.
- **Comprehensive Testing:** All endpoints are covered by feature tests (TDD).
- **API Documentation:** Auto-generated with Scribe, including example requests/responses and Postman/OpenAPI export.

---

## Installation and Setup

### Prerequisites

- PHP 8.2.x (PHP 8.4 may cause compatibility issues)
- Composer
- **MySQL** or compatible database
- Required PHP extensions:
  - PDO PHP Extension
  - Mbstring PHP Extension
  - Tokenizer PHP Extension
  - XML PHP Extension
  - Ctype PHP Extension
  - JSON PHP Extension
  - BCMath PHP Extension
  - **Sodium Extension** (critical - enable in `php.ini`)

### 1. Clone the Repository

```bash
git clone https://github.com/AxelPasky/5.1_API_Fields_Booking.git
cd 5.1_API_Fields_Booking
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Create the Database

Create a new MySQL database (e.g. `booking_fields`) using your preferred tool (phpMyAdmin, MySQL Workbench, CLI, etc.).

### 4. Environment Configuration

Copy the example environment file and generate the app key:

```bash
cp .env.example .env
php artisan key:generate
```

Edit the `.env` file and set your database connection details:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=booking_fields
DB_USERNAME=your_mysql_user
DB_PASSWORD=your_mysql_password
```

### 5. Passport Installation (Important!)

This step is **crucial** and must be done **before** running migrations. It will create the necessary OAuth clients and migrations.

```bash
php artisan passport:install
```

You will see output similar to:

```
Personal access client created successfully.
Client ID: 1
Client secret: xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
Password grant client created successfully.
Client ID: 2
Client secret: xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

Copy these values to your `.env` file:

```
PASSPORT_PERSONAL_ACCESS_CLIENT_ID=1
PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=first-client-secret-from-output
PASSPORT_PASSWORD_GRANT_CLIENT_ID=2
PASSPORT_PASSWORD_GRANT_CLIENT_SECRET=second-client-secret-from-output
```

> **Note:** If you don't see the "Password grant client", run `php artisan passport:client --password` and copy the new credentials to the `PASSPORT_PASSWORD_GRANT_CLIENT_*` variables.

### 6. Run Migrations and Seeders

Now that Passport is set up, run the migrations and seed the database:

```bash
php artisan migrate --seed
```

### 7. Storage Link

```bash
php artisan storage:link
```

### 8. Run the Server & View Documentation

```bash
php artisan serve
```

The API server is now running. Access the interactive API documentation in your browser:

**[http://127.0.0.1:8000/docs](http://127.0.0.1:8000/docs)**

---

## Online/Production Deployment

This API can be deployed in several ways. Here are the main options:

### Option 1: Deploying to Railway (Recommended)

[Railway](https://railway.app/) is a PaaS platform that makes deploying Laravel applications straightforward. Since this project is already configured for it, this is the easiest path.

1.  **Create a Railway Account** and link your GitHub repository.
2.  **Create a New Project** by selecting this repository.
3.  **Add a MySQL Service** from the "New Service" menu. Railway will provide the database connection details.
4.  **Configure Environment Variables** in the Railway project dashboard under the "Variables" tab. Use the following, letting Railway inject its own database variables:
    ```
    APP_ENV=production
    APP_DEBUG=false
    APP_URL=https://your-railway-domain.up.railway.app

    # Railway will provide these automatically
    DB_CONNECTION=mysql
    DB_HOST=${{RAILWAY_MYSQL_HOST}}
    DB_PORT=${{RAILWAY_MYSQL_PORT}}
    DB_DATABASE=${{RAILWAY_MYSQL_DATABASE}}
    DB_USERNAME=${{RAILWAY_MYSQL_USERNAME}}
    DB_PASSWORD=${{RAILWAY_MYSQL_PASSWORD}}

    # You still need to set up Passport variables for production
    PASSPORT_PERSONAL_ACCESS_CLIENT_ID=...
    PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=...
    PASSPORT_PASSWORD_GRANT_CLIENT_ID=...
    PASSPORT_PASSWORD_GRANT_CLIENT_SECRET=...
    ```
5.  **Automatic Deploys:** Railway will detect the `Dockerfile` in the repository and use it to build and deploy the application automatically on every push to the main branch.

### Option 2: Manual Deployment with Docker

The `Dockerfile` included in this project allows you to build a production-ready container image. This is useful if you want to host the API on your own server (e.g., a VPS) using Docker.

1.  **Build the Docker image:**
    ```bash
    docker build -t fields-booking-api .
    ```
2.  **Run the container:**
    You need to connect it to an existing database and provide all necessary environment variables.
    ```bash
    docker run -p 8080:8080 \
      -e APP_ENV=production \
      -e APP_DEBUG=false \
      -e DB_HOST=your_database_host \
      -e DB_USERNAME=your_db_user \
      -e DB_PASSWORD=your_db_password \
      fields-booking-api
    ```

### Option 3: Traditional Deployment (Without Docker)

You can also deploy this project to a traditional server (like a VPS or shared hosting) without using Docker.

1.  Clone the repository onto your server.
2.  Follow the same steps as the [Local Installation](#installation-and-setup), but use your production database credentials in the `.env` file.
3.  Configure your web server (e.g., Apache or Nginx) to point its document root to the `public` directory of the project.
4.  Ensure file permissions are set correctly for the `storage` and `bootstrap/cache` directories.

---

### Current Production API

Production API documentation:

```
https://api-booking-fields.up.railway.app/docs
```

---

## Demo Credentials

Seeders create these users:

**Admin**
- Email: `admin@example.com`
- Password: `password`

**User**
- Email: `user@example.com`
- Password: `password`

---

## API Documentation

Full, interactive documentation is auto-generated with [Scribe](https://scribe.knuckles.wtf/):

- **View in browser:** [http://localhost:8000/docs](http://localhost:8000/docs)
- **Download Postman collection:** [http://localhost:8000/docs.postman](http://localhost:8000/docs.postman)
- **OpenAPI spec:** [http://localhost:8000/docs.openapi](http://localhost:8000/docs.openapi)

---

## How to Test the API with Postman

1.  **Import the Collection**
    - Download from [http://localhost:8000/docs.postman](http://localhost:8000/docs.postman)
    - In Postman, click "Import" and select the downloaded file.

2.  **Set the Base URL**
    - Make sure the `baseUrl` variable in Postman is set to `http://localhost:8000` (or your server address).

3.  **Authentication Flow**
    - Register a user via `POST /api/register` (or use demo credentials).
    - Log in via `POST /api/login` to obtain an `access_token`.
    - For all protected endpoints, add this header:
      ```
      Authorization: Bearer {access_token}
      ```

4.  **Try the Endpoints**
    - Test all endpoints: fields, bookings, statistics, etc.

5.  **Troubleshooting**
    - `401 Unauthorized`: Make sure you included the correct token and updated the Passport client details in your `.env` file.
    - `422 Unprocessable Entity`: Check required fields and validation rules in the docs.

---

## Common Issues & Solutions

1. **Failed to listen on 127.0.0.1:8000**: Another process is using the port. Try `php artisan serve --port=8080`.

2. **PHP Version Compatibility**: This project requires PHP 8.2.x. If you have PHP 8.4.x installed, you may encounter issues.

3. **Sodium Extension Missing**: Enable the sodium extension in your `php.ini` file.

---

## Running Tests

All features are covered by automated tests.

```bash
php artisan test
```

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

**Note:**
This project is a pure API and does **not** require Node.js, NPM, or any frontend asset build steps.
