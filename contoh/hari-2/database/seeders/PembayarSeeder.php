<?php

namespace Database\Seeders;

use App\Models\Pembayar;
use Illuminate\Database\Seeder;

class PembayarSeeder extends Seeder
{
    /**
     * Masukkan 10 rekod pembayar contoh.
     */
    public function run(): void
    {
        $pembayars = [
            [
                'nama' => 'Ahmad bin Abdullah',
                'no_ic' => '850101145678',
                'alamat' => 'No. 12, Jalan Sultan Badlishah, 05000 Alor Setar, Kedah',
                'no_tel' => '0124567890',
                'email' => 'ahmad@email.com',
                'pekerjaan' => 'Guru',
                'pendapatan_bulanan' => 4500.00,
            ],
            [
                'nama' => 'Siti Nurhaliza binti Mohd Razali',
                'no_ic' => '900215085432',
                'alamat' => 'Lot 5, Taman Aman, 06000 Jitra, Kedah',
                'no_tel' => '0135678901',
                'email' => 'siti.nurhaliza@email.com',
                'pekerjaan' => 'Jururawat',
                'pendapatan_bulanan' => 3800.00,
            ],
            [
                'nama' => 'Mohd Faizal bin Hassan',
                'no_ic' => '880730021234',
                'alamat' => 'No. 8, Lorong Mawar 3, Taman Sejahtera, 08000 Sungai Petani, Kedah',
                'no_tel' => '0196789012',
                'email' => 'faizal.hassan@email.com',
                'pekerjaan' => 'Jurutera',
                'pendapatan_bulanan' => 6500.00,
            ],
            [
                'nama' => 'Nor Azizah binti Ibrahim',
                'no_ic' => '920503145566',
                'alamat' => 'Blok C-3-12, Pangsapuri Damai, 05150 Alor Setar, Kedah',
                'no_tel' => '0167890123',
                'email' => null,
                'pekerjaan' => 'Suri rumah',
                'pendapatan_bulanan' => null,
            ],
            [
                'nama' => 'Ismail bin Yusof',
                'no_ic' => '780420025678',
                'alamat' => 'No. 45, Kampung Baru, 06700 Pendang, Kedah',
                'no_tel' => '0148901234',
                'email' => 'ismail.yusof@email.com',
                'pekerjaan' => 'Peniaga',
                'pendapatan_bulanan' => 5200.00,
            ],
            [
                'nama' => 'Fatimah binti Omar',
                'no_ic' => '950812146789',
                'alamat' => 'No. 22, Jalan Pegawai, 05100 Alor Setar, Kedah',
                'no_tel' => '0179012345',
                'email' => 'fatimah.omar@email.com',
                'pekerjaan' => 'Pegawai Tadbir',
                'pendapatan_bulanan' => 4200.00,
            ],
            [
                'nama' => 'Razak bin Che Mat',
                'no_ic' => '830625087890',
                'alamat' => 'Lot 18, Felda Sungai Tiang, 09300 Kuala Ketil, Kedah',
                'no_tel' => '0110123456',
                'email' => null,
                'pekerjaan' => 'Petani',
                'pendapatan_bulanan' => 2800.00,
            ],
            [
                'nama' => 'Nurul Huda binti Zakaria',
                'no_ic' => '970118141234',
                'alamat' => 'No. 3, Jalan Putra, Taman Putra, 05200 Alor Setar, Kedah',
                'no_tel' => '0181234567',
                'email' => 'nurul.huda@email.com',
                'pekerjaan' => 'Akauntan',
                'pendapatan_bulanan' => 5800.00,
            ],
            [
                'nama' => 'Hassan bin Daud',
                'no_ic' => '800903025432',
                'alamat' => 'No. 67, Jalan Langgar, 05460 Alor Setar, Kedah',
                'no_tel' => '0192345678',
                'email' => 'hassan.daud@email.com',
                'pekerjaan' => 'Pemandu',
                'pendapatan_bulanan' => 2500.00,
            ],
            [
                'nama' => 'Aminah binti Sulaiman',
                'no_ic' => '860417145678',
                'alamat' => 'No. 9, Taman Harmoni, 06550 Changlun, Kedah',
                'no_tel' => '0163456789',
                'email' => 'aminah.sulaiman@email.com',
                'pekerjaan' => 'Pensyarah',
                'pendapatan_bulanan' => 7200.00,
            ],
        ];

        foreach ($pembayars as $pembayar) {
            Pembayar::create($pembayar);
        }
    }
}
