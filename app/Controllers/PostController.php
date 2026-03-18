<?php

class PostController extends Controller
{
    private Post $post;
    private User $user;

    public function __construct()
    {
        $this->post = new Post();
        $this->user = new User();
    }

    public function index(): void
    {
        $posts = $this->post->allWithUser();

        if (Request::isAjax()) {
            Response::success($posts);
        }

        $this->view('posts/index', ['posts' => $posts]);
    }

    public function show(string $id): void
    {
        $post = $this->post->findWithUser($id);
        if (!$post) {
            $this->redirect('/posts', 'Post not found', 'error');
            return;
        }

        $this->view('posts/show', ['post' => $post]);
    }

    public function create(): void
    {
        $users = $this->user->all('name ASC');
        $this->view('posts/form', ['post' => null, 'users' => $users]);
    }

    public function store(): void
    {
        $data = Request::only(['title', 'body', 'user_id', 'status']);

        $v = Validator::make($data, [
            'title'   => 'required|min:3|max:200',
            'body'    => 'required|min:10',
            'user_id' => 'required|integer',
            'status'  => 'required|in:draft,published',
        ]);

        if ($v->fails()) {
            if (Request::isAjax()) Response::error('Validation failed', 422, $v->errors());
            $this->back($v->errors());
            return;
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $this->post->create($data);

        if (Request::isAjax()) Response::success(null, 'Post created');
        $this->redirect('/posts', 'Post created successfully');
    }

    public function edit(string $id): void
    {
        $post = $this->post->find($id);
        if (!$post) {
            $this->redirect('/posts', 'Post not found', 'error');
            return;
        }

        $users = $this->user->all('name ASC');
        $this->view('posts/form', ['post' => $post, 'users' => $users]);
    }

    public function update(string $id): void
    {
        $post = $this->post->find($id);
        if (!$post) {
            if (Request::isAjax()) Response::error('Not found', 404);
            $this->redirect('/posts', 'Post not found', 'error');
            return;
        }

        $data = Request::only(['title', 'body', 'user_id', 'status']);

        $v = Validator::make($data, [
            'title'   => 'required|min:3|max:200',
            'body'    => 'required|min:10',
            'user_id' => 'required|integer',
            'status'  => 'required|in:draft,published',
        ]);

        if ($v->fails()) {
            if (Request::isAjax()) Response::error('Validation failed', 422, $v->errors());
            $this->back($v->errors());
            return;
        }

        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->post->update($id, $data);

        if (Request::isAjax()) Response::success(null, 'Post updated');
        $this->redirect('/posts', 'Post updated successfully');
    }

    public function destroy(string $id): void
    {
        $this->post->delete($id);

        if (Request::isAjax()) Response::success(null, 'Post deleted');
        $this->redirect('/posts', 'Post deleted successfully');
    }
}
