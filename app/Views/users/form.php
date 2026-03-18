<?php
$isEdit = !empty($user);
$title = $isEdit ? 'Edit User' : 'Create User';
$action = $isEdit ? baseUrl("/users/{$user['id']}") : baseUrl('/users');
$errors = flash('errors') ?? [];
?>

<div class="flex items-center justify-between mb-4">
    <h2 class="text-2xl font-bold text-gray-800"><?= $title ?></h2>
    <a href="<?= baseUrl('/users') ?>"
       class="inline-flex items-center gap-1 text-sm font-medium text-gray-600 hover:text-gray-900 transition">
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
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name"
                       value="<?= e(old('name', $user['name'] ?? '')) ?>" required
                       class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                              <?= !empty($errors['name']) ? 'border-red-400 bg-red-50' : 'border-gray-300' ?>"
                       placeholder="John Doe">
                <?php if (!empty($errors['name'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= e($errors['name'][0]) ?></p>
                <?php endif; ?>
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                <input type="email" id="email" name="email"
                       value="<?= e(old('email', $user['email'] ?? '')) ?>" required
                       class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                              <?= !empty($errors['email']) ? 'border-red-400 bg-red-50' : 'border-gray-300' ?>"
                       placeholder="john@example.com">
                <?php if (!empty($errors['email'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= e($errors['email'][0]) ?></p>
                <?php endif; ?>
            </div>

            <!-- Phone -->
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input type="text" id="phone" name="phone"
                       value="<?= e(old('phone', $user['phone'] ?? '')) ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                              <?= !empty($errors['phone']) ? 'border-red-400 bg-red-50' : 'border-gray-300' ?>"
                       placeholder="1234567890">
                <?php if (!empty($errors['phone'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= e($errors['phone'][0]) ?></p>
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
                <span x-text="submitting ? 'Saving...' : '<?= $isEdit ? 'Update' : 'Create' ?> User'"></span>
            </button>
            <a href="<?= baseUrl('/users') ?>" class="text-sm text-gray-500 hover:text-gray-700 transition">Cancel</a>
        </div>
    </form>
</div>
