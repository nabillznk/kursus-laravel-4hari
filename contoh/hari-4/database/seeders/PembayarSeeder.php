<?php

namespace Database\Seeders;

use App\Models\Pembayar;
use Illuminate\Database\Seeder;

class PembayarSeeder extends Seeder
{
    public function run(): void
    {
        $pembayars = [
            ['nama' => 'Ahmad bin Ismail',      'no_ic' => '850315085123', 'alamat' => 'No 12, Jalan Putra, 05000 Alor Setar, Kedah',         'no_tel' => '0124567890', 'email' => 'ahmad@email.com',   'pekerjaan' => 'Guru',              'pendapatan_bulanan' => 4500.00],
            ['nama' => 'Siti Aminah binti Yusof','no_ic' => '900721065432', 'alamat' => 'Lot 5, Taman Aman, 06000 Jitra, Kedah',               'no_tel' => '0135678901', 'email' => 'siti@email.com',    'pekerjaan' => 'Jururawat',         'pendapatan_bulanan' => 3800.00],
            ['nama' => 'Mohd Razak bin Abdullah','no_ic' => '780505025678', 'alamat' => 'No 8, Lorong Bakti, 08000 Sungai Petani, Kedah',      'no_tel' => '0146789012', 'email' => 'razak@email.com',   'pekerjaan' => 'Peniaga',           'pendapatan_bulanan' => 8500.00],
            ['nama' => 'Fatimah binti Hassan',   'no_ic' => '880912045987', 'alamat' => 'Blok A-3, Flat Sri Aman, 05100 Alor Setar, Kedah',    'no_tel' => '0157890123', 'email' => 'fatimah@email.com', 'pekerjaan' => 'Pegawai Kerajaan',  'pendapatan_bulanan' => 5200.00],
            ['nama' => 'Ismail bin Othman',      'no_ic' => '750228035456', 'alamat' => 'No 22, Jalan Langgar, 05460 Alor Setar, Kedah',       'no_tel' => '0168901234', 'email' => null,                'pekerjaan' => 'Petani',            'pendapatan_bulanan' => 2500.00],
            ['nama' => 'Nor Azizah binti Kamal', 'no_ic' => '920415075321', 'alamat' => 'Taman Sejahtera, 06550 Changlun, Kedah',              'no_tel' => '0179012345', 'email' => 'azizah@email.com',  'pekerjaan' => 'Akauntan',          'pendapatan_bulanan' => 6000.00],
            ['nama' => 'Zulkifli bin Mohamad',   'no_ic' => '830607015789', 'alamat' => 'No 3, Jalan Datuk Kumbar, 05250 Alor Setar, Kedah',   'no_tel' => '0180123456', 'email' => 'zul@email.com',     'pekerjaan' => 'Jurutera',          'pendapatan_bulanan' => 7200.00],
            ['nama' => 'Hasnah binti Ibrahim',   'no_ic' => '860130095654', 'alamat' => 'Kampung Baru, 06800 Kuala Nerang, Kedah',              'no_tel' => '0191234567', 'email' => null,                'pekerjaan' => 'Suri Rumah',        'pendapatan_bulanan' => null],
            ['nama' => 'Kamarulzaman bin Jusoh', 'no_ic' => '700819055432', 'alamat' => 'No 15, Taman Indah, 09000 Kulim, Kedah',              'no_tel' => '0112345678', 'email' => 'kamal@email.com',   'pekerjaan' => 'Pengurus',          'pendapatan_bulanan' => 9000.00],
            ['nama' => 'Ramlah binti Sulaiman',  'no_ic' => '810425045876', 'alamat' => 'Lot 10, Jalan Pegawai, 06600 Kuala Kedah, Kedah',     'no_tel' => '0123456789', 'email' => 'ramlah@email.com',  'pekerjaan' => 'Doktor',            'pendapatan_bulanan' => 12000.00],
        ];

        foreach ($pembayars as $p) {
            Pembayar::create($p);
        }
    }
}
