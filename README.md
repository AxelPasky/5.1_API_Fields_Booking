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
- MySQL or compatible database

### 1. Clone the Repository

```bash
git clone https://github.com/AxelPasky/5.1_API_Fields_Booking.git
cd 5.1_API_Fields_Booking
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```
Edit `.env` and set your database credentials.

### 4. Database Migration and Seeding

```bash
php artisan migrate:fresh --seed
```
This creates demo users and fields.

### 5. Storage Link

```bash
php artisan storage:link
```

### 6. Run the Server

```bash
php artisan serve
```
The API will be available at `http://127.0.0.1:8000`.

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

1. **Import the Collection**
   - Download from [http://localhost:8000/docs.postman](http://localhost:8000/docs.postman)
   - In Postman, click "Import" and select the downloaded file.

2. **Set the Base URL**
   - Make sure the `baseUrl` variable in Postman is set to `http://localhost:8000` (or your server address).

3. **Authentication Flow**
   - Register a user via `POST /api/register` (or use demo credentials).
   - Log in via `POST /api/login` to obtain an `access_token`.
   - For all protected endpoints, add this header:
     ```
     Authorization: Bearer {access_token}
     ```
   - In Postman, you can set this as a "Bearer Token" in the Authorization tab or as a header.

4. **Try the Endpoints**
   - Use the provided demo credentials or register a new user.
   - Test all endpoints: fields, bookings, statistics, etc.

5. **Troubleshooting**
   - `401 Unauthorized`: Make sure you included the correct token.
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

