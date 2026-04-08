<?php

namespace Database\Seeders;

use App\Models\Pembayaran;
use Illuminate\Database\Seeder;

class PembayaranSeeder extends Seeder
{
    public function run(): void
    {
        $pembayarans = [
            ['pembayar_id' => 1, 'jenis_zakat_id' => 1, 'jumlah' => 7.00,    'tarikh_bayar' => '2024-03-25', 'cara_bayar' => 'tunai',  'no_resit' => 'ZK-2024-0001', 'status' => 'sah'],
            ['pembayar_id' => 1, 'jenis_zakat_id' => 2, 'jumlah' => 1350.00, 'tarikh_bayar' => '2024-04-01', 'cara_bayar' => 'fpx',    'no_resit' => 'ZK-2024-0002', 'status' => 'sah'],
            ['pembayar_id' => 2, 'jenis_zakat_id' => 1, 'jumlah' => 7.00,    'tarikh_bayar' => '2024-03-25', 'cara_bayar' => 'tunai',  'no_resit' => 'ZK-2024-0003', 'status' => 'sah'],
            ['pembayar_id' => 2, 'jenis_zakat_id' => 2, 'jumlah' => 1140.00, 'tarikh_bayar' => '2024-04-05', 'cara_bayar' => 'online', 'no_resit' => 'ZK-2024-0004', 'status' => 'sah'],
            ['pembayar_id' => 3, 'jenis_zakat_id' => 3, 'jumlah' => 5000.00, 'tarikh_bayar' => '2024-04-10', 'cara_bayar' => 'fpx',    'no_resit' => 'ZK-2024-0005', 'status' => 'sah'],
            ['pembayar_id' => 3, 'jenis_zakat_id' => 2, 'jumlah' => 2550.00, 'tarikh_bayar' => '2024-04-12', 'cara_bayar' => 'kad',    'no_resit' => 'ZK-2024-0006', 'status' => 'pending'],
            ['pembayar_id' => 4, 'jenis_zakat_id' => 1, 'jumlah' => 7.00,    'tarikh_bayar' => '2024-03-26', 'cara_bayar' => 'tunai',  'no_resit' => 'ZK-2024-0007', 'status' => 'sah'],
            ['pembayar_id' => 4, 'jenis_zakat_id' => 2, 'jumlah' => 1560.00, 'tarikh_bayar' => '2024-05-01', 'cara_bayar' => 'fpx',    'no_resit' => 'ZK-2024-0008', 'status' => 'sah'],
            ['pembayar_id' => 5, 'jenis_zakat_id' => 1, 'jumlah' => 7.00,    'tarikh_bayar' => '2024-03-27', 'cara_bayar' => 'tunai',  'no_resit' => 'ZK-2024-0009', 'status' => 'sah'],
            ['pembayar_id' => 6, 'jenis_zakat_id' => 2, 'jumlah' => 1800.00, 'tarikh_bayar' => '2024-05-05', 'cara_bayar' => 'online', 'no_resit' => 'ZK-2024-0010', 'status' => 'sah'],
            ['pembayar_id' => 6, 'jenis_zakat_id' => 4, 'jumlah' => 3500.00, 'tarikh_bayar' => '2024-05-10', 'cara_bayar' => 'fpx',    'no_resit' => 'ZK-2024-0011', 'status' => 'sah'],
            ['pembayar_id' => 7, 'jenis_zakat_id' => 2, 'jumlah' => 2160.00, 'tarikh_bayar' => '2024-05-15', 'cara_bayar' => 'kad',    'no_resit' => 'ZK-2024-0012', 'status' => 'sah'],
            ['pembayar_id' => 7, 'jenis_zakat_id' => 5, 'jumlah' => 1200.00, 'tarikh_bayar' => '2024-06-01', 'cara_bayar' => 'fpx',    'no_resit' => 'ZK-2024-0013', 'status' => 'pending'],
            ['pembayar_id' => 8, 'jenis_zakat_id' => 1, 'jumlah' => 7.00,    'tarikh_bayar' => '2024-03-28', 'cara_bayar' => 'tunai',  'no_resit' => 'ZK-2024-0014', 'status' => 'sah'],
            ['pembayar_id' => 9, 'jenis_zakat_id' => 2, 'jumlah' => 2700.00, 'tarikh_bayar' => '2024-06-05', 'cara_bayar' => 'fpx',    'no_resit' => 'ZK-2024-0015', 'status' => 'sah'],
            ['pembayar_id' => 9, 'jenis_zakat_id' => 3, 'jumlah' => 4500.00, 'tarikh_bayar' => '2024-06-10', 'cara_bayar' => 'online', 'no_resit' => 'ZK-2024-0016', 'status' => 'sah'],
            ['pembayar_id' => 10,'jenis_zakat_id' => 2, 'jumlah' => 3600.00, 'tarikh_bayar' => '2024-06-15', 'cara_bayar' => 'fpx',    'no_resit' => 'ZK-2024-0017', 'status' => 'sah'],
            ['pembayar_id' => 10,'jenis_zakat_id' => 4, 'jumlah' => 8000.00, 'tarikh_bayar' => '2024-06-20', 'cara_bayar' => 'kad',    'no_resit' => 'ZK-2024-0018', 'status' => 'batal'],
            ['pembayar_id' => 5, 'jenis_zakat_id' => 2, 'jumlah' => 750.00,  'tarikh_bayar' => '2024-06-25', 'cara_bayar' => 'tunai',  'no_resit' => 'ZK-2024-0019', 'status' => 'sah'],
            ['pembayar_id' => 3, 'jenis_zakat_id' => 1, 'jumlah' => 7.00,    'tarikh_bayar' => '2024-03-25', 'cara_bayar' => 'tunai',  'no_resit' => 'ZK-2024-0020', 'status' => 'sah'],
        ];

        foreach ($pembayarans as $p) {
            Pembayaran::create($p);
        }
    }
}
