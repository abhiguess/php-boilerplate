<?php

class Post extends Model
{
    protected string $table = 'posts';

    /**
     * Get all posts with user name (JOIN)
     */
    public function allWithUser(): array
    {
        return Database::query(
            "SELECT posts.*, users.name as user_name
             FROM posts
             LEFT JOIN users ON posts.user_id = users.id
             ORDER BY posts.id DESC"
        );
    }

    /**
     * Get single post with user
     */
    public function findWithUser(int|string $id): ?array
    {
        return Database::queryOne(
            "SELECT posts.*, users.name as user_name
             FROM posts
             LEFT JOIN users ON posts.user_id = users.id
             WHERE posts.id = ?",
            [$id]
        );
    }

    /**
     * Get all posts by a user
     */
    public function byUser(int|string $userId): array
    {
        return $this->where(['user_id' => $userId]);
    }

    /**
     * Get user who owns this post
     */
    public function user(array $post): ?array
    {
        return $this->belongsTo('users', $post['user_id']);
    }
}
