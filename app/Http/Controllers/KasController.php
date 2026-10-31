<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Jurnal;
use App\Models\KasTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class KasController extends Controller
{
    private function cabangAktif(): int
    {
        $id = (int) session('cabang_id');

        if (!$id || !DB::table('cabangs')->where('id', $id)->exists()) {
            abort(403, 'Silakan pilih cabang aktif terlebih dahulu.');
        }

        return $id;
    }

    private function akunKas()
    {
        return Account::where('is_active', true)
            ->whereIn('code', ['1111', '1113'])
            ->orderBy('code')
            ->get();
    }

    private function akunKategori(string $jenis)
    {
        $type = $jenis === 'income' ? 'revenue' : 'expense';

        $query = Account::where('is_active', true)
            ->where('type', $type)
            ->whereDoesntHave('children');

        // Hanya akun beban operasional untuk pengeluaran kas.
        if ($jenis === 'expense') {
            $query->where('code', 'like', '6%');
        }

        return $query->orderBy('code')->get();
    }

    public function index(Request $request)
    {
        $cabangId = $this->cabangAktif();

        $query = KasTransaction::with([
            'account',
            'cashAccount',
            'jurnal',
            'user',
        ])->where('cabang_id', $cabangId);

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        if ($request->filled('q')) {
            $keyword = $request->q;

            $query->where(function ($q) use ($keyword) {
                $q->where('nomor_transaksi', 'like', "%{$keyword}%")
                    ->orWhere('deskripsi', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_akhir);
        }

        $transaksi = $query->latest('tanggal')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $bulanIni = KasTransaction::where('cabang_id', $cabangId)
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year);

        $totalPemasukan = (clone $bulanIni)
            ->where('jenis', 'income')
            ->sum('nominal');

        $totalPengeluaran = (clone $bulanIni)
            ->where('jenis', 'expense')
            ->sum('nominal');

        $totalTransferMasuk = (clone $bulanIni)
            ->where('jenis', 'transfer_in')
            ->sum('nominal');

        $totalTransferKeluar = (clone $bulanIni)
            ->where('jenis', 'transfer_out')
            ->sum('nominal');

        return view('kas.index', compact(
            'transaksi',
            'totalPemasukan',
            'totalPengeluaran',
            'totalTransferMasuk',
            'totalTransferKeluar'
        ));
    }

    public function create(Request $request)
    {
        $this->cabangAktif();

        $jenis = $request->query('jenis', 'income');

        if (!in_array($jenis, ['income', 'expense', 'transfer_in', 'transfer_out'], true)) {
            abort(404);
        }

        $cashAccounts = $this->akunKas();
        $incomeAccounts = $this->akunKategori('income');
        $expenseAccounts = $this->akunKategori('expense');

        return view('kas.create', compact(
            'jenis',
            'cashAccounts',
            'incomeAccounts',
            'expenseAccounts'
        ));
    }

    public function store(Request $request)
    {
        $cabangId = $this->cabangAktif();

        $data = $request->validate([
            'jenis' => ['required', 'in:income,expense,transfer_in,transfer_out'],
            'tanggal' => ['required', 'date'],
            'deskripsi' => ['required', 'string', 'max:255'],
            'nominal' => ['required', 'numeric', 'min:0.01', 'max:9999999999999'],
            'account_id' => ['nullable', 'required_if:jenis,income,expense', 'integer'],
            'cash_account_id' => ['nullable', 'required_if:jenis,income,expense', 'integer'],
            'bukti' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $jenis = $data['jenis'];
        $kategori = null;
        $akunKas = null;

        if (in_array($jenis, ['income', 'expense'], true)) {
            $akunKas = Account::where('id', $data['cash_account_id'])
                ->where('is_active', true)
                ->whereIn('code', ['1111', '1113'])
                ->first();

            if (!$akunKas) {
                throw ValidationException::withMessages([
                    'cash_account_id' => 'Pilih akun Kas Cabang atau Bank yang aktif.',
                ]);
            }

            $kategori = Account::where('id', $data['account_id'])
                ->where('is_active', true)
                ->whereDoesntHave('children')
                ->where('type', $jenis === 'income' ? 'revenue' : 'expense')
                ->when($jenis === 'expense', function ($query) {
                    $query->where('code', 'like', '6%');
                })
                ->first();

            if (!$kategori) {
                throw ValidationException::withMessages([
                    'account_id' => 'Akun kategori tidak sesuai dengan jenis transaksi.',
                ]);
            }
        }

        $kasCabang = null;
        $kasPusat = null;

        if (in_array($jenis, ['transfer_in', 'transfer_out'], true)) {
            $kasCabang = Account::where('code', '1111')
                ->where('is_active', true)
                ->first();

            $kasPusat = Account::where('code', '1112')
                ->where('is_active', true)
                ->first();

            if (!$kasCabang || !$kasPusat) {
                throw ValidationException::withMessages([
                    'jenis' => 'Akun Kas Cabang atau Kas Pusat tidak tersedia atau tidak aktif di COA.',
                ]);
            }
        }

        $buktiPath = $request->hasFile('bukti')
            ? $request->file('bukti')->store('bukti-kas', 'public')
            : null;

        try {
            $transaksi = DB::transaction(function () use (
                $data,
                $cabangId,
                $jenis,
                $kategori,
                $akunKas,
                $kasCabang,
                $kasPusat,
                $buktiPath
            ) {
                $nominal = round((float) $data['nominal'], 2);

                $transaksi = KasTransaction::create([
                    'nomor_transaksi' => 'TEMP-' . uniqid(),
                    'jenis' => $jenis,
                    'tanggal' => $data['tanggal'],
                    'deskripsi' => $data['deskripsi'],
                    'nominal' => $nominal,
                    'account_id' => $kategori?->id,
                    'cash_account_id' => $akunKas?->id,
                    'cabang_id' => $cabangId,
                    'user_id' => auth()->id(),
                    'bukti_path' => $buktiPath,
                ]);

                if ($jenis === 'income') {
                    $prefix = 'KM';
                    $debitAccount = $akunKas;
                    $creditAccount = $kategori;
                } elseif ($jenis === 'expense') {
                    $prefix = 'KK';
                    $debitAccount = $kategori;
                    $creditAccount = $akunKas;
                } elseif ($jenis === 'transfer_in') {
                    $prefix = 'TR';
                    $debitAccount = $kasCabang;
                    $creditAccount = $kasPusat;
                } else {
                    $prefix = 'TR';
                    $debitAccount = $kasPusat;
                    $creditAccount = $kasCabang;
                }

                $jurnal = Jurnal::create([
                    'nomor_jurnal' => null,
                    'tanggal' => $data['tanggal'],
                    'deskripsi' => $data['deskripsi'],
                    'cabang_id' => $cabangId,
                    'user_id' => auth()->id(),
                    'status' => 'posted',
                ]);

                $tanggalNomor = date('Ymd', strtotime($data['tanggal']));
                $nomorJurnal = 'JU-' . $tanggalNomor . '-'
                    . str_pad((string) $jurnal->id, 6, '0', STR_PAD_LEFT);

                $jurnal->update(['nomor_jurnal' => $nomorJurnal]);

                $jurnal->details()->create([
                    'account_id' => $debitAccount->id,
                    'debit' => $nominal,
                    'kredit' => 0,
                    'keterangan' => $data['deskripsi'],
                ]);

                $jurnal->details()->create([
                    'account_id' => $creditAccount->id,
                    'debit' => 0,
                    'kredit' => $nominal,
                    'keterangan' => $data['deskripsi'],
                ]);

                $nomorTransaksi = $prefix . '-' . $tanggalNomor . '-'
                    . str_pad((string) $transaksi->id, 6, '0', STR_PAD_LEFT);

                $transaksi->update([
                    'nomor_transaksi' => $nomorTransaksi,
                    'jurnal_id' => $jurnal->id,
                ]);

                return $transaksi;
            });
        } catch (Throwable $e) {
            if ($buktiPath) {
                Storage::disk('public')->delete($buktiPath);
            }

            throw $e;
        }

        return redirect()
            ->route('kas.show', $transaksi)
            ->with('success', 'Transaksi kas dan jurnal otomatis berhasil disimpan.');
    }

    public function show(KasTransaction $cashTransaction)
    {
        $cabangId = $this->cabangAktif();

        abort_unless((int) $cashTransaction->cabang_id === $cabangId, 404);

        $cashTransaction->load([
            'account',
            'cashAccount',
            'jurnal.details.account',
            'user',
        ]);

        return view('kas.show', compact('cashTransaction'));
    }
}
