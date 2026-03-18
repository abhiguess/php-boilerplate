<div class="bg-white rounded-lg shadow p-8 text-center">
    <h2 class="text-3xl font-bold text-gray-800">Welcome to <?= e(env('APP_NAME', 'PHP App')) ?></h2>
    <p class="mt-3 text-gray-500">Your boilerplate is running. Start building!</p>
    <a href="<?= baseUrl('/users') ?>"
       class="mt-5 inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2.5 rounded-lg transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        Manage Users (Sample CRUD)
    </a>
</div>
