# StyleHub API

Base URL: /api/

Authentication
- Admin-only endpoints require an authenticated session (admin login) and CSRF token for state-changing operations.

Products

List products
GET products.php

Query params:
- page (int, default 1)
- limit (int, max 50)
- search (string)
- category (int)
- gender (femme|homme|unisexe)
- brand (string)
- color (string)
- size (string)
- min_price (float)
- max_price (float)
- sort (price_asc|price_desc|name_asc|popularity|rating)
- featured (1|0)

Response:
{
  "products": [ { ...product } ],
  "total": 123,
  "pages": 11,
  "current_page": 1,
  "per_page": 12
}

Get product by id
GET products.php/{id}

Response:
{ "product": { ... } }

Create product (admin)
POST products.php
Form fields:
- name, description, price, sale_price?, sku, stock, category_id?, brand?, color?, size?, material?, gender, season, image_url?, gallery[]?, tags[]?, featured (on|1), status
- csrf_token

Update product (admin)
PUT products.php/{id}
JSON body fields: same as create + csrf_token

Delete product (admin)
DELETE products.php/{id}

Search suggestions
GET search.php?q=robe&limit=8
Response:
{ "suggestions": [ { id, name, price, image, brand } ] }

Stock update (admin)
POST stock_update.php
Form fields:
- product_id (int)
- stock (int)
- operation (set|increment|decrement)
- csrf_token

Server-Sent Events (admin dashboard)
GET notifications.php
Content-Type: text/event-stream
Events types: low_stock, new_orders, stats_update, heartbeat

Error format
{ "error": "message" }

Security
- All inputs sanitized server-side
- CSRF required for write operations
- Rate limiting on login
- Prepared statements via PDO