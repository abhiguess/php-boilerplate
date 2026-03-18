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
// Add your routes below
// -------------------------------------------------------
