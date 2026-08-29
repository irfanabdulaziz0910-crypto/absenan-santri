@extends($layout)

@section('title', 'Profil Saya')
@section('breadcrumb', 'Profil Saya')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-10">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-[#1a4731] to-[#234d3a] px-5 py-6 sm:px-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4 min-w-0">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($profile['name']) }}&background=e2f3e9&color=1a4731&bold=true" alt="{{ $profile['name'] }}" class="w-16 h-16 rounded-full border-4 border-white/40 object-cover shrink-0">
                    <div class="min-w-0">
                        <p class="text-lg sm:text-xl font-bold text-white truncate">{{ $profile['name'] }}</p>
                        <p class="text-sm text-emerald-100">{{ $profile['role'] }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="document.getElementById('editProfileModal').classList.remove('hidden')" class="px-4 py-2 rounded-xl bg-white text-[#1a4731] hover:bg-emerald-50 font-semibold text-sm shadow-sm">
                        Edit Profil
                    </button>
                    <button type="button" onclick="document.getElementById('usernameModal').classList.remove('hidden')" class="px-4 py-2 rounded-xl border border-white/40 text-white hover:bg-white/10 font-semibold text-sm">
                        Ubah Username
                    </button>
                    <button type="button" onclick="document.getElementById('passwordModal').classList.remove('hidden')" class="px-4 py-2 rounded-xl border border-white/40 text-white hover:bg-white/10 font-semibold text-sm">
                        Ubah Password
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 p-5 sm:p-6">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400">Nama</p>
                <p class="mt-2 font-bold text-slate-800 text-lg break-words">{{ $profile['name'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400">Username</p>
                <p class="mt-2 font-bold text-slate-800 text-lg break-words">{{ $profile['username'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400">Role</p>
                <p class="mt-2 font-bold text-slate-800 text-lg break-words">{{ $profile['role'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 lg:col-span-2">
                <p class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400">Kelas Induk / Data Awal</p>
                <p class="mt-2 font-bold text-slate-800 text-lg break-words">{{ $profile['kelas'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400">Status Akun</p>
                <p class="mt-2 inline-flex items-center gap-2 font-bold text-emerald-700 text-base">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    {{ $profile['status'] }}
                </p>
            </div>
            @if (($profile['type'] ?? '') === 'user')
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400">NIP / ID Guru</p>
                    <p class="mt-2 font-bold text-slate-800 text-base">{{ $profile['nip'] }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 lg:col-span-2">
                    <p class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400">Nomor HP / WhatsApp</p>
                    <p class="mt-2 font-bold text-slate-800 text-base">{{ $profile['nomor_hp'] }}</p>
                </div>
                <div class="lg:col-span-3 pt-2">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Ringkasan Aktivitas Mengajar</p>
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                        <div class="rounded-xl border border-slate-200 bg-white p-4">
                            <p class="text-xs font-semibold text-slate-500">Total Mengajar</p>
                            <p class="mt-1 text-2xl font-extrabold text-slate-800">{{ $profile['total_mengajar'] }}</p>
                        </div>
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-4">
                            <p class="text-xs font-semibold text-emerald-700">Total Hadir</p>
                            <p class="mt-1 text-2xl font-extrabold text-emerald-800">{{ $profile['total_hadir'] }}</p>
                        </div>
                        <div class="rounded-xl border border-blue-200 bg-blue-50/50 p-4">
                            <p class="text-xs font-semibold text-blue-700">Total Izin</p>
                            <p class="mt-1 text-2xl font-extrabold text-blue-800">{{ $profile['total_izin'] }}</p>
                        </div>
                        <div class="rounded-xl border border-amber-200 bg-amber-50/50 p-4">
                            <p class="text-xs font-semibold text-amber-700">Total Sakit</p>
                            <p class="mt-1 text-2xl font-extrabold text-amber-800">{{ $profile['total_sakit'] }}</p>
                        </div>
                        <div class="rounded-xl border border-rose-200 bg-rose-50/50 p-4">
                            <p class="text-xs font-semibold text-rose-700">Total Alfa</p>
                            <p class="mt-1 text-2xl font-extrabold text-rose-800">{{ $profile['total_alfa'] }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<div id="editProfileModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeAllModals()"></div>
    <div class="absolute left-1/2 top-1/2 w-[92%] max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white shadow-2xl overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h3 class="text-lg font-bold text-slate-800">Edit Profil</h3>
            <button type="button" onclick="closeAllModals()" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form method="POST" action="{{ route('profile.update') }}" class="p-5 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Nama</label>
                <input type="text" name="name" value="{{ old('name', $profile['name']) }}" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeAllModals()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-semibold">Batal</button>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#1a4731] text-white font-bold">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<div id="usernameModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeAllModals()"></div>
    <div class="absolute left-1/2 top-1/2 w-[92%] max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white shadow-2xl overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h3 class="text-lg font-bold text-slate-800">Ubah Username</h3>
            <button type="button" onclick="closeAllModals()" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form method="POST" action="{{ route('profile.username') }}" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Username Saat Ini</label>
                <input type="text" value="{{ $profile['username'] }}" disabled class="w-full border border-slate-200 bg-slate-50 rounded-xl px-3 py-2.5 text-sm text-slate-500">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Username Baru</label>
                <input type="text" name="username" value="{{ old('username') }}" required pattern="[A-Za-z0-9._-]+" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition">
                @error('username')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Password Saat Ini</label>
                <input type="password" name="current_password" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition">
                @error('current_password')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeAllModals()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-semibold">Batal</button>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#1a4731] text-white font-bold">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<div id="passwordModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="closeAllModals()"></div>
    <div class="absolute left-1/2 top-1/2 w-[92%] max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white shadow-2xl overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h3 class="text-lg font-bold text-slate-800">Ubah Password</h3>
            <button type="button" onclick="closeAllModals()" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form method="POST" action="{{ route('profile.password') }}" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Password Saat Ini</label>
                <input type="password" name="current_password" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition">
                @error('current_password')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Password Baru</label>
                <input type="password" name="password" required minlength="8" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition">
                @error('password')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:ring-[#1a4731] focus:border-[#1a4731] outline-none transition">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeAllModals()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-semibold">Batal</button>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#1a4731] text-white font-bold">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function closeAllModals() {
        document.getElementById('editProfileModal').classList.add('hidden');
        document.getElementById('usernameModal').classList.add('hidden');
        document.getElementById('passwordModal').classList.add('hidden');
    }
</script>
@endsection
