<?php
$isEdit = !empty($post);
$title = $isEdit ? 'Edit Post' : 'Create Post';
$action = $isEdit ? baseUrl("/posts/{$post['id']}") : baseUrl('/posts');
$errors = flash('errors') ?? [];
?>

<div class="flex items-center justify-between mb-4">
    <h2 class="text-2xl font-bold text-gray-800"><?= $title ?></h2>
    <a href="<?= baseUrl('/posts') ?>" class="inline-flex items-center gap-1 text-sm font-medium text-gray-600 hover:text-gray-900 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg>
        Back to list
    </a>
</div>

<div class="bg-white rounded-lg shadow p-6" x-data="{ submitting: false }">
    <form method="POST" action="<?= $action ?>" @submit="submitting = true">
        <?= csrfField() ?>
        <?php if ($isEdit): ?>
            <input type="hidden" name="_method" value="PUT">
        <?php endif; ?>

        <div class="space-y-5">
            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                <input type="text" id="title" name="title"
                       value="<?= e(old('title', $post['title'] ?? '')) ?>" required
                       class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                              <?= !empty($errors['title']) ? 'border-red-400 bg-red-50' : 'border-gray-300' ?>"
                       placeholder="Post title">
                <?php if (!empty($errors['title'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= e($errors['title'][0]) ?></p>
                <?php endif; ?>
            </div>

            <!-- Author (belongsTo User) -->
            <div>
                <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">Author <span class="text-red-500">*</span></label>
                <select id="user_id" name="user_id" required
                        class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                               <?= !empty($errors['user_id']) ? 'border-red-400 bg-red-50' : 'border-gray-300' ?>">
                    <option value="">-- Select Author --</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= e($user['id']) ?>"
                            <?= (old('user_id', $post['user_id'] ?? '')) == $user['id'] ? 'selected' : '' ?>>
                            <?= e($user['name']) ?> (<?= e($user['email']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['user_id'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= e($errors['user_id'][0]) ?></p>
                <?php endif; ?>
            </div>

            <!-- Status -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                <select id="status" name="status" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="draft" <?= (old('status', $post['status'] ?? 'draft')) === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= (old('status', $post['status'] ?? '')) === 'published' ? 'selected' : '' ?>>Published</option>
                </select>
            </div>

            <!-- Body -->
            <div>
                <label for="body" class="block text-sm font-medium text-gray-700 mb-1">Content <span class="text-red-500">*</span></label>
                <textarea id="body" name="body" rows="8" required
                          class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                 <?= !empty($errors['body']) ? 'border-red-400 bg-red-50' : 'border-gray-300' ?>"
                          placeholder="Write your post content here..."><?= e(old('body', $post['body'] ?? '')) ?></textarea>
                <?php if (!empty($errors['body'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= e($errors['body'][0]) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-6 flex items-center gap-3">
            <button type="submit" :disabled="submitting"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-medium text-sm px-5 py-2.5 rounded-lg transition">
                <svg x-show="submitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span x-text="submitting ? 'Saving...' : '<?= $isEdit ? 'Update' : 'Create' ?> Post'"></span>
            </button>
            <a href="<?= baseUrl('/posts') ?>" class="text-sm text-gray-500 hover:text-gray-700 transition">Cancel</a>
        </div>
    </form>
</div>
