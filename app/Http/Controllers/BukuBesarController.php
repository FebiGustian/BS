<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BukuBesarController extends Controller
{
    public function index(Request $request)
    {
        $cabangId = (int) session('cabang_id');

        if (!$cabangId || !DB::table('cabangs')->where('id', $cabangId)->exists()) {
            abort(403, 'Cabang aktif belum dipilih.');
        }

        $filters = $request->validate([
            'account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ]);

        $accounts = Account::where('is_active', true)
            ->orderBy('code')
            ->get();

        $account = null;
        $rows = collect();
        $openingBalance = 0;
        $totalDebit = 0;
        $totalKredit = 0;

        if (!empty($filters['account_id'])) {
            $account = Account::findOrFail($filters['account_id']);

            if (!$account->is_active) {
                abort(404);
            }

            $normalBalance = strtoupper($account->normal_balance);
            $normalIsDebit = $normalBalance === 'DEBIT';

            $baseQuery = DB::table('detail_jurnals')
                ->join('jurnals', 'jurnals.id', '=', 'detail_jurnals.jurnal_id')
                ->where('detail_jurnals.account_id', $account->id)
                ->where('jurnals.cabang_id', $cabangId)
                ->where('jurnals.status', 'posted');

            // Hitung saldo awal dari transaksi sebelum tanggal mulai.
            if (!empty($filters['tanggal_mulai'])) {
                $opening = (clone $baseQuery)
                    ->whereDate('jurnals.tanggal', '<', $filters['tanggal_mulai'])
                    ->selectRaw(
                        'COALESCE(SUM(detail_jurnals.debit), 0) as debit_total,
                         COALESCE(SUM(detail_jurnals.kredit), 0) as kredit_total'
                    )
                    ->first();

                $openingDebit = (float) $opening->debit_total;
                $openingKredit = (float) $opening->kredit_total;

                $openingBalance = $normalIsDebit
                    ? $openingDebit - $openingKredit
                    : $openingKredit - $openingDebit;
            }

            $query = clone $baseQuery;

            if (!empty($filters['tanggal_mulai'])) {
                $query->whereDate('jurnals.tanggal', '>=', $filters['tanggal_mulai']);
            }

            if (!empty($filters['tanggal_akhir'])) {
                $query->whereDate('jurnals.tanggal', '<=', $filters['tanggal_akhir']);
            }

            $rows = $query
                ->select([
                    'detail_jurnals.id as detail_id',
                    'detail_jurnals.debit',
                    'detail_jurnals.kredit',
                    'detail_jurnals.keterangan as keterangan_detail',
                    'jurnals.tanggal',
                    'jurnals.nomor_jurnal',
                    'jurnals.deskripsi',
                ])
                ->orderBy('jurnals.tanggal')
                ->orderBy('jurnals.id')
                ->orderBy('detail_jurnals.id')
                ->get();

            $runningBalance = $openingBalance;

            foreach ($rows as $row) {
                $debit = (float) $row->debit;
                $kredit = (float) $row->kredit;

                $runningBalance += $normalIsDebit
                    ? $debit - $kredit
                    : $kredit - $debit;

                $row->saldo = abs($runningBalance);
                $row->saldo_side = $runningBalance >= 0
                    ? ($normalIsDebit ? 'D' : 'K')
                    : ($normalIsDebit ? 'K' : 'D');

                $totalDebit += $debit;
                $totalKredit += $kredit;
            }
        }

        return view('buku-besar.index', compact(
            'accounts',
            'account',
            'rows',
            'openingBalance',
            'totalDebit',
            'totalKredit'
        ));
    }
}
