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

// -------------------------------------------------------
// Add your routes below
// -------------------------------------------------------
