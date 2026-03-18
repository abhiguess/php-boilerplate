<div class="flex items-center justify-between mb-4">
    <h2 class="text-2xl font-bold text-gray-800">Users</h2>
    <a href="<?= baseUrl('/users/create') ?>"
       class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
        Add User
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden" x-data="{ search: '' }">

    <!-- Search -->
    <div class="px-5 py-3 border-b border-gray-200">
        <input type="text" x-model="search" placeholder="Search users..."
               class="w-full sm:w-72 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>

    <?php if (empty($data)): ?>
        <div class="px-5 py-10 text-center text-gray-500">
            <p class="text-lg">No users found.</p>
            <a href="<?= baseUrl('/users/create') ?>" class="text-blue-600 hover:underline mt-2 inline-block">Create the first user</a>
        </div>
    <?php else: ?>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <th class="px-5 py-3">ID</th>
                    <th class="px-5 py-3">Name</th>
                    <th class="px-5 py-3">Email</th>
                    <th class="px-5 py-3">Phone</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($data as $user): ?>
                <tr x-show="search === '' || '<?= e(strtolower($user['name'] . ' ' . $user['email'])) ?>'.includes(search.toLowerCase())"
                    class="hover:bg-gray-50 transition">
                    <td class="px-5 py-3 text-gray-500"><?= e($user['id']) ?></td>
                    <td class="px-5 py-3 font-medium text-gray-800"><?= e($user['name']) ?></td>
                    <td class="px-5 py-3 text-gray-600"><?= e($user['email']) ?></td>
                    <td class="px-5 py-3 text-gray-600"><?= e($user['phone'] ?? '-') ?></td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-end gap-2">
                            <a href="<?= baseUrl("/users/{$user['id']}/edit") ?>"
                               class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-md bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </a>

                            <!-- Delete with Alpine confirmation -->
                            <div x-data="{ confirm: false }">
                                <button @click="confirm = true"
                                        x-show="!confirm"
                                        class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-md bg-red-50 text-red-700 hover:bg-red-100 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Delete
                                </button>
                                <form x-show="confirm"
                                      x-transition
                                      method="POST" action="<?= baseUrl("/users/{$user['id']}/delete") ?>"
                                      class="inline-flex items-center gap-1">
                                    <?= csrfField() ?>
                                    <button type="submit"
                                            class="text-xs font-medium px-3 py-1.5 rounded-md bg-red-600 text-white hover:bg-red-700 transition">
                                        Confirm
                                    </button>
                                    <button type="button" @click="confirm = false"
                                            class="text-xs font-medium px-3 py-1.5 rounded-md bg-gray-200 text-gray-700 hover:bg-gray-300 transition">
                                        Cancel
                                    </button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
        <div class="px-5 py-3 border-t border-gray-200 flex items-center justify-between">
            <span class="text-sm text-gray-500">Page <?= $current_page ?> of <?= $total_pages ?> (<?= $total ?> total)</span>
            <div class="flex gap-1">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="<?= baseUrl("/users?page=$i") ?>"
                       class="px-3 py-1 text-sm rounded-md border transition
                              <?= $i === $current_page
                                  ? 'bg-blue-600 text-white border-blue-600'
                                  : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
