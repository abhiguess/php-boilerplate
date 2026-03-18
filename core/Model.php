<?php

class Model
{
    protected string $table;
    protected string $primaryKey = 'id';

    /**
     * Get all records
     */
    public function all(string $orderBy = 'id DESC'): array
    {
        return Database::query("SELECT * FROM {$this->table} ORDER BY $orderBy");
    }

    /**
     * Find by primary key
     */
    public function find(int|string $id): ?array
    {
        return Database::queryOne(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?",
            [$id]
        );
    }

    /**
     * Find by a column value
     */
    public function findBy(string $column, mixed $value): ?array
    {
        return Database::queryOne(
            "SELECT * FROM {$this->table} WHERE $column = ?",
            [$value]
        );
    }

    /**
     * Get records matching conditions
     */
    public function where(array $conditions, string $orderBy = 'id DESC'): array
    {
        $clauses = [];
        $params = [];
        foreach ($conditions as $col => $val) {
            $clauses[] = "$col = ?";
            $params[] = $val;
        }
        $where = implode(' AND ', $clauses);
        return Database::query(
            "SELECT * FROM {$this->table} WHERE $where ORDER BY $orderBy",
            $params
        );
    }

    /**
     * Insert a new record
     */
    public function create(array $data): string
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        return Database::insert(
            "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)",
            array_values($data)
        );
    }

    /**
     * Update a record by primary key
     */
    public function update(int|string $id, array $data): int
    {
        $sets = [];
        $params = [];
        foreach ($data as $col => $val) {
            $sets[] = "$col = ?";
            $params[] = $val;
        }
        $params[] = $id;

        return Database::execute(
            "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE {$this->primaryKey} = ?",
            $params
        );
    }

    /**
     * Delete a record by primary key
     */
    public function delete(int|string $id): int
    {
        return Database::execute(
            "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?",
            [$id]
        );
    }

    /**
     * Count records, optionally with conditions
     */
    public function count(array $conditions = []): int
    {
        if (empty($conditions)) {
            $result = Database::queryOne("SELECT COUNT(*) as count FROM {$this->table}");
        } else {
            $clauses = [];
            $params = [];
            foreach ($conditions as $col => $val) {
                $clauses[] = "$col = ?";
                $params[] = $val;
            }
            $result = Database::queryOne(
                "SELECT COUNT(*) as count FROM {$this->table} WHERE " . implode(' AND ', $clauses),
                $params
            );
        }
        return (int) ($result['count'] ?? 0);
    }

    /**
     * hasMany — get related records from another table
     * Usage: $user->hasMany('posts', 'user_id', $userId)
     */
    public function hasMany(string $table, string $foreignKey, int|string $id, string $orderBy = 'id DESC'): array
    {
        return Database::query(
            "SELECT * FROM $table WHERE $foreignKey = ? ORDER BY $orderBy",
            [$id]
        );
    }

    /**
     * belongsTo — get parent record from another table
     * Usage: $post->belongsTo('users', $post['user_id'])
     */
    public function belongsTo(string $table, int|string $foreignId): ?array
    {
        return Database::queryOne(
            "SELECT * FROM $table WHERE id = ?",
            [$foreignId]
        );
    }

    /**
     * withJoin — get records with a joined parent table
     * Usage: $post->withJoin('users', 'user_id', 'id', ['users.name as user_name'])
     */
    public function withJoin(
        string $joinTable,
        string $foreignKey,
        string $ownerKey = 'id',
        array $selectExtra = [],
        string $orderBy = ''
    ): array {
        $select = "{$this->table}.*";
        if ($selectExtra) {
            $select .= ', ' . implode(', ', $selectExtra);
        }
        $orderBy = $orderBy ?: "{$this->table}.id DESC";

        return Database::query(
            "SELECT $select FROM {$this->table}
             LEFT JOIN $joinTable ON {$this->table}.$foreignKey = $joinTable.$ownerKey
             ORDER BY $orderBy"
        );
    }

    /**
     * Simple pagination
     */
    public function paginate(int $page = 1, int $perPage = 10, string $orderBy = 'id DESC'): array
    {
        $total = $this->count();
        $totalPages = (int) ceil($total / $perPage);
        $page = max(1, min($page, $totalPages ?: 1));
        $offset = ($page - 1) * $perPage;

        $rows = Database::query(
            "SELECT * FROM {$this->table} ORDER BY $orderBy LIMIT $perPage OFFSET $offset"
        );

        return [
            'data'        => $rows,
            'total'       => $total,
            'per_page'    => $perPage,
            'current_page'=> $page,
            'total_pages' => $totalPages,
        ];
    }
}
