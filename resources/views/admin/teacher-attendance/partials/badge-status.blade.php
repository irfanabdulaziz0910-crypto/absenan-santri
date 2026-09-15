@php
    $badgeClass = match ($status ?? '-') {
        'Hadir' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'Izin'  => 'bg-blue-50 text-blue-700 border-blue-200',
        'Sakit' => 'bg-amber-50 text-amber-700 border-amber-200',
        'Alfa'  => 'bg-rose-50 text-rose-700 border-rose-200',
        'Libur' => 'bg-slate-100 text-slate-700 border-slate-300 font-semibold',
        default => 'bg-slate-50 text-slate-400 border-slate-200 font-normal',
    };
@endphp
<span class="inline-block px-2.5 py-1 rounded-full border text-xs font-bold {{ $badgeClass }}">
    {{ $status ?? '-' }}
</span>
