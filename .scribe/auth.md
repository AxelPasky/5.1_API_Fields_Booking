# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer Bearer {TOKEN}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

You can retrieve your token by making a POST request to the `/api/login` endpoint.
