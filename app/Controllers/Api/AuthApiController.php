<?php

class AuthApiController extends ApiController
{
    /**
     * POST /api/login
     */
    public function login(): void
    {
        $data = Request::only(['email', 'password']);

        $this->validate($data, [
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (!Auth::attempt($data['email'], $data['password'])) {
            $this->error('Invalid email or password', 401);
        }

        $user = Auth::user();
        $token = Auth::generateToken($user['id']);

        $this->success([
            'user'  => $user,
            'token' => $token,
        ], 'Login successful');
    }

    /**
     * POST /api/register
     */
    public function register(): void
    {
        $data = Request::only(['name', 'email', 'password', 'password_confirmation']);

        $this->validate($data, [
            'name'     => 'required|min:2|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        $id = Auth::register($data);

        $user = (new User())->find($id);
        Auth::login($user);
        $token = Auth::generateToken($id);

        $this->created([
            'user'  => [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
            ],
            'token' => $token,
        ], 'Registration successful');
    }

    /**
     * GET /api/me (requires token)
     */
    public function me(): void
    {
        Auth::requireToken();

        $user = Auth::fresh();
        unset($user['password'], $user['api_token']);

        $this->success($user);
    }

    /**
     * POST /api/logout (requires token)
     */
    public function logout(): void
    {
        Auth::requireToken();

        // Clear token
        Database::execute("UPDATE users SET api_token = NULL WHERE id = ?", [Auth::id()]);
        Auth::logout();

        $this->success(null, 'Logged out');
    }
}
