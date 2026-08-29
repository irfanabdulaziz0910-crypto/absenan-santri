@extends('layouts.app')

@section('title', 'Scanner Absensi')

@section('content')
<div class="flex flex-col items-center justify-center min-h-[80vh] p-4">
    <!-- Main Card -->
    <div class="w-full max-w-xl rounded-[2rem] bg-white p-12 text-center shadow-xl shadow-slate-200/50 relative">
        
        <!-- Alerts -->
        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm font-medium text-rose-700">{{ $errors->first() }}</div>
        @endif

        <!-- Session Pill -->
        @if (!empty($isLibur) || ($statusSesi ?? '') === 'Libur')
            <div class="inline-flex items-center gap-2 rounded-full bg-rose-600 px-5 py-1.5 text-xs font-bold tracking-wider text-white mb-6 uppercase shadow-md shadow-rose-900/20">
                <span class="w-2 h-2 rounded-full bg-white animate-ping"></span>
                SESI LIBUR (DITUTUP)
            </div>
            <h2 class="text-3xl font-bold text-slate-900 mb-2">Absensi Sesi {{ $session }} Libur</h2>
            <p class="text-sm text-rose-500 font-semibold mb-8">Mesin RFID dimatikan sementara untuk sesi ini.</p>
        @else
            <div class="inline-flex rounded-full bg-indigo-600 px-5 py-1.5 text-xs font-bold tracking-wider text-white mb-6 uppercase">
                SESI OTOMATIS: {{ $session ?? 'DI LUAR JADWAL' }}
            </div>
            <h2 class="text-3xl font-bold text-slate-900 mb-2">Scan ID Card Kamu Disini</h2>
            <p class="text-sm text-slate-500 mb-8">Sistem otomatis mendeteksi waktu sholat/kegiatan</p>
        @endif

        <!-- Clock -->
        <div id="clock" class="text-7xl font-bold {{ !empty($isLibur) ? 'text-slate-400' : 'text-indigo-600' }} tracking-widest mb-10">
            --:--:--
        </div>

        <!-- Input Form -->
        <form action="{{ route('attendance.scan') }}" method="POST" class="mb-8">
            @csrf
            <div class="relative max-w-sm mx-auto">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                    </svg>
                </div>
                <input type="text" name="barcode" id="barcode" autocomplete="off" class="w-full rounded-xl border {{ !empty($isLibur) ? 'border-rose-200 bg-rose-50/50 text-rose-400' : 'border-slate-300 text-slate-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500' }} py-3.5 pl-12 pr-4 placeholder:text-slate-400 outline-none" placeholder="{{ !empty($isLibur) ? 'Sesi Libur (Ditutup)' : 'Scan Barcode...' }}" required {{ !empty($isLibur) ? 'disabled' : 'autofocus' }}>
            </div>
            <!-- Menyembunyikan tombol submit karena scanner barcode biasanya mengirimkan 'Enter' secara otomatis -->
            <button type="submit" class="hidden" {{ !empty($isLibur) ? 'disabled' : '' }}>Submit</button>
        </form>
    </div>
</div>

<script>
    const clock = document.getElementById('clock');
    if (clock) {
        function updateClock() {
            const now = new Date();
            clock.textContent = now.toLocaleTimeString('id-ID', { hour12: false, hour: '2-digit', minute:'2-digit', second:'2-digit' }).replace(/\./g, ':');
        }
        updateClock();
        setInterval(updateClock, 1000);
    }
    
    // Memastikan input barcode selalu fokus saat di-klik di luar input
    document.addEventListener('click', function(e) {
        const barcodeInput = document.getElementById('barcode');
        if(barcodeInput && e.target.id !== 'barcode') {
            barcodeInput.focus();
        }
    });
</script>
@endsection
