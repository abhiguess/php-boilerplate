<?php

/**
 * Define all application routes here
 */

// Home
Router::get('/', function () {
    $ctrl = new class extends Controller {
        public function index(): void
        {
            $this->view('home', ['title' => 'Home']);
        }
    };
    $ctrl->index();
});

// Users CRUD
Router::get('/users',              [UserController::class, 'index']);
Router::get('/users/create',       [UserController::class, 'create']);
Router::post('/users',             [UserController::class, 'store']);
Router::get('/users/{id}/edit',    [UserController::class, 'edit']);
Router::put('/users/{id}',         [UserController::class, 'update']);
Router::post('/users/{id}/delete', [UserController::class, 'destroy']);

// Posts CRUD (belongsTo User)
Router::get('/posts',              [PostController::class, 'index']);
Router::get('/posts/create',       [PostController::class, 'create']);
Router::post('/posts',             [PostController::class, 'store']);
Router::get('/posts/{id}',         [PostController::class, 'show']);
Router::get('/posts/{id}/edit',    [PostController::class, 'edit']);
Router::put('/posts/{id}',         [PostController::class, 'update']);
Router::post('/posts/{id}/delete', [PostController::class, 'destroy']);

// -------------------------------------------------------
// API Routes (JSON only)
// -------------------------------------------------------

// Users API
Router::get('/api/users',          [UserApiController::class, 'index']);
Router::get('/api/users/{id}',     [UserApiController::class, 'show']);
Router::post('/api/users',         [UserApiController::class, 'store']);
Router::put('/api/users/{id}',     [UserApiController::class, 'update']);
Router::delete('/api/users/{id}',  [UserApiController::class, 'destroy']);

// Posts API
Router::get('/api/posts',          [PostApiController::class, 'index']);
Router::get('/api/posts/{id}',     [PostApiController::class, 'show']);
Router::post('/api/posts',         [PostApiController::class, 'store']);
Router::put('/api/posts/{id}',     [PostApiController::class, 'update']);
Router::delete('/api/posts/{id}',  [PostApiController::class, 'destroy']);

// -------------------------------------------------------
// Add your routes below
// -------------------------------------------------------
