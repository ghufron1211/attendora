<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $holidays = [
            // 2025
            ['tanggal' => '2025-01-01', 'nama_libur' => 'Tahun Baru Masehi', 'tipe' => 'nasional'],
            ['tanggal' => '2025-01-27', 'nama_libur' => 'Isra Mikraj Nabi Muhammad SAW', 'tipe' => 'nasional'],
            ['tanggal' => '2025-01-29', 'nama_libur' => 'Tahun Baru Imlek 2576 Kongzili', 'tipe' => 'nasional'],
            ['tanggal' => '2025-03-29', 'nama_libur' => 'Hari Suci Nyepi (Tahun Baru Saka 1947)', 'tipe' => 'nasional'],
            ['tanggal' => '2025-03-31', 'nama_libur' => 'Hari Raya Idul Fitri 1446 H (Hari Ke-1)', 'tipe' => 'nasional'],
            ['tanggal' => '2025-04-01', 'nama_libur' => 'Hari Raya Idul Fitri 1446 H (Hari Ke-2)', 'tipe' => 'nasional'],
            ['tanggal' => '2025-04-18', 'nama_libur' => 'Wafat Yesus Kristus (Jumat Agung)', 'tipe' => 'nasional'],
            ['tanggal' => '2025-05-01', 'nama_libur' => 'Hari Buruh Internasional', 'tipe' => 'nasional'],
            ['tanggal' => '2025-05-12', 'nama_libur' => 'Hari Raya Waisak 2569 BE', 'tipe' => 'nasional'],
            ['tanggal' => '2025-05-29', 'nama_libur' => 'Kenaikan Yesus Kristus', 'tipe' => 'nasional'],
            ['tanggal' => '2025-06-01', 'nama_libur' => 'Hari Lahir Pancasila', 'tipe' => 'nasional'],
            ['tanggal' => '2025-06-06', 'nama_libur' => 'Hari Raya Idul Adha 1446 H', 'tipe' => 'nasional'],
            ['tanggal' => '2025-06-27', 'nama_libur' => 'Tahun Baru Islam 1447 H', 'tipe' => 'nasional'],
            ['tanggal' => '2025-08-17', 'nama_libur' => 'Hari Kemerdekaan Republik Indonesia', 'tipe' => 'nasional'],
            ['tanggal' => '2025-09-05', 'nama_libur' => 'Maulid Nabi Muhammad SAW', 'tipe' => 'nasional'],
            ['tanggal' => '2025-12-25', 'nama_libur' => 'Hari Raya Natal', 'tipe' => 'nasional'],

            // 2026
            ['tanggal' => '2026-01-01', 'nama_libur' => 'Tahun Baru Masehi', 'tipe' => 'nasional'],
            ['tanggal' => '2026-01-15', 'nama_libur' => 'Isra Mikraj Nabi Muhammad SAW', 'tipe' => 'nasional'],
            ['tanggal' => '2026-02-17', 'nama_libur' => 'Tahun Baru Imlek 2577 Kongzili', 'tipe' => 'nasional'],
            ['tanggal' => '2026-03-19', 'nama_libur' => 'Hari Suci Nyepi (Tahun Baru Saka 1948)', 'tipe' => 'nasional'],
            ['tanggal' => '2026-03-20', 'nama_libur' => 'Hari Raya Idul Fitri 1447 H (Hari Ke-1)', 'tipe' => 'nasional'],
            ['tanggal' => '2026-03-21', 'nama_libur' => 'Hari Raya Idul Fitri 1447 H (Hari Ke-2)', 'tipe' => 'nasional'],
            ['tanggal' => '2026-04-03', 'nama_libur' => 'Wafat Yesus Kristus (Jumat Agung)', 'tipe' => 'nasional'],
            ['tanggal' => '2026-05-01', 'nama_libur' => 'Hari Buruh Internasional', 'tipe' => 'nasional'],
            ['tanggal' => '2026-05-14', 'nama_libur' => 'Kenaikan Yesus Kristus', 'tipe' => 'nasional'],
            ['tanggal' => '2026-05-27', 'nama_libur' => 'Hari Raya Idul Adha 1447 H', 'tipe' => 'nasional'],
            ['tanggal' => '2026-05-31', 'nama_libur' => 'Hari Raya Waisak 2570 BE', 'tipe' => 'nasional'],
            ['tanggal' => '2026-06-01', 'nama_libur' => 'Hari Lahir Pancasila', 'tipe' => 'nasional'],
            ['tanggal' => '2026-06-16', 'nama_libur' => 'Tahun Baru Islam 1448 H', 'tipe' => 'nasional'],
            ['tanggal' => '2026-08-17', 'nama_libur' => 'Hari Kemerdekaan Republik Indonesia', 'tipe' => 'nasional'],
            ['tanggal' => '2026-08-25', 'nama_libur' => 'Maulid Nabi Muhammad SAW', 'tipe' => 'nasional'],
            ['tanggal' => '2026-12-25', 'nama_libur' => 'Hari Raya Natal', 'tipe' => 'nasional'],

            // 2027
            ['tanggal' => '2027-01-01', 'nama_libur' => 'Tahun Baru Masehi', 'tipe' => 'nasional'],
            ['tanggal' => '2027-01-05', 'nama_libur' => 'Isra Mikraj Nabi Muhammad SAW', 'tipe' => 'nasional'],
            ['tanggal' => '2027-02-06', 'nama_libur' => 'Tahun Baru Imlek 2578 Kongzili', 'tipe' => 'nasional'],
            ['tanggal' => '2027-03-09', 'nama_libur' => 'Hari Suci Nyepi (Tahun Baru Saka 1949)', 'tipe' => 'nasional'],
            ['tanggal' => '2027-03-10', 'nama_libur' => 'Hari Raya Idul Fitri 1448 H (Hari Ke-1)', 'tipe' => 'nasional'],
            ['tanggal' => '2027-03-11', 'nama_libur' => 'Hari Raya Idul Fitri 1448 H (Hari Ke-2)', 'tipe' => 'nasional'],
            ['tanggal' => '2027-03-26', 'nama_libur' => 'Wafat Yesus Kristus (Jumat Agung)', 'tipe' => 'nasional'],
            ['tanggal' => '2027-05-01', 'nama_libur' => 'Hari Buruh Internasional', 'tipe' => 'nasional'],
            ['tanggal' => '2027-05-06', 'nama_libur' => 'Kenaikan Yesus Kristus', 'tipe' => 'nasional'],
            ['tanggal' => '2027-05-17', 'nama_libur' => 'Hari Raya Idul Adha 1448 H', 'tipe' => 'nasional'],
            ['tanggal' => '2027-05-20', 'nama_libur' => 'Hari Raya Waisak 2571 BE', 'tipe' => 'nasional'],
            ['tanggal' => '2027-06-01', 'nama_libur' => 'Hari Lahir Pancasila', 'tipe' => 'nasional'],
            ['tanggal' => '2027-06-06', 'nama_libur' => 'Tahun Baru Islam 1449 H', 'tipe' => 'nasional'],
            ['tanggal' => '2027-08-15', 'nama_libur' => 'Maulid Nabi Muhammad SAW', 'tipe' => 'nasional'],
            ['tanggal' => '2027-08-17', 'nama_libur' => 'Hari Kemerdekaan Republik Indonesia', 'tipe' => 'nasional'],
            ['tanggal' => '2027-12-25', 'nama_libur' => 'Hari Raya Natal', 'tipe' => 'nasional'],

            // 2028
            ['tanggal' => '2028-01-01', 'nama_libur' => 'Tahun Baru Masehi', 'tipe' => 'nasional'],
            ['tanggal' => '2028-01-26', 'nama_libur' => 'Tahun Baru Imlek 2579 Kongzili', 'tipe' => 'nasional'],
            ['tanggal' => '2028-02-23', 'nama_libur' => 'Isra Mikraj Nabi Muhammad SAW', 'tipe' => 'nasional'],
            ['tanggal' => '2028-02-26', 'nama_libur' => 'Hari Raya Idul Fitri 1449 H (Hari Ke-1)', 'tipe' => 'nasional'],
            ['tanggal' => '2028-02-27', 'nama_libur' => 'Hari Raya Idul Fitri 1449 H (Hari Ke-2)', 'tipe' => 'nasional'],
            ['tanggal' => '2028-03-26', 'nama_libur' => 'Hari Suci Nyepi (Tahun Baru Saka 1950)', 'tipe' => 'nasional'],
            ['tanggal' => '2028-04-14', 'nama_libur' => 'Wafat Yesus Kristus (Jumat Agung)', 'tipe' => 'nasional'],
            ['tanggal' => '2028-05-01', 'nama_libur' => 'Hari Buruh Internasional', 'tipe' => 'nasional'],
            ['tanggal' => '2028-05-09', 'nama_libur' => 'Hari Raya Waisak 2572 BE', 'tipe' => 'nasional'],
            ['tanggal' => '2028-05-25', 'nama_libur' => 'Kenaikan Yesus Kristus', 'tipe' => 'nasional'],
            ['tanggal' => '2028-06-01', 'nama_libur' => 'Hari Lahir Pancasila', 'tipe' => 'nasional'],
            ['tanggal' => '2028-06-06', 'nama_libur' => 'Hari Raya Idul Adha 1449 H', 'tipe' => 'nasional'],
            ['tanggal' => '2028-06-25', 'nama_libur' => 'Tahun Baru Islam 1450 H', 'tipe' => 'nasional'],
            ['tanggal' => '2028-08-03', 'nama_libur' => 'Maulid Nabi Muhammad SAW', 'tipe' => 'nasional'],
            ['tanggal' => '2028-08-17', 'nama_libur' => 'Hari Kemerdekaan Republik Indonesia', 'tipe' => 'nasional'],
            ['tanggal' => '2028-12-25', 'nama_libur' => 'Hari Raya Natal', 'tipe' => 'nasional'],
        ];

        foreach ($holidays as $h) {
            Holiday::updateOrCreate(
                ['tanggal' => $h['tanggal']],
                ['nama_libur' => $h['nama_libur'], 'tipe' => $h['tipe']]
            );
        }
    }
}
