<?php

class UserController extends Controller
{
    private User $user;

    public function __construct()
    {
        Auth::requireLogin();
        $this->user = new User();
    }

    public function index(): void
    {
        $page = (int) Request::query('page', 1);
        $result = $this->user->paginate($page, 10);

        if (Request::isAjax()) {
            Response::success($result);
        }

        $this->view('users/index', $result);
    }

    public function create(): void
    {
        $this->view('users/form', ['user' => null]);
    }

    public function store(): void
    {
        $data = Request::only(['name', 'email', 'phone']);

        $v = Validator::make($data, [
            'name'  => 'required|min:2|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'max:20',
        ]);

        if ($v->fails()) {
            if (Request::isAjax()) {
                Response::error('Validation failed', 422, $v->errors());
            }
            $this->back($v->errors());
            return;
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $this->user->create($data);

        if (Request::isAjax()) {
            Response::success(null, 'User created');
        }

        $this->redirect('/users', 'User created successfully');
    }

    public function edit(string $id): void
    {
        $user = $this->user->find($id);
        if (!$user) {
            $this->redirect('/users', 'User not found', 'error');
            return;
        }

        $this->view('users/form', ['user' => $user]);
    }

    public function update(string $id): void
    {
        $user = $this->user->find($id);
        if (!$user) {
            if (Request::isAjax()) Response::error('Not found', 404);
            $this->redirect('/users', 'User not found', 'error');
            return;
        }

        $data = Request::only(['name', 'email', 'phone']);

        $v = Validator::make($data, [
            'name'  => 'required|min:2|max:100',
            'email' => "required|email|unique:users,email,$id",
            'phone' => 'max:20',
        ]);

        if ($v->fails()) {
            if (Request::isAjax()) Response::error('Validation failed', 422, $v->errors());
            $this->back($v->errors());
            return;
        }

        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->user->update($id, $data);

        if (Request::isAjax()) Response::success(null, 'User updated');
        $this->redirect('/users', 'User updated successfully');
    }

    public function destroy(string $id): void
    {
        $this->user->delete($id);

        if (Request::isAjax()) Response::success(null, 'User deleted');
        $this->redirect('/users', 'User deleted successfully');
    }
}
