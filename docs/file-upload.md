# File Upload Guide (Image/Media)

How to add file uploads to the PHP boilerplate — no packages needed, pure PHP.

The `uploadFile()` and `deleteFile()` helpers are already included in `config/app.php`.
The `public/uploads/` directory is already created and git-ignored.

**You're ready to use them — just follow the examples below.**

---

## Example 1: User Avatar (Web)

### Database

Add `avatar` column to users table:

```sql
ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL AFTER phone;
```

### Routes

No new routes needed — existing `/users` and `/users/{id}` handle it.

### Controller — `UserController.php`

Update `store()` and `update()` methods:

```php
public function store(): void
{
    verifyCsrf();
    $data = Request::only(['name', 'email', 'phone']);

    $v = Validator::make($data, [
        'name'  => 'required|min:2|max:100',
        'email' => 'required|email|unique:users,email',
    ]);

    if ($v->fails()) {
        $this->back(['errors' => $v->errors(), 'old' => $data]);
    }

    // Handle avatar upload
    $avatar = uploadFile('avatar', 'users');
    if ($avatar) {
        $data['avatar'] = $avatar;
    }

    $data['created_at'] = date('Y-m-d H:i:s');
    $this->user->create($data);

    flash('message', 'User created');
    flash('message_type', 'success');
    $this->redirect(baseUrl('/users'));
}

public function update(string $id): void
{
    verifyCsrf();
    $user = $this->user->find($id);
    if (!$user) {
        flash('message', 'User not found');
        flash('message_type', 'error');
        $this->redirect(baseUrl('/users'));
    }

    $data = Request::only(['name', 'email', 'phone']);

    $v = Validator::make($data, [
        'name'  => 'required|min:2|max:100',
        'email' => "required|email|unique:users,email,{$id}",
    ]);

    if ($v->fails()) {
        $this->back(['errors' => $v->errors(), 'old' => $data]);
    }

    // Handle avatar upload
    $avatar = uploadFile('avatar', 'users');
    if ($avatar) {
        // Delete old avatar
        deleteFile($user['avatar']);
        $data['avatar'] = $avatar;
    }

    // Handle avatar removal (checkbox)
    if (Request::input('remove_avatar') && $user['avatar']) {
        deleteFile($user['avatar']);
        $data['avatar'] = null;
    }

    $data['updated_at'] = date('Y-m-d H:i:s');
    $this->user->update($id, $data);

    flash('message', 'User updated');
    flash('message_type', 'success');
    $this->redirect(baseUrl('/users'));
}
```

### View — Form with Avatar Upload

Add to `app/Views/users/form.php` inside the form (form must have `enctype`):

```php
<!-- The form tag MUST have enctype for file uploads -->
<form action="<?= baseUrl($user ? "/users/{$user['id']}" : '/users') ?>"
      method="POST" enctype="multipart/form-data">
    <?= csrfField() ?>
    <?php if ($user): ?>
        <input type="hidden" name="_method" value="PUT">
    <?php endif; ?>

    <!-- ... other fields ... -->

    <!-- Avatar Upload -->
    <div x-data="{ preview: '<?= e($user['avatar'] ?? '') ?>' }">
        <label class="block text-sm font-medium text-gray-700 mb-1">Avatar</label>

        <!-- Current avatar preview -->
        <template x-if="preview">
            <div class="mb-2">
                <img :src="preview.startsWith('uploads/') ? '<?= baseUrl('/') ?>' + preview : preview"
                     class="w-20 h-20 rounded-full object-cover">
            </div>
        </template>

        <!-- File input -->
        <input type="file" name="avatar" accept="image/*"
               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
                      file:rounded file:border-0 file:text-sm file:font-semibold
                      file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
               @change="
                   const file = $event.target.files[0];
                   if (file) {
                       const reader = new FileReader();
                       reader.onload = (e) => preview = e.target.result;
                       reader.readAsDataURL(file);
                   }
               ">

        <?php if (!empty($user['avatar'])): ?>
            <!-- Remove avatar checkbox -->
            <label class="inline-flex items-center mt-2 text-sm text-gray-600">
                <input type="checkbox" name="remove_avatar" value="1" class="mr-2">
                Remove current avatar
            </label>
        <?php endif; ?>
    </div>

    <!-- ... submit button ... -->
</form>
```

### Display Avatar in Views

```php
<!-- In user list or profile -->
<?php if ($user['avatar']): ?>
    <img src="<?= baseUrl('/' . e($user['avatar'])) ?>"
         alt="<?= e($user['name']) ?>"
         class="w-10 h-10 rounded-full object-cover">
<?php else: ?>
    <!-- Fallback: initials -->
    <div class="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center text-white text-sm font-bold">
        <?= strtoupper(substr($user['name'], 0, 1)) ?>
    </div>
<?php endif; ?>
```

---

## Example 2: Post Featured Image (Web)

### Database

```sql
ALTER TABLE posts ADD COLUMN image VARCHAR(255) DEFAULT NULL AFTER body;
```

### Controller — `PostController.php`

```php
public function store(): void
{
    verifyCsrf();
    $data = Request::only(['title', 'body', 'user_id', 'status']);

    $v = Validator::make($data, [
        'title'   => 'required|min:3|max:200',
        'body'    => 'required|min:10',
        'user_id' => 'required|integer',
        'status'  => 'required|in:draft,published',
    ]);

    if ($v->fails()) {
        $this->back(['errors' => $v->errors(), 'old' => $data]);
    }

    // Handle image upload
    $image = uploadFile('image', 'posts');
    if ($image) {
        $data['image'] = $image;
    }

    $data['created_at'] = date('Y-m-d H:i:s');
    $this->post->create($data);

    flash('message', 'Post created');
    flash('message_type', 'success');
    $this->redirect(baseUrl('/posts'));
}
```

---

## Example 3: API File Upload

### Route

```php
Router::post('/api/users/{id}/avatar', [UserApiController::class, 'uploadAvatar']);
```

### Controller — `UserApiController.php`

```php
/**
 * POST /api/users/{id}/avatar
 * Upload via multipart/form-data
 */
public function uploadAvatar(string $id): void
{
    $user = $this->user->find($id);
    if (!$user) {
        $this->error('User not found', 404);
    }

    $path = uploadFile('avatar', 'users');
    if (!$path) {
        $this->error('Invalid file. Allowed: jpg, png, gif, webp. Max 5MB.', 422);
    }

    // Delete old avatar
    deleteFile($user['avatar']);

    // Update user
    $this->user->update($id, ['avatar' => $path]);

    $this->success([
        'avatar'  => $path,
        'url'     => baseUrl('/' . $path),
    ], 'Avatar uploaded');
}
```

### Test with curl

```bash
# Upload avatar
curl -X POST http://localhost:8000/api/users/1/avatar \
  -H "Authorization: Bearer your-token" \
  -F "avatar=@photo.jpg"

# Response:
# {
#   "success": true,
#   "message": "Avatar uploaded",
#   "data": {
#     "avatar": "uploads/users/64a1b2c3_1710789012.jpg",
#     "url": "http://localhost:8000/uploads/users/64a1b2c3_1710789012.jpg"
#   }
# }
```

---

## Example 4: Multiple File Upload

### Form

```php
<input type="file" name="images[]" multiple accept="image/*">
```

### Controller

```php
public function uploadGallery(string $postId): void
{
    $files = $_FILES['images'];
    $uploaded = [];

    // Reorganize PHP's weird multi-file array structure
    $fileCount = count($files['name']);

    for ($i = 0; $i < $fileCount; $i++) {
        // Temporarily set $_FILES so uploadFile() works
        $_FILES['_temp_upload'] = [
            'name'     => $files['name'][$i],
            'type'     => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error'    => $files['error'][$i],
            'size'     => $files['size'][$i],
        ];

        $path = uploadFile('_temp_upload', 'posts/gallery');
        if ($path) {
            // Save to a post_images table or JSON column
            Database::insert(
                "INSERT INTO post_images (post_id, path, created_at) VALUES (?, ?, ?)",
                [$postId, $path, date('Y-m-d H:i:s')]
            );
            $uploaded[] = $path;
        }
    }

    unset($_FILES['_temp_upload']);

    flash('message', count($uploaded) . ' images uploaded');
    flash('message_type', 'success');
    redirect(baseUrl("/posts/{$postId}"));
}
```

---

## Allowing Other File Types

The `uploadFile()` helper accepts custom allowed extensions:

```php
// Images only (default)
$path = uploadFile('avatar', 'users');

// PDF documents
$path = uploadFile('document', 'docs', ['pdf'], 10 * 1024 * 1024); // 10MB max

// Any document
$path = uploadFile('file', 'files', ['pdf', 'doc', 'docx', 'xls', 'xlsx'], 20 * 1024 * 1024);

// Video
$path = uploadFile('video', 'videos', ['mp4', 'mov', 'avi'], 50 * 1024 * 1024); // 50MB
```

---

## Security Checklist

- [x] Validate file extension against whitelist
- [x] Validate MIME type (don't trust extension alone)
- [x] Enforce max file size
- [x] Generate random filename (never use original name)
- [x] Store uploads outside app root or in `public/uploads/` only
- [x] Delete old files when replacing
- [ ] For production: check PHP `upload_max_filesize` and `post_max_size` in `php.ini`

```ini
; php.ini — increase if needed
upload_max_filesize = 10M
post_max_size = 12M
```

---

## Quick Reference

| Task | Code |
|------|------|
| Upload single file | `$path = uploadFile('field', 'folder');` |
| Upload with custom types | `uploadFile('field', 'folder', ['pdf', 'doc'], 10*1024*1024)` |
| Delete file | `deleteFile($path);` |
| Show image in view | `<img src="<?= baseUrl('/' . e($path)) ?>">` |
| Form must have | `enctype="multipart/form-data"` |
| API upload via curl | `curl -F "file=@photo.jpg" URL` |
