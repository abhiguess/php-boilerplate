<?php

class PostApiController extends ApiController
{
    private Post $post;

    public function __construct()
    {
        $this->post = new Post();
    }

    /**
     * GET /api/posts
     * Optional filters: ?user_id=1&status=published
     */
    public function index(): void
    {
        $userId = Request::query('user_id');
        $status = Request::query('status');

        // Build conditions from query params
        $conditions = [];
        if ($userId) $conditions['user_id'] = $userId;
        if ($status) $conditions['status'] = $status;

        if (!empty($conditions)) {
            $posts = $this->post->where($conditions);
        } else {
            $posts = $this->post->allWithUser();
        }

        $this->success($posts);
    }

    /**
     * GET /api/posts/{id}
     */
    public function show(string $id): void
    {
        $post = $this->post->findWithUser($id);
        if (!$post) {
            $this->notFound('Post not found');
        }

        $this->success($post);
    }

    /**
     * POST /api/posts
     */
    public function store(): void
    {
        $data = Request::only(['title', 'body', 'user_id', 'status']);

        $this->validate($data, [
            'title'   => 'required|min:3|max:200',
            'body'    => 'required|min:10',
            'user_id' => 'required|integer',
            'status'  => 'required|in:draft,published',
        ]);

        $data['created_at'] = date('Y-m-d H:i:s');
        $id = $this->post->create($data);

        $this->created($this->post->findWithUser($id), 'Post created');
    }

    /**
     * PUT /api/posts/{id}
     */
    public function update(string $id): void
    {
        $post = $this->post->find($id);
        if (!$post) {
            $this->notFound('Post not found');
        }

        $data = Request::only(['title', 'body', 'user_id', 'status']);

        $this->validate($data, [
            'title'   => 'required|min:3|max:200',
            'body'    => 'required|min:10',
            'user_id' => 'required|integer',
            'status'  => 'required|in:draft,published',
        ]);

        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->post->update($id, $data);

        $this->success($this->post->findWithUser($id), 'Post updated');
    }

    /**
     * DELETE /api/posts/{id}
     */
    public function destroy(string $id): void
    {
        $post = $this->post->find($id);
        if (!$post) {
            $this->notFound('Post not found');
        }

        $this->post->delete($id);
        $this->success(null, 'Post deleted');
    }
}
