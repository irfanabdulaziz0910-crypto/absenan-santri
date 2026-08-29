@extends('layouts.wali-kelas')
@section('title', 'Absensi Manual Wali Kelas')

@section('content')
<div class="mb-6 flex flex-col gap-4 md:flex-row md:justify-between md:items-end">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Absensi Manual</h1>
        <p class="text-slate-500 text-sm mt-1">Catat kehadiran santri yang belum melakukan absensi RFID.</p>
    </div>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="bg-white border border-slate-200 rounded-xl px-3 py-2 flex items-center gap-2 shadow-sm min-h-[44px]">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">TANGGAL</span>
            <input type="date" id="selTanggal" onchange="renderManualTable()" class="text-xs font-semibold text-slate-700 outline-none bg-transparent cursor-pointer min-h-[32px]">
        </div>
        <div class="bg-white border border-slate-200 rounded-xl px-3 py-2 flex items-center gap-2 shadow-sm min-h-[44px]">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">SESI NGAJI</span>
            <select id="selSesi" onchange="renderManualTable()" class="text-xs font-bold text-slate-800 outline-none bg-transparent cursor-pointer min-h-[32px]">
                <option value="Subuh" {{ ($sesiAktif ?? 'Subuh') == 'Subuh' ? 'selected' : '' }}>Subuh</option>
                <option value="Dzuhur" {{ ($sesiAktif ?? '') == 'Dzuhur' ? 'selected' : '' }}>Dzuhur</option>
                <option value="Ashar" {{ ($sesiAktif ?? '') == 'Ashar' ? 'selected' : '' }}>Ashar</option>
                <option value="Isya" {{ ($sesiAktif ?? '') == 'Isya' ? 'selected' : '' }}>Isya</option>
            </select>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-6">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left whitespace-nowrap">
            <thead class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-4 w-12 text-center">NO</th>
                    <th class="px-5 py-4">SANTRI</th>
                    <th class="px-5 py-4">NIS</th>
                    <th class="px-5 py-4">STATUS RFID</th>
                    <th class="px-5 py-4 text-center">KEHADIRAN MANUAL</th>
                    <th class="px-5 py-4">KETERANGAN</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50" id="manualTableBody">
                <!-- Rows injected via JS -->
            </tbody>
        </table>
    </div>

    <div class="px-4 py-4 border-t border-slate-100 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <div class="flex items-center gap-2 text-xs font-semibold text-slate-600">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span id="manualNotice">Terdapat 0 santri yang perlu diabsen manual.</span>
        </div>
        <button onclick="saveManualBatch()" class="w-full min-h-[48px] px-6 py-3 bg-[#1a4731] text-white font-bold text-xs rounded-xl shadow-md shadow-green-900/20 hover:bg-[#153c28] transition flex items-center justify-center gap-2 sm:w-auto sm:py-2.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Simpan Absensi
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const targetKelas = @json($assignedKelas ?? '');
    let santriKelas = JSON.parse(localStorage.getItem('santri_data')) || [];
    if (targetKelas) {
        santriKelas = santriKelas.filter(s => s.kelas === targetKelas);
    }
    if (santriKelas.length === 0) {
        let dbSantris = @json($santris);
        if (dbSantris && dbSantris.length) {
            santriKelas = dbSantris.map(s => ({
                id: s.id, name: s.name, nis: s.nis, kelas: s.kelas || targetKelas, rfid_barcode: s.rfid_barcode, jenis_kelamin: 'L'
            }));
        }
    }

    // Remove any stale localStorage items
    localStorage.removeItem('absensi_records');
    localStorage.removeItem('santri_data');

    function getTodayStr() {
        const now = new Date();
        return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
    }
    document.getElementById('selTanggal').value = getTodayStr();

    const attendanceMap = @json($attendanceMap ?? []);
    let manualDrafts = {};

    function renderManualTable() {
        const tgl = document.getElementById('selTanggal').value;
        const sesi = document.getElementById('selSesi').value.toLowerCase().replace('ashar', 'asar');
        const tbody = document.getElementById('manualTableBody');
        tbody.innerHTML = '';
        manualDrafts = {};

        let countNeedManual = 0;

        santriKelas.sort((a,b) => a.name.localeCompare(b.name)).forEach((s, idx) => {
            const attObj = (attendanceMap[s.id] && attendanceMap[s.id][tgl] && attendanceMap[s.id][tgl][sesi]) ? attendanceMap[s.id][tgl][sesi] : null;
            const isRfid = attObj && attObj.status && attObj.status.toLowerCase() === 'hadir' && attObj.notes && attObj.notes.toLowerCase().includes('rfid');
            const isLibur = attObj && attObj.status && attObj.status.toLowerCase() === 'libur';

            let statusRfidHtml = '';
            let manualButtonsHtml = '';
            let ketInputHtml = '';

            if (isRfid) {
                statusRfidHtml = `<span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-bold border border-emerald-200"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Hadir - RFID</span>`;
                manualButtonsHtml = `<div class="text-xs text-slate-400 font-semibold flex items-center justify-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg> Terkunci (Sistem)</div>`;
                ketInputHtml = `<span class="text-xs text-slate-400">-</span>`;
            } else if (isLibur) {
                statusRfidHtml = `<span class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-200 text-slate-700 rounded-lg text-xs font-bold border border-slate-300">Libur</span>`;
                manualButtonsHtml = `<div class="text-xs text-red-600 font-extrabold flex items-center justify-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500"></span> Terkunci (Jadwal Libur)</div>`;
                ketInputHtml = `<span class="text-xs text-slate-600 font-bold">${attObj.notes || 'Jadwal Libur'}</span>`;
            } else {
                countNeedManual++;
                statusRfidHtml = `<span class="text-xs text-slate-400 italic">- Belum Tap -</span>`;

                const currentStatus = attObj ? attObj.status : 'Alfa';
                const currentKet = attObj ? (attObj.notes || '') : '';
                manualDrafts[s.id] = { status: currentStatus, keterangan: currentKet };

                const btnHadir = currentStatus === 'Hadir' ? 'bg-[#1a4731] text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200';
                const btnIzin = currentStatus === 'Izin' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200';
                const btnSakit = currentStatus === 'Sakit' ? 'bg-amber-500 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200';
                const btnAlfa = currentStatus === 'Alfa' ? 'bg-red-600 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200';

                manualButtonsHtml = `
                    <div class="inline-flex bg-slate-100 p-1 rounded-xl gap-1">
                        <button onclick="setDraftStatus(${s.id}, 'Hadir')" id="btnSt-${s.id}-Hadir" class="px-3 py-1.5 text-xs rounded-lg transition ${btnHadir}">Hadir</button>
                        <button onclick="setDraftStatus(${s.id}, 'Izin')" id="btnSt-${s.id}-Izin" class="px-3 py-1.5 text-xs rounded-lg transition ${btnIzin}">Izin</button>
                        <button onclick="setDraftStatus(${s.id}, 'Sakit')" id="btnSt-${s.id}-Sakit" class="px-3 py-1.5 text-xs rounded-lg transition ${btnSakit}">Sakit</button>
                        <button onclick="setDraftStatus(${s.id}, 'Alfa')" id="btnSt-${s.id}-Alfa" class="px-3 py-1.5 text-xs rounded-lg transition ${btnAlfa}">Alfa</button>
                    </div>
                `;

                ketInputHtml = `<input type="text" id="inputKet-${s.id}" value="${currentKet}" oninput="setDraftKet(${s.id}, this.value)" placeholder="Keterangan..." class="w-full text-xs border border-slate-200 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-[#1a4731] outline-none">`;
            }

            const bgAvatar = s.jenis_kelamin === 'L' ? 'e2f3e9' : 'fdf4ff';
            const colAvatar = s.jenis_kelamin === 'L' ? '1a4731' : '86198f';

            const tr = document.createElement('tr');
            tr.className = `hover:bg-slate-50/50 transition ${isRfid ? 'bg-slate-50/40' : ''}`;
            tr.innerHTML = `
                <td class="px-5 py-4 text-center font-medium text-slate-500">${idx + 1}</td>
                <td class="px-5 py-4 flex items-center gap-3">
                    <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(s.name)}&background=${bgAvatar}&color=${colAvatar}&bold=true" class="w-8 h-8 rounded-full border border-slate-200 shrink-0">
                    <span class="font-bold text-slate-800">${s.name}</span>
                </td>
                <td class="px-5 py-4 font-mono text-xs font-semibold text-slate-600">${s.nis}</td>
                <td class="px-5 py-4">${statusRfidHtml}</td>
                <td class="px-5 py-4 text-center">${manualButtonsHtml}</td>
                <td class="px-5 py-4">${ketInputHtml}</td>
            `;
            tbody.appendChild(tr);
        });

        document.getElementById('manualNotice').textContent = `Terdapat ${countNeedManual} santri yang perlu diabsen manual.`;
    }

    function setDraftStatus(santriId, status) {
        if(!manualDrafts[santriId]) manualDrafts[santriId] = {};
        manualDrafts[santriId].status = status;

        const statuses = ['Hadir', 'Izin', 'Sakit', 'Alfa'];
        statuses.forEach(st => {
            const btn = document.getElementById(`btnSt-${santriId}-${st}`);
            if(!btn) return;
            if(st === status) {
                if(st==='Hadir') btn.className = "px-3 py-1.5 text-xs rounded-lg transition bg-[#1a4731] text-white font-bold shadow-sm";
                if(st==='Izin') btn.className = "px-3 py-1.5 text-xs rounded-lg transition bg-blue-600 text-white font-bold shadow-sm";
                if(st==='Sakit') btn.className = "px-3 py-1.5 text-xs rounded-lg transition bg-amber-500 text-white font-bold shadow-sm";
                if(st==='Alfa') btn.className = "px-3 py-1.5 text-xs rounded-lg transition bg-red-600 text-white font-bold shadow-sm";
            } else {
                btn.className = "px-3 py-1.5 text-xs rounded-lg transition bg-slate-100 text-slate-600 hover:bg-slate-200";
            }
        });
    }

    function setDraftKet(santriId, val) {
        if(!manualDrafts[santriId]) manualDrafts[santriId] = {};
        manualDrafts[santriId].keterangan = val;
    }

    function saveManualBatch() {
        const tgl = document.getElementById('selTanggal').value;
        const sesi = document.getElementById('selSesi').value;

        const payloadAttendance = [];
        let hasError = false;

        Object.keys(manualDrafts).forEach(sIdStr => {
            const sId = parseInt(sIdStr);
            const draft = manualDrafts[sId];
            if (!draft || !draft.status) return;

            if ((draft.status === 'Izin' || draft.status === 'Sakit') && (!draft.keterangan || !draft.keterangan.trim())) {
                const s = santriKelas.find(k => k.id === sId);
                showToast(`Keterangan wajib diisi untuk ${s ? s.name : 'santri'} (${draft.status})!`, 'error');
                hasError = true;
                return;
            }

            payloadAttendance.push({
                santri_id: sId,
                status: draft.status,
                notes: draft.keterangan || null
            });
        });

        if (hasError) return;

        if (payloadAttendance.length === 0) {
            showToast('Tidak ada perubahan absensi manual yang perlu disimpan.', 'error');
            return;
        }

        fetch("{{ route('wali-kelas.absensi-manual.post') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                date: tgl,
                session: sesi,
                attendance: payloadAttendance
            })
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok || data.success === false) throw new Error(data.message || 'Absensi manual gagal disimpan.');
            showToast(`Absensi manual untuk ${payloadAttendance.length} santri berhasil disimpan ke database!`, 'success');
            setTimeout(() => location.reload(), 1000);
        })
        .catch(err => {
            showToast(err.message || 'Absensi manual gagal disimpan.', 'error');
        });
    }

    renderManualTable();
</script>
@endpush
