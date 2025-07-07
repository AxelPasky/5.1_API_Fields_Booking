# Authenticating requests

To authenticate requests, you must first **register** a new user by making a POST request to the `/api/register` endpoint. Once registered, you can **log in** by making a POST request to the `/api/login` endpoint. The login response will include your access token.

For all authenticated requests, include an **`Authorization`** header with the value **`Bearer {TOKEN}`** (replace `{TOKEN}` with your actual access token).

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

---

## Example authentication flow

1. **Register a new user**

```bash
curl --request POST \
  --url http://localhost:8000/api/register \
  --header 'Content-Type: application/json' \
  --data '{
    "name": "John Doe",
    "email": "john.doe@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

2. **Log in to get your token**

```bash
curl --request POST \
  --url http://localhost:8000/api/login \
  --header 'Content-Type: application/json' \
  --data '{
    "email": "john.doe@example.com",
    "password": "password123"
  }'
```

The response will include an `access_token`.

3. **Make an authenticated request**

```bash
curl --request GET \
  --url http://localhost:8000/api/user \
  --header 'Authorization: Bearer {TOKEN}'
```

Replace `{TOKEN}` with the value of your `access_token` from the login response.
