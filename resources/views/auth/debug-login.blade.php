<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Login Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 p-6">
    <div class="mx-auto max-w-2xl rounded-3xl border border-slate-200 bg-white p-8 shadow-xl">
        <h1 class="text-2xl font-semibold text-slate-900">Debug Login Admin</h1>
        <p class="mt-2 text-sm text-slate-600">Halaman ini membantu memeriksa apakah akun admin default ada dan bisa login.</p>

        @if(session('debug_result'))
            <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
                {{ session('debug_result') }}
            </div>
        @endif

        @if(isset($admin))
            <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                <p><strong>Username:</strong> {{ $admin->username ?? '-' }}</p>
                <p><strong>Nama:</strong> {{ $admin->name ?? '-' }}</p>
                <p><strong>Password tersimpan:</strong> {{ $admin->password ?? '-' }}</p>
                <p><strong>Validasi password admin:</strong> {{ $admin->validatePassword('password123') ? 'Cocok' : 'Tidak cocok' }}</p>
            </div>
        @endif

        <form action="{{ route('admin.debug.login') }}" method="POST" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-700">Username</label>
                <input type="text" name="username" value="admin" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700">Password</label>
                <input type="password" name="password" value="password123" class="mt-2 w-full rounded-2xl border border-slate-300 px-4 py-3" required>
            </div>
            <button type="submit" class="w-full rounded-2xl bg-violet-600 px-4 py-3 font-semibold text-white">Coba Login</button>
        </form>
    </div>
</body>
</html>
