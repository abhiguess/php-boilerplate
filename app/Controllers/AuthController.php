<?php

class AuthController extends Controller
{
    public function loginForm(): void
    {
        Auth::requireGuest();
        $this->view('auth/login', ['title' => 'Login']);
    }

    public function login(): void
    {
        $data = Request::only(['email', 'password']);

        $v = Validator::make($data, [
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($v->fails()) {
            $this->back($v->errors());
            return;
        }

        if (!Auth::attempt($data['email'], $data['password'])) {
            $this->back(['email' => ['Invalid email or password.']]);
            return;
        }

        $this->redirect('/', 'Welcome back, ' . Auth::user()['name'] . '!');
    }

    public function registerForm(): void
    {
        Auth::requireGuest();
        $this->view('auth/register', ['title' => 'Register']);
    }

    public function register(): void
    {
        $data = Request::only(['name', 'email', 'password', 'password_confirmation']);

        $v = Validator::make($data, [
            'name'     => 'required|min:2|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($v->fails()) {
            $this->back($v->errors());
            return;
        }

        $id = Auth::register($data);

        // Auto-login after register
        $user = (new User())->find($id);
        Auth::login($user);

        $this->redirect('/', 'Account created. Welcome!');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/login', 'You have been logged out.');
    }
}
