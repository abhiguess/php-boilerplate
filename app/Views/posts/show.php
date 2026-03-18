<div class="flex items-center justify-between mb-4">
    <h2 class="text-2xl font-bold text-gray-800"><?= e($post['title']) ?></h2>
    <div class="flex items-center gap-2">
        <a href="<?= baseUrl("/posts/{$post['id']}/edit") ?>" class="text-sm font-medium text-emerald-600 hover:underline">Edit</a>
        <a href="<?= baseUrl('/posts') ?>" class="inline-flex items-center gap-1 text-sm font-medium text-gray-600 hover:text-gray-900 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg>
            Back
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center gap-3 mb-4 text-sm text-gray-500">
        <span>By <strong class="text-gray-700"><?= e($post['user_name'] ?? 'Unknown') ?></strong></span>
        <span>&middot;</span>
        <span><?= e($post['created_at']) ?></span>
        <span>&middot;</span>
        <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full
            <?= $post['status'] === 'published' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
            <?= e(ucfirst($post['status'])) ?>
        </span>
    </div>

    <div class="prose max-w-none text-gray-700 leading-relaxed whitespace-pre-line"><?= e($post['body']) ?></div>
</div>
