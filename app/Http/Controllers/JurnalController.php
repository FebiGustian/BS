<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Jurnal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JurnalController extends Controller
{
    private function cabangAktif(): int
    {
        $cabangId = (int) session('cabang_id');

        if (!$cabangId || !DB::table('cabangs')->where('id', $cabangId)->exists()) {
            abort(403, 'Cabang aktif belum dipilih. Silakan pilih cabang terlebih dahulu.');
        }

        return $cabangId;
    }

    public function index(Request $request)
    {
        $cabangId = $this->cabangAktif();

        $query = Jurnal::with('user')
            ->withSum('details', 'debit')
            ->withSum('details', 'kredit')
            ->where('cabang_id', $cabangId);

        if ($request->filled('q')) {
            $keyword = $request->q;

            $query->where(function ($q) use ($keyword) {
                $q->where('nomor_jurnal', 'like', "%{$keyword}%")
                  ->orWhere('deskripsi', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_akhir);
        }

        $jurnals = $query->latest('tanggal')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('jurnal.index', compact('jurnals'));
    }

    public function create()
    {
        $this->cabangAktif();

        $accounts = Account::where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('jurnal.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $cabangId = $this->cabangAktif();

        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'deskripsi' => ['required', 'string', 'max:255'],
            'details' => ['required', 'array', 'min:2'],
            'details.*.account_id' => ['required', 'integer', 'exists:accounts,id'],
            'details.*.debit' => ['nullable', 'numeric', 'min:0'],
            'details.*.kredit' => ['nullable', 'numeric', 'min:0'],
            'details.*.keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $accountIds = array_unique(array_map(
            'intval',
            array_column($data['details'], 'account_id')
        ));

        $activeAccountCount = Account::whereIn('id', $accountIds)
            ->where('is_active', true)
            ->count();

        if ($activeAccountCount !== count($accountIds)) {
            throw ValidationException::withMessages([
                'details' => 'Ada akun yang tidak aktif atau tidak tersedia.',
            ]);
        }

        $totalDebit = 0;
        $totalKredit = 0;

        foreach ($data['details'] as $row) {
            $debit = (float) ($row['debit'] ?? 0);
            $kredit = (float) ($row['kredit'] ?? 0);

            if (($debit > 0 && $kredit > 0) || ($debit <= 0 && $kredit <= 0)) {
                throw ValidationException::withMessages([
                    'details' => 'Setiap baris harus memiliki nilai debit atau kredit saja, tidak boleh keduanya kosong atau terisi bersamaan.',
                ]);
            }

            $totalDebit += $debit;
            $totalKredit += $kredit;
        }

        if ($totalDebit <= 0 || round($totalDebit, 2) !== round($totalKredit, 2)) {
            throw ValidationException::withMessages([
                'details' => 'Jurnal tidak seimbang. Total debit harus sama dengan total kredit.',
            ]);
        }

        $jurnal = DB::transaction(function () use ($data, $cabangId) {
            $jurnal = Jurnal::create([
                'nomor_jurnal' => null,
                'tanggal' => $data['tanggal'],
                'deskripsi' => $data['deskripsi'],
                'cabang_id' => $cabangId,
                'user_id' => auth()->id(),
                'status' => 'posted',
            ]);

            $nomor = 'JU-' . date('Ymd', strtotime($data['tanggal']))
                . '-' . str_pad((string) $jurnal->id, 6, '0', STR_PAD_LEFT);

            $jurnal->update(['nomor_jurnal' => $nomor]);

            foreach ($data['details'] as $row) {
                $jurnal->details()->create([
                    'account_id' => $row['account_id'],
                    'debit' => $row['debit'] ?? 0,
                    'kredit' => $row['kredit'] ?? 0,
                    'keterangan' => $row['keterangan'] ?? null,
                ]);
            }

            return $jurnal;
        });

        return redirect()
            ->route('jurnal.show', $jurnal)
            ->with('success', 'Jurnal umum berhasil disimpan.');
    }

    public function show(Jurnal $jurnal)
    {
        $cabangId = $this->cabangAktif();

        abort_unless((int) $jurnal->cabang_id === $cabangId, 404);

        $jurnal->load(['details.account', 'user']);

        return view('jurnal.show', compact('jurnal'));
    }
}
