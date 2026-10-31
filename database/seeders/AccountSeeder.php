<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // ASET
            ['1000', 'ASET', 'asset', 'debit', null],
            ['1100', 'Aset Lancar', 'asset', 'debit', '1000'],
            ['1110', 'Kas dan Setara Kas', 'asset', 'debit', '1100'],
            ['1111', 'Kas Cabang', 'asset', 'debit', '1110'],
            ['1112', 'Kas Pusat', 'asset', 'debit', '1110'],
            ['1113', 'Bank', 'asset', 'debit', '1110'],
            ['1200', 'Persediaan', 'asset', 'debit', '1100'],
            ['1210', 'Persediaan Barang Dagang', 'asset', 'debit', '1200'],
            ['1300', 'Piutang Usaha', 'asset', 'debit', '1100'],

            // LIABILITAS
            ['2000', 'LIABILITAS', 'liability', 'credit', null],
            ['2100', 'Liabilitas Jangka Pendek', 'liability', 'credit', '2000'],
            ['2110', 'Utang Usaha', 'liability', 'credit', '2100'],
            ['2120', 'Utang Gaji', 'liability', 'credit', '2100'],

            // EKUITAS
            ['3000', 'EKUITAS', 'equity', 'credit', null],
            ['3100', 'Modal Pemilik', 'equity', 'credit', '3000'],
            ['3200', 'Saldo Laba', 'equity', 'credit', '3000'],

            // PENDAPATAN
            ['4000', 'PENDAPATAN', 'revenue', 'credit', null],
            ['4100', 'Pendapatan Penjualan', 'revenue', 'credit', '4000'],
            ['4200', 'Pendapatan Lainnya', 'revenue', 'credit', '4000'],

            // HARGA POKOK PENJUALAN
            ['5000', 'HARGA POKOK PENJUALAN', 'expense', 'debit', null],
            ['5100', 'Harga Pokok Penjualan', 'expense', 'debit', '5000'],

            // BEBAN OPERASIONAL
            ['6000', 'BEBAN OPERASIONAL', 'expense', 'debit', null],
            ['6100', 'Beban Gaji Karyawan', 'expense', 'debit', '6000'],
            ['6110', 'Beban Keamanan', 'expense', 'debit', '6000'],
            ['6120', 'Beban Sewa Toko', 'expense', 'debit', '6000'],
            ['6130', 'Beban Listrik dan Air', 'expense', 'debit', '6000'],
            ['6140', 'Beban Operasional Toko', 'expense', 'debit', '6000'],
            ['6150', 'Beban Transportasi', 'expense', 'debit', '6000'],
            ['6160', 'Beban Administrasi Bank', 'expense', 'debit', '6000'],
            ['6170', 'Beban Perlengkapan Toko', 'expense', 'debit', '6000'],
        ];

        foreach ($accounts as $data) {
            [$code, $name, $type, $normalBalance, $parentCode] = $data;

            $parentId = $parentCode
                ? Account::where('code', $parentCode)->value('id')
                : null;

            if ($parentCode && !$parentId) {
                throw new \RuntimeException(
                    "Akun induk {$parentCode} tidak ditemukan."
                );
            }

            Account::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'type' => $type,
                    'normal_balance' => $normalBalance,
                    'parent_id' => $parentId,
                    'description' => null,
                    'is_active' => true,
                    'is_system' => true,
                ]
            );
        }
    }
}
