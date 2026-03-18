<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? env('APP_NAME', 'PHP App')) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 py-6">

        <!-- Navbar -->
        <nav class="bg-gray-800 text-white rounded-lg px-5 py-3 mb-6 flex items-center gap-6">
            <a href="<?= baseUrl('/') ?>" class="font-bold text-lg hover:text-gray-300 transition"><?= e(env('APP_NAME', 'PHP App')) ?></a>
            <a href="<?= baseUrl('/users') ?>" class="text-gray-300 hover:text-white transition">Users</a>
        </nav>

        <!-- Flash Messages -->
        <?php if ($msg = flash('message')): ?>
        <?php $type = flash('message_type') ?: 'success'; ?>
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="mb-4 px-4 py-3 rounded-lg flex items-center justify-between
                    <?= $type === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300' ?>">
            <span><?= e($msg) ?></span>
            <button @click="show = false" class="ml-4 text-lg font-bold leading-none opacity-60 hover:opacity-100">&times;</button>
        </div>
        <?php endif; ?>

        <!-- Page Content -->
        <?= $content ?>

    </div>
</body>
</html>
