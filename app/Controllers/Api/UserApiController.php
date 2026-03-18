<?php

class UserApiController extends ApiController
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    /**
     * GET /api/users
     */
    public function index(): void
    {
        $page = (int) Request::query('page', 0);

        if ($page > 0) {
            $perPage = (int) Request::query('per_page', 10);
            $this->success($this->user->paginate($page, $perPage));
        } else {
            $this->success($this->user->all());
        }
    }

    /**
     * GET /api/users/{id}
     */
    public function show(string $id): void
    {
        $user = $this->user->find($id);
        if (!$user) {
            $this->notFound('User not found');
        }

        // Include user's posts
        $user['posts'] = $this->user->hasMany('posts', 'user_id', $id);

        $this->success($user);
    }

    /**
     * POST /api/users
     */
    public function store(): void
    {
        $data = Request::only(['name', 'email', 'phone']);

        $this->validate($data, [
            'name'  => 'required|min:2|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'max:20',
        ]);

        $data['created_at'] = date('Y-m-d H:i:s');
        $id = $this->user->create($data);

        $this->created($this->user->find($id), 'User created');
    }

    /**
     * PUT /api/users/{id}
     */
    public function update(string $id): void
    {
        $user = $this->user->find($id);
        if (!$user) {
            $this->notFound('User not found');
        }

        $data = Request::only(['name', 'email', 'phone']);

        $this->validate($data, [
            'name'  => 'required|min:2|max:100',
            'email' => "required|email|unique:users,email,$id",
            'phone' => 'max:20',
        ]);

        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->user->update($id, $data);

        $this->success($this->user->find($id), 'User updated');
    }

    /**
     * DELETE /api/users/{id}
     */
    public function destroy(string $id): void
    {
        $user = $this->user->find($id);
        if (!$user) {
            $this->notFound('User not found');
        }

        $this->user->delete($id);
        $this->success(null, 'User deleted');
    }
}
