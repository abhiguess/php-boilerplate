<div class="flex items-center justify-between mb-4">
    <h2 class="text-2xl font-bold text-gray-800">Posts</h2>
    <a href="<?= baseUrl('/posts/create') ?>"
       class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
        Add Post
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden" x-data="{ search: '' }">

    <div class="px-5 py-3 border-b border-gray-200">
        <input type="text" x-model="search" placeholder="Search posts..."
               class="w-full sm:w-72 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>

    <?php if (empty($posts)): ?>
        <div class="px-5 py-10 text-center text-gray-500">
            <p class="text-lg">No posts found.</p>
            <a href="<?= baseUrl('/posts/create') ?>" class="text-blue-600 hover:underline mt-2 inline-block">Create the first post</a>
        </div>
    <?php else: ?>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <th class="px-5 py-3">ID</th>
                    <th class="px-5 py-3">Title</th>
                    <th class="px-5 py-3">Author</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($posts as $post): ?>
                <tr x-show="search === '' || '<?= e(strtolower($post['title'] . ' ' . ($post['user_name'] ?? ''))) ?>'.includes(search.toLowerCase())"
                    class="hover:bg-gray-50 transition">
                    <td class="px-5 py-3 text-gray-500"><?= e($post['id']) ?></td>
                    <td class="px-5 py-3">
                        <a href="<?= baseUrl("/posts/{$post['id']}") ?>" class="font-medium text-blue-600 hover:underline">
                            <?= e($post['title']) ?>
                        </a>
                    </td>
                    <td class="px-5 py-3 text-gray-600"><?= e($post['user_name'] ?? 'Unknown') ?></td>
                    <td class="px-5 py-3">
                        <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full
                            <?= $post['status'] === 'published' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                            <?= e(ucfirst($post['status'])) ?>
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-end gap-2">
                            <a href="<?= baseUrl("/posts/{$post['id']}/edit") ?>"
                               class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-md bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition">
                                Edit
                            </a>
                            <div x-data="{ confirm: false }">
                                <button @click="confirm = true" x-show="!confirm"
                                        class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-md bg-red-50 text-red-700 hover:bg-red-100 transition">
                                    Delete
                                </button>
                                <form x-show="confirm" x-transition method="POST" action="<?= baseUrl("/posts/{$post['id']}/delete") ?>" class="inline-flex items-center gap-1">
                                    <?= csrfField() ?>
                                    <button type="submit" class="text-xs font-medium px-3 py-1.5 rounded-md bg-red-600 text-white hover:bg-red-700 transition">Confirm</button>
                                    <button type="button" @click="confirm = false" class="text-xs font-medium px-3 py-1.5 rounded-md bg-gray-200 text-gray-700 hover:bg-gray-300 transition">Cancel</button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
