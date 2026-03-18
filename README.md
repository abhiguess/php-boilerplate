# PHP Boilerplate

A minimal PHP MVC boilerplate with routing, database (PDO), validation, and Tailwind + Alpine.js frontend. No framework, no Composer — just clean PHP 8+.

## Quick Start

### 1. Configure Environment

Copy and edit `.env`:

```bash
cp .env.example .env
```

Update database credentials in `.env`:

```
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=php_boilerplate
DB_USERNAME=root
DB_PASSWORD=
```

### 2. Create Database

Import `database.sql` via phpMyAdmin, HeidiSQL, or CLI:

```bash
mysql -u root -p < database.sql
```

### 3. Run the App

**PHP built-in server:**

```bash
php -S localhost:8000 -t public
```

**Laragon/Apache** — just visit `http://localhost/php-boilerplate` (update `APP_URL` in `.env` accordingly).

## Project Structure

```
php-boilerplate/
├── public/
│   ├── index.php             # Entry point (all requests)
│   └── .htaccess             # Apache URL rewriting
├── config/
│   └── app.php               # Helpers: env(), e(), redirect(), flash(), csrf, old()
├── core/
│   ├── Database.php          # PDO wrapper (query, insert, execute, transactions)
│   ├── Router.php            # GET/POST/PUT/DELETE routing with {param} support
│   ├── Request.php           # Input handling (POST, GET, JSON, files)
│   ├── Response.php          # JSON responses (success/error)
│   ├── Controller.php        # Base web controller: view(), json(), redirect(), back()
│   ├── ApiController.php     # Base API controller: success(), error(), validate()
│   ├── Model.php             # Base model: CRUD + hasMany, belongsTo, withJoin
│   └── Validator.php         # Validation: required, email, min, max, unique, numeric, etc.
├── app/
│   ├── Controllers/          # Web controllers
│   │   └── Api/              # API controllers (JSON only)
│   ├── Models/               # Your models go here
│   └── Views/
│       └── layouts/main.php  # HTML layout (Tailwind + Alpine.js via CDN)
├── routes.php                # All route definitions
├── database.sql              # DB schema + sample data
├── .env                      # Environment config
└── .env.example              # Template for .env
```

## How to Add a New Resource

Example: adding a **Product** CRUD.

### 1. Create the Table

```sql
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 2. Create the Model — `app/Models/Product.php`

```php
<?php

class Product extends Model
{
    protected string $table = 'products';
}
```

### 3. Create the Controller — `app/Controllers/ProductController.php`

```php
<?php

class ProductController extends Controller
{
    private Product $product;

    public function __construct()
    {
        $this->product = new Product();
    }

    public function index(): void
    {
        $result = $this->product->paginate((int) Request::query('page', 1));
        $this->view('products/index', $result);
    }

    public function store(): void
    {
        $data = Request::only(['name', 'price']);

        $v = Validator::make($data, [
            'name'  => 'required|min:2|max:150',
            'price' => 'required|numeric|min_value:0',
        ]);

        if ($v->fails()) {
            $this->back($v->errors());
            return;
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $this->product->create($data);
        $this->redirect('/products', 'Product created');
    }

    // ... edit, update, destroy follow the same pattern
}
```

### 4. Add Routes — `routes.php`

```php
Router::get('/products',              [ProductController::class, 'index']);
Router::get('/products/create',       [ProductController::class, 'create']);
Router::post('/products',             [ProductController::class, 'store']);
Router::get('/products/{id}/edit',    [ProductController::class, 'edit']);
Router::put('/products/{id}',         [ProductController::class, 'update']);
Router::post('/products/{id}/delete', [ProductController::class, 'destroy']);
```

### 5. Create Views — `app/Views/products/`

Copy from `app/Views/users/` and modify fields.

## Available Helpers

| Helper | Description |
|---|---|
| `env('KEY', 'default')` | Read `.env` value |
| `baseUrl('/path')` | Full URL with base path |
| `e($string)` | HTML escape (XSS safe) |
| `redirect($url)` | Redirect and exit |
| `flash('key', $value)` | Set/get flash message |
| `old('field', 'default')` | Get old form input |
| `csrfField()` | CSRF hidden input |
| `verifyCsrf()` | Verify CSRF token |
| `dd($var)` | Dump and die |

## Base Model Methods

| Method | Description |
|---|---|
| `all($orderBy)` | Get all records |
| `find($id)` | Find by primary key |
| `findBy($column, $value)` | Find by any column |
| `where($conditions)` | Filter by conditions |
| `create($data)` | Insert, returns ID |
| `update($id, $data)` | Update by ID |
| `delete($id)` | Delete by ID |
| `count($conditions)` | Count records |
| `paginate($page, $perPage)` | Paginated results |
| `hasMany($table, $fk, $id)` | One-to-many relation |
| `belongsTo($table, $foreignId)` | Inverse relation |
| `withJoin($table, $fk, ...)` | LEFT JOIN with extras |

## REST API

All API endpoints return JSON with consistent format:

```json
{ "success": true, "message": "Success", "data": { ... } }
```

### Users API

| Method | URL | Description |
|---|---|---|
| `GET` | `/api/users` | List all (`?page=1&per_page=10` for pagination) |
| `GET` | `/api/users/{id}` | Get user with their posts |
| `POST` | `/api/users` | Create user |
| `PUT` | `/api/users/{id}` | Update user |
| `DELETE` | `/api/users/{id}` | Delete user |

### Posts API

| Method | URL | Description |
|---|---|---|
| `GET` | `/api/posts` | List all (`?user_id=1&status=published` to filter) |
| `GET` | `/api/posts/{id}` | Get post with author |
| `POST` | `/api/posts` | Create post |
| `PUT` | `/api/posts/{id}` | Update post |
| `DELETE` | `/api/posts/{id}` | Delete post |

### Example: Adding a Product API

Create `app/Controllers/Api/ProductApiController.php`:

```php
<?php

class ProductApiController extends ApiController
{
    private Product $product;

    public function __construct()
    {
        $this->product = new Product();
    }

    public function index(): void
    {
        $this->success($this->product->all());
    }

    public function show(string $id): void
    {
        $product = $this->product->find($id);
        if (!$product) $this->notFound();
        $this->success($product);
    }

    public function store(): void
    {
        $data = Request::only(['name', 'price']);
        $this->validate($data, [
            'name'  => 'required|min:2',
            'price' => 'required|numeric|min_value:0',
        ]);
        $id = $this->product->create($data);
        $this->created($this->product->find($id));
    }
}
```

Add routes in `routes.php`:

```php
Router::get('/api/products',          [ProductApiController::class, 'index']);
Router::get('/api/products/{id}',     [ProductApiController::class, 'show']);
Router::post('/api/products',         [ProductApiController::class, 'store']);
Router::put('/api/products/{id}',     [ProductApiController::class, 'update']);
Router::delete('/api/products/{id}',  [ProductApiController::class, 'destroy']);
```

### Test with curl

```bash
# List users
curl http://localhost:8000/api/users

# Get user with posts
curl http://localhost:8000/api/users/1

# Filter posts
curl http://localhost:8000/api/posts?user_id=1&status=published

# Create user (JSON body)
curl -X POST http://localhost:8000/api/users \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com"}'

# Update user
curl -X PUT http://localhost:8000/api/users/1 \
  -H "Content-Type: application/json" \
  -d '{"name":"Updated Name","email":"updated@example.com"}'

# Delete user
curl -X DELETE http://localhost:8000/api/users/1
```

## Validation Rules

`required`, `email`, `numeric`, `integer`, `min:N` (length), `max:N` (length), `min_value:N`, `max_value:N`, `in:a,b,c`, `unique:table,column,except_id`, `confirmed`, `url`, `date`, `regex:pattern`

```php
$v = Validator::make(Request::all(), [
    'name'  => 'required|min:2|max:100',
    'email' => 'required|email|unique:users,email',
    'age'   => 'numeric|min_value:18',
]);

if ($v->fails()) {
    // $v->errors()     → ['field' => ['error msg', ...]]
    // $v->firstError() → first error string
}
```

## Frontend Stack

- **Tailwind CSS** — via CDN (`<script src="https://cdn.tailwindcss.com">`)
- **Alpine.js** — via CDN (search filter, delete confirmations, form submit spinners)

No build step required.
