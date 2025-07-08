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

## Project Structure & Approach

- **Controllers:** Orchestrate requests and responses; business logic is in services/policies.
- **Validation:** Handled via Form Requests or inline validation.
- **Authorization:** Managed via policies and explicit checks in controllers.
- **Testing:** Test-Driven Development (TDD) for all features and edge cases.
- **Documentation:** Written with Scribe, customized for clarity and real-world usage.

---

## API Resources

- **Fields:** View all fields, see details, check availability (all users). Admins can create, update, delete.
- **Bookings:** Users can create, view, update, and delete their own bookings. Price and overlap checks included.
- **Statistics:** Admin-only endpoints for total revenue and field performance.

---

## Installation and Setup

### Prerequisites

- PHP >= 8.2
- Composer
- **MySQL** or compatible database (SQLite is NOT recommended, see note below)
- Ensure all required PHP extensions for Laravel are enabled (e.g., PDO, Mbstring, XML, Ctype). If `composer install` fails, checking your enabled extensions is a good first step. 
(should be sodium extension on php.ini file. Raw 958 +- ;)

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

Create a new MySQL database (e.g. `fields_booking`) using your preferred tool (phpMyAdmin, MySQL Workbench, CLI, etc.).

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
DB_DATABASE=fields_booking
DB_USERNAME=your_mysql_user
DB_PASSWORD=your_mysql_password
```

> **Note:**
> This project is designed to work with MySQL.
> Using SQLite may cause issues with OAuth (Passport) tables and is not recommended for development or production.

### 5. Database and Passport Setup

This is a critical step. We will create the database tables and the necessary OAuth clients for authentication.

First, run the migrations and the seeders. This command will create the database structure and populate it with demo data, including the default Passport clients.

```bash
php artisan migrate:fresh --seed
```

### 6. Passport Client Configuration (.env)

For the API to issue access tokens correctly (especially for login), you must copy the credentials of the OAuth clients from your database into your `.env` file.

**How to find the credentials:**

1.  Open your MySQL database (with phpMyAdmin, etc.) and look at the `oauth_clients` table.
2.  You should see two rows: one named "Laravel Personal Access Client" and another named "Laravel Password Grant Client".
3.  Copy the `id` and `secret` values from **both** rows.

Now, update the `.env` file with these four values:

```properties
# .env file

# From the "Personal Access Client" row
PASSPORT_PERSONAL_ACCESS_CLIENT_ID=...
PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=...

# From the "Password Grant Client" row
PASSPORT_PASSWORD_GRANT_CLIENT_ID=...
PASSPORT_PASSWORD_GRANT_CLIENT_SECRET=...
```

> ### **Important Note: What if the "Password Grant Client" is missing?**
>
> If you only see one row in the `oauth_clients` table after seeding, the "Password Grant Client" (essential for user login) was not created.
>
> **To fix this, run this command in your terminal:**
>
> ```bash
> php artisan passport:client --password
> ```
>
> This will generate the missing client and give you its `Client ID` and `Client Secret`. Copy these new credentials into the `PASSPORT_PASSWORD_GRANT_CLIENT_ID` and `PASSPORT_PASSWORD_GRANT_CLIENT_SECRET` variables in your `.env` file.

### 7. Storage Link

```bash
php artisan storage:link
```

### 8. Run the Server & View Documentation

```bash
php artisan serve
```

The API server is now running. Access the interactive API documentation in your browser to start testing:

**[http://127.0.0.1:8000/docs](http://127.0.0.1:8000/docs)**

---

## Demo Credentials

Seeder creates these users:

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

The docs include:
- Endpoint descriptions, parameters, and example requests/responses
- Authentication instructions
- Grouped endpoints by resource

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
    - In Postman, you can set this as a "Bearer Token" in the Authorization tab or as a header.

4.  **Try the Endpoints**
    - Use the provided demo credentials or register a new user.
    - Test all endpoints: fields, bookings, statistics, etc.

5.  **Troubleshooting**
    - `401 Unauthorized`: Make sure you included the correct token and updated the Passport client details in your `.env` file.
    - `422 Unprocessable Entity`: Check required fields and validation rules in the docs.

---

## Running Tests

All features are covered by automated tests.

```bash
php artisan test
```

---

## Customization & Extending

- **Add new endpoints:** Follow the existing structure (controller, validation, policy, test, docblock).
- **Update documentation:** Edit docblocks in controllers and re-run `php artisan scribe:generate`.
- **Change roles/permissions:** Update policies and seeders as needed.

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

**Note:**
This project is a pure API and does **not** require Node.js, NPM, or any frontend asset build steps.
You can safely ignore or delete any Node.js-related files.
