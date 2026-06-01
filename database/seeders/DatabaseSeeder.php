<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Logbook;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ── Clean Existing Data (Preserving Admins) ──
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Logbook::truncate();
        Attendance::truncate();
        User::where('role', 'user')->forceDelete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ── Ensure Placeholders Exist on Disk ──
        $this->createDummyImage(storage_path('app/public/attendances/dummy.jpg'), 150, 150, 'Foto Absensi');
        $this->createDummyImage(storage_path('app/public/logbooks/dummy_kegiatan.jpg'), 150, 100, 'Foto Kegiatan');

        // ── Ensure Admin Exists ──
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $admin = User::create([
                'username' => 'admin',
                'name' => 'Admin Mentor',
                'email' => 'admin@gmail.com',
                'no_telp' => '081234567890',
                'asal_instansi' => 'Attendora Platform',
                'role' => 'admin',
                'face_data' => 'dummy_admin_face_data',
                'password' => 'admin123',
            ]);
        }

        // ── 15 Real Interns ──
        $interns = [
            ['name' => 'Abdul Rauf Ghufron', 'username' => 'raufg', 'email' => 'raufg@student.ui.ac.id', 'instansi' => 'Universitas Indonesia'],
            ['name' => 'Ahmad Fauzi', 'username' => 'fauzia', 'email' => 'fauzia@student.upnvj.ac.id', 'instansi' => 'UPN Veteran Jakarta'],
            ['name' => 'Dinda Putri Anindya', 'username' => 'dindap', 'email' => 'dindap@student.gunadarma.ac.id', 'instansi' => 'Universitas Gunadarma'],
            ['name' => 'Rizky Ramadhan', 'username' => 'rizkyr', 'email' => 'rizkyr@student.bsi.ac.id', 'instansi' => 'Universitas BSI'],
            ['name' => 'Nabila Salsabila', 'username' => 'nabilas', 'email' => 'nabilas@student.pnj.ac.id', 'instansi' => 'Politeknik Negeri Jakarta'],
            ['name' => 'Muhammad Fikri', 'username' => 'fikrim', 'email' => 'fikrim@student.ui.ac.id', 'instansi' => 'Universitas Indonesia'],
            ['name' => 'Aulia Rahma', 'username' => 'auliar', 'email' => 'auliar@student.upnvj.ac.id', 'instansi' => 'UPN Veteran Jakarta'],
            ['name' => 'Raka Pratama', 'username' => 'rakap', 'email' => 'rakap@student.gunadarma.ac.id', 'instansi' => 'Universitas Gunadarma'],
            ['name' => 'Siti Nurhaliza', 'username' => 'sitin', 'email' => 'sitin@student.bsi.ac.id', 'instansi' => 'Universitas BSI'],
            ['name' => 'M. Alif Akbar', 'username' => 'alifakbar', 'email' => 'alifakbar@student.pnj.ac.id', 'instansi' => 'Politeknik Negeri Jakarta'],
            ['name' => 'Zahra Khairunnisa', 'username' => 'zahrak', 'email' => 'zahrak@student.ui.ac.id', 'instansi' => 'Universitas Indonesia'],
            ['name' => 'Fajar Nugraha', 'username' => 'fajarn', 'email' => 'fajarn@student.upnvj.ac.id', 'instansi' => 'UPN Veteran Jakarta'],
            ['name' => 'Intan Permata', 'username' => 'intanp', 'email' => 'intanp@student.gunadarma.ac.id', 'instansi' => 'Universitas Gunadarma'],
            ['name' => 'Reza Maulana', 'username' => 'rezam', 'email' => 'rezam@student.bsi.ac.id', 'instansi' => 'Universitas BSI'],
            ['name' => 'Putri Maharani', 'username' => 'putrim', 'email' => 'putrim@student.pnj.ac.id', 'instansi' => 'Politeknik Negeri Jakarta'],
        ];

        $hashedPassword = Hash::make('password');
        $createdUsers = [];

        foreach ($interns as $intern) {
            $createdUsers[] = User::create([
                'username' => $intern['username'],
                'name' => $intern['name'],
                'email' => $intern['email'],
                'no_telp' => '08' . rand(111111111, 999999999),
                'asal_instansi' => $intern['instansi'],
                'role' => 'user',
                'face_data' => 'dummy_face_data',
                'password' => $hashedPassword,
            ]);
        }

        // Call HolidaySeeder to ensure holidays are in database
        $this->call(HolidaySeeder::class);

        // ── 4 Months of History ──
        $startDate = Carbon::now()->subMonths(4)->startOfDay();
        $endDate = Carbon::now()->startOfDay();

        // 20 Realistic Tasks (> 100 characters)
        $logbookTasks = [
            "Melakukan input data peserta magang baru ke dalam sistem absensi dan memverifikasi kelengkapan profil masing-masing pengguna agar tidak ada kesalahan data.",
            "Melakukan validasi dokumen persyaratan administrasi magang milik peserta dari berbagai universitas untuk diserahkan ke divisi SDM.",
            "Mengembangkan fitur absensi berbasis lokasi (geolocation) menggunakan Leaflet JS untuk mendeteksi koordinat presisi siswa saat melakukan clock-in.",
            "Menganalisis log error pada server dan melakukan bug fixing terhadap masalah autentikasi session yang sering logout sendiri pada beberapa browser.",
            "Melakukan testing aplikasi secara menyeluruh pada modul absensi dan logbook untuk memastikan alur pengajuan persetujuan berjalan tanpa kendala.",
            "Merancang desain dashboard admin dan user yang lebih premium dan bersih menggunakan Figma, fokus pada kemudahan navigasi dan visualisasi grafik.",
            "Melakukan analisis kebutuhan sistem untuk modul laporan bulanan guna menentukan field apa saja yang perlu diekspor ke dalam format Excel.",
            "Melakukan entry data harian terkait jadwal libur nasional dan mencocokkannya dengan kalender akademik untuk sinkronisasi sistem absensi.",
            "Melakukan monitoring kinerja server dan utilitas CPU/RAM selama jam masuk absensi pagi untuk memastikan tidak terjadi keterlambatan respon sistem.",
            "Menyusun laporan mingguan mengenai progres pekerjaan tim pengembangan aplikasi dan mempresentasikannya kepada pembimbing lapangan.",
            "Membuat dokumentasi teknis API endpoint sistem absensi serta petunjuk penggunaan (user guide) bagi admin dan peserta magang baru.",
            "Mengikuti rapat evaluasi mingguan bersama seluruh peserta magang dan mentor untuk membahas kendala teknis serta pembagian tugas selanjutnya.",
            "Melakukan integrasi API pihak ketiga untuk layanan sinkronisasi waktu server agar tidak bisa dimanipulasi oleh waktu lokal perangkat user.",
            "Melakukan pengujian fungsionalitas fitur login multi-role (admin dan user) serta memvalidasi pesan kesalahan saat input data tidak sesuai.",
            "Menguji fitur pengisian logbook harian termasuk validasi ukuran file foto kegiatan yang diunggah oleh peserta magang agar tetap optimal.",
            "Melakukan backup database absensi secara terjadwal serta menguji proses restorasi data untuk mengantisipasi potensi kegagalan sistem.",
            "Melakukan perbaikan UI/UX pada halaman login dan dashboard dengan menambahkan transisi halus serta palet warna monokrom yang lebih elegan.",
            "Menganalisis indeks tabel absensi dan melakukan optimasi query database agar proses loading grafik dashboard menjadi jauh lebih cepat.",
            "Membantu admin melakukan review data kehadiran mingguan peserta magang dan menandai absensi yang memerlukan tindak lanjut khusus.",
            "Memulai penyusunan draf laporan akhir magang yang merangkum seluruh kontribusi proyek pengembangan sistem absensi selama periode magang."
        ];

        // Comments
        $approvedComments = [
            "Kerja sudah baik, pertahankan.",
            "Sudah sesuai instruksi.",
            "Hasil pekerjaan sangat baik.",
            "Sangat bagus, pengerjaan cepat dan tepat waktu.",
            "Laporan harian sangat detail dan progresnya terlihat jelas.",
            "Pekerjaan diselesaikan dengan baik dan rapi."
        ];

        $rejectedComments = [
            "Dokumentasi perlu dilengkapi.",
            "Perbaiki detail laporan harian.",
            "Deskripsi kurang detail, silakan jelaskan lebih rinci apa yang dikerjakan.",
            "Foto kegiatan kurang jelas atau tidak sesuai, tolong diunggah ulang.",
            "Aktivitas tidak sesuai dengan target mingguan, silakan direvisi."
        ];

        // Distinct base64 transparent signatures
        $adminSignature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAAAyCAYAAACqKw8UAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH5gYMDQcKCBgVDAAAAB1pVFh0Q29tbWVudAAAAAAAQ3JlYXRlZCB3aXRoIEdJTVBkLmUHAAADSUlEQVR42u2aXUiTYRjHf+/77reZ27RpaWmhUUGFFhFCF3XRXSiGXaVdRFd1E3SRXUQQXnQTRFeFdRGFhVBhF0GEWUBURCGYmS/N2ea+77tOC0U/Nqe1NWev+d944Pnhgff9v+d9n+e8z3nOU1BQUFBQUFBQUFD+D0g05gJomqYoikJKiWmaKKX291sU7bVomqYoikJKiWmaKKX291sU7bVomqYoikJKWbT3bE/T1B91Wdb9nFPD97M9dK89GnMBNI2UkpQSXdfD3d93NMb4f/v7vV3N/+2P/+/4w/8Q/2P8gf8x/sD/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8j/EH/sf4A/9j/IH/Mf7A/1ixv7Ue/P0s4H+KxljR3n7Of5t/GX8A42mEAAAAAElFTkSuQmCC';
        $pembimbingSignature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAAAyCAYAAACqKw8UAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH5gYMDQcKCBgVDAAAAB1pVFh0Q29tbWVudAAAAAAAQ3JlYXRlZCB3aXRoIEdJTVBkLmUHAAADSUlEQVR42u3az0tUURgH8O+578yMZv7IaEqLqChwEUTYRlCif0GLVq1atWrVIrRoEUTQopUQtGgVREQhWkiFIIoiM6fJ+eG8e02LqPkwp7U15+7v5oHnhwfe7z3n3HPuuY8IIYQQQgghhBBCCH8HUp/bAaVURCnFKaU4pRRVVeW7rV5rD5VSUUr5hFKKU4pTSlFVVb7b6rX2UClVEaWUp5TiKaWYqqr8f9eT/V0l32vzevtP3Uv32n9j24HaRkpJSklKSerq6vJ6X5Ikyf733f3+v/2t/+2v7/92j9/9jfHfxx/4H+MP/I/xB/7H+AP/Y/yB/zH+wP8Yf+B/jD/wP9rf+N9aDv5sF/D2NsXy9vc3z71vvGf8BmqiHjP+AzVRD/gAAAABJRU5ErkJggg==';

        $totalDays = (int) $startDate->diffInDays($endDate);

        for ($d = $totalDays; $d >= 0; $d--) {
            $currentDate = Carbon::now('Asia/Jakarta')->subDays($d);
            $dateStr = $currentDate->toDateString();

            // Skip weekends
            if (\App\Helpers\HolidayHelper::isWeekend($currentDate)) {
                continue;
            }

            // Check if holiday
            $holidayName = \App\Helpers\HolidayHelper::getHolidayName($currentDate);

            foreach ($createdUsers as $user) {
                if ($holidayName !== null) {
                    // Create national holiday record
                    Attendance::create([
                        'user_id' => $user->id,
                        'tanggal' => $dateStr,
                        'jam_masuk' => null,
                        'jam_pulang' => null,
                        'latitude' => -6.2088,
                        'longitude' => 106.8456,
                        'foto' => null,
                        'status' => 'libur_nasional',
                        'device_info' => 'Holiday System Seeder',
                    ]);
                    continue;
                }

                // Roll status: 82% tepat_waktu, 12% terlambat, 6% absent/sakit/izin
                $roll = rand(1, 100);

                if ($roll <= 82) {
                    $status = 'tepat_waktu';
                } elseif ($roll <= 94) {
                    $status = 'terlambat';
                } else {
                    // Absent distribution: 50% sakit, 50% izin
                    $status = rand(1, 2) === 1 ? 'sakit' : 'izin';
                }

                $lat = -6.2088 + (rand(-1000, 1000) / 2000000);
                $lon = 106.8456 + (rand(-1000, 1000) / 2000000);

                $jamMasuk = null;
                $jamPulang = null;
                $deviceInfo = 'Chrome on Windows 11 (PC)';

                if ($status === 'tepat_waktu') {
                    // Clock in: 07:40 - 08:00
                    $min = rand(40, 59);
                    $sec = rand(0, 59);
                    $jamMasuk = sprintf('07:%02d:%02d', $min, $sec);

                    // Clock out: 16:00 - 18:00
                    $hourOut = rand(16, 17);
                    $minOut = rand(0, 59);
                    $secOut = rand(0, 59);
                    $jamPulang = sprintf('%02d:%02d:%02d', $hourOut, $minOut, $secOut);
                } elseif ($status === 'terlambat') {
                    // Clock in: 08:05 - 09:15
                    $hour = rand(8, 9);
                    $min = $hour === 8 ? rand(5, 59) : rand(0, 15);
                    $sec = rand(0, 59);
                    $jamMasuk = sprintf('%02d:%02d:%02d', $hour, $min, $sec);

                    // Clock out: 16:00 - 18:00
                    $hourOut = rand(16, 17);
                    $minOut = rand(0, 59);
                    $secOut = rand(0, 59);
                    $jamPulang = sprintf('%02d:%02d:%02d', $hourOut, $minOut, $secOut);
                } else {
                    // Sickness or Permission
                    $reasons = [
                        'sakit' => ['Sakit Demam Tinggi', 'Sakit Flu & Batuk', 'Sakit Pencernaan'],
                        'izin' => ['Izin Kampus - Seminar Kerja Praktek', 'Izin Kampus - Wisuda Kakak', 'Izin Keperluan Keluarga', 'Izin Wawancara Tugas Akhir']
                    ];
                    $selectedReason = $reasons[$status][array_rand($reasons[$status])];
                    $deviceInfo = 'Alasan: ' . $selectedReason;
                }

                // Save attendance record
                Attendance::create([
                    'user_id' => $user->id,
                    'tanggal' => $dateStr,
                    'jam_masuk' => $jamMasuk,
                    'jam_pulang' => $jamPulang,
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'foto' => in_array($status, ['tepat_waktu', 'terlambat']) ? 'attendances/dummy.jpg' : null,
                    'status' => $status,
                    'device_info' => $deviceInfo,
                ]);

                // Save Logbook for present days (tepat_waktu or terlambat)
                if (in_array($status, ['tepat_waktu', 'terlambat'])) {
                    // Roll approval: 85% approved, 10% pending, 5% rejected
                    $lbRoll = rand(1, 100);
                    if ($lbRoll <= 85) {
                        $lbStatus = 'approved';
                        $adminId = $admin->id;
                        $comment = $approvedComments[array_rand($approvedComments)];
                        $ttdAdmin = $adminSignature;
                        $ttdPembimbing = $pembimbingSignature;
                    } elseif ($lbRoll <= 95) {
                        $lbStatus = 'pending';
                        $adminId = null;
                        $comment = null;
                        $ttdAdmin = null;
                        $ttdPembimbing = null;
                    } else {
                        $lbStatus = 'rejected';
                        $adminId = $admin->id;
                        $comment = $rejectedComments[array_rand($rejectedComments)];
                        $ttdAdmin = null;
                        $ttdPembimbing = null;
                    }

                    Logbook::create([
                        'user_id' => $user->id,
                        'tanggal' => $dateStr,
                        'deskripsi' => $logbookTasks[array_rand($logbookTasks)],
                        'foto_kegiatan' => 'logbooks/dummy_kegiatan.jpg',
                        'status' => $lbStatus,
                        'komentar_pembimbing' => $comment,
                        'tanda_tangan_admin' => $ttdAdmin,
                        'tanda_tangan_pembimbing' => $ttdPembimbing,
                        'admin_id' => $adminId,
                    ]);
                }
            }
        }
    }

    /**
     * Helper to generate dummy JPEG image.
     */
    protected function createDummyImage(string $path, int $width, int $height, string $text): void
    {
        $dir = dirname($path);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        if (function_exists('imagecreatetruecolor')) {
            $im = imagecreatetruecolor($width, $height);
            $bg = imagecolorallocate($im, 240, 240, 240); // Light gray
            imagefill($im, 0, 0, $bg);

            $textColor = imagecolorallocate($im, 80, 80, 80);
            $border = imagecolorallocate($im, 200, 200, 200);

            imagerectangle($im, 0, 0, $width - 1, $height - 1, $border);
            imagestring($im, 3, 10, intval($height / 2) - 8, $text, $textColor);

            imagejpeg($im, $path, 80);
            imagedestroy($im);
        } else {
            $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
            file_put_contents($path, $gif);
        }
    }
}
