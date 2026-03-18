<?php

class Auth
{
    /**
     * Attempt login with email and password
     */
    public static function attempt(string $email, string $password): bool
    {
        $user = Database::queryOne(
            "SELECT * FROM users WHERE email = ?",
            [$email]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        self::login($user);
        return true;
    }

    /**
     * Log a user in (set session)
     */
    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['_auth_user_id'] = $user['id'];
        $_SESSION['_auth_user'] = [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
        ];
    }

    /**
     * Log the user out
     */
    public static function logout(): void
    {
        unset($_SESSION['_auth_user_id'], $_SESSION['_auth_user']);
        session_regenerate_id(true);
    }

    /**
     * Check if user is logged in
     */
    public static function check(): bool
    {
        return !empty($_SESSION['_auth_user_id']);
    }

    /**
     * Check if user is guest
     */
    public static function guest(): bool
    {
        return !self::check();
    }

    /**
     * Get logged in user data from session
     */
    public static function user(): ?array
    {
        return $_SESSION['_auth_user'] ?? null;
    }

    /**
     * Get logged in user ID
     */
    public static function id(): ?int
    {
        return $_SESSION['_auth_user_id'] ?? null;
    }

    /**
     * Get full user record from DB (fresh)
     */
    public static function fresh(): ?array
    {
        if (!self::check()) return null;
        return Database::queryOne("SELECT * FROM users WHERE id = ?", [self::id()]);
    }

    /**
     * Register a new user
     */
    public static function register(array $data): string
    {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['created_at'] = date('Y-m-d H:i:s');

        return Database::insert(
            "INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, ?)",
            [$data['name'], $data['email'], $data['password'], $data['created_at']]
        );
    }

    /**
     * Require auth — redirect to login if guest (for web routes)
     */
    public static function requireLogin(): void
    {
        if (self::guest()) {
            flash('message', 'Please login to continue');
            flash('message_type', 'error');
            redirect(baseUrl('/login'));
        }
    }

    /**
     * Require auth — return 401 JSON if guest (for API routes)
     */
    public static function requireAuth(): void
    {
        if (self::guest()) {
            Response::error('Unauthorized', 401);
        }
    }

    /**
     * Require guest — redirect to home if logged in
     */
    public static function requireGuest(): void
    {
        if (self::check()) {
            redirect(baseUrl('/'));
        }
    }

    /**
     * Generate a simple API token for a user
     */
    public static function generateToken(int|string $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);

        Database::execute(
            "UPDATE users SET api_token = ? WHERE id = ?",
            [$hash, $userId]
        );

        return $token;
    }

    /**
     * Authenticate via Bearer token (for API)
     */
    public static function attemptToken(): ?array
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!str_starts_with($header, 'Bearer ')) {
            return null;
        }

        $token = substr($header, 7);
        $hash = hash('sha256', $token);

        $user = Database::queryOne(
            "SELECT * FROM users WHERE api_token = ?",
            [$hash]
        );

        if ($user) {
            self::login($user);
        }

        return $user;
    }

    /**
     * Require API token auth
     */
    public static function requireToken(): void
    {
        if (!self::check() && !self::attemptToken()) {
            Response::error('Unauthorized', 401);
        }
    }
}
