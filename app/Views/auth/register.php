<?php $errors = flash('errors') ?? []; ?>

<div class="max-w-md mx-auto mt-10">
    <h2 class="text-2xl font-bold text-gray-800 text-center mb-6">Register</h2>

    <div class="bg-white rounded-lg shadow p-6" x-data="{ submitting: false }">
        <form method="POST" action="<?= baseUrl('/register') ?>" @submit="submitting = true">
            <?= csrfField() ?>

            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" id="name" name="name"
                           value="<?= e(old('name')) ?>" required autofocus
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                  <?= !empty($errors['name']) ? 'border-red-400 bg-red-50' : 'border-gray-300' ?>"
                           placeholder="John Doe">
                    <?php if (!empty($errors['name'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= e($errors['name'][0]) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="email" name="email"
                           value="<?= e(old('email')) ?>" required
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                  <?= !empty($errors['email']) ? 'border-red-400 bg-red-50' : 'border-gray-300' ?>"
                           placeholder="you@example.com">
                    <?php if (!empty($errors['email'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= e($errors['email'][0]) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                  <?= !empty($errors['password']) ? 'border-red-400 bg-red-50' : 'border-gray-300' ?>"
                           placeholder="Min 6 characters">
                    <?php if (!empty($errors['password'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= e($errors['password'][0]) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Repeat password">
                </div>
            </div>

            <button type="submit" :disabled="submitting"
                    class="mt-5 w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-medium text-sm px-5 py-2.5 rounded-lg transition">
                <svg x-show="submitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span x-text="submitting ? 'Creating account...' : 'Register'"></span>
            </button>
        </form>

        <p class="mt-4 text-center text-sm text-gray-500">
            Already have an account?
            <a href="<?= baseUrl('/login') ?>" class="text-blue-600 hover:underline">Login</a>
        </p>
    </div>
</div>
