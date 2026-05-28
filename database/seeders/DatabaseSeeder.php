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
        // ── Clean Existing Data ──
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Logbook::truncate();
        Attendance::truncate();
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ── Create Placeholder Images on Disk ──
        $this->createDummyImage(storage_path('app/public/attendances/dummy.jpg'), 150, 150, 'Foto Absensi');
        $this->createDummyImage(storage_path('app/public/logbooks/dummy_kegiatan.jpg'), 150, 100, 'Foto Kegiatan');

        // ── 1. Create Default Admin User ──
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

        // ── 2. User Data (5 Mahasiswa & 10 Siswa) ──
        $students = [
            // Mahasiswa (5)
            ['name' => 'Rian Hidayat', 'username' => 'rianh', 'email' => 'rian.hidayat@student.itb.ac.id', 'no_telp' => '081298765431', 'instansi' => 'Institut Teknologi Bandung'],
            ['name' => 'Aditya Pratama', 'username' => 'adityap', 'email' => 'aditya.pratama@ui.ac.id', 'no_telp' => '081398765432', 'instansi' => 'Universitas Indonesia'],
            ['name' => 'Siti Rahmawati', 'username' => 'sitir', 'email' => 'siti.rahmawati@mail.ugm.ac.id', 'no_telp' => '081498765433', 'instansi' => 'Universitas Gadjah Mada'],
            ['name' => 'Dewi Lestari', 'username' => 'dewil', 'email' => 'dewi.lestari@student.unair.ac.id', 'no_telp' => '081598765434', 'instansi' => 'Universitas Airlangga'],
            ['name' => 'Fahmi Nurahman', 'username' => 'fahmin', 'email' => 'fahmi.nurahman@student.undip.ac.id', 'no_telp' => '081698765435', 'instansi' => 'Universitas Diponegoro'],
            
            // Siswa (10)
            ['name' => 'Budi Santoso', 'username' => 'budis', 'email' => 'budi.santoso@smk.belajar.id', 'no_telp' => '082198765436', 'instansi' => 'SMKN 1 Jakarta'],
            ['name' => 'Indah Permatasari', 'username' => 'indahp', 'email' => 'indah.permata@sma.belajar.id', 'no_telp' => '082298765437', 'instansi' => 'SMAN 8 Jakarta'],
            ['name' => 'Agus Wijaya', 'username' => 'agusw', 'email' => 'agus.wijaya@smk.belajar.id', 'no_telp' => '082398765438', 'instansi' => 'SMKN 4 Bandung'],
            ['name' => 'Larasati Putri', 'username' => 'larasatip', 'email' => 'laras.putri@sma.belajar.id', 'no_telp' => '082498765439', 'instansi' => 'SMAN 3 Yogyakarta'],
            ['name' => 'Riko Simanjuntak', 'username' => 'rikos', 'email' => 'riko.simanjuntak@smk.belajar.id', 'no_telp' => '082598765440', 'instansi' => 'SMKN 2 Surabaya'],
            ['name' => 'Fitri Handayani', 'username' => 'fitrih', 'email' => 'fitri.handayani@sma.belajar.id', 'no_telp' => '082698765441', 'instansi' => 'SMAN 1 Semarang'],
            ['name' => 'Eko Prasetyo', 'username' => 'ekop', 'email' => 'eko.prasetyo@smk.belajar.id', 'no_telp' => '082798765442', 'instansi' => 'SMKN 2 Malang'],
            ['name' => 'Yuni Kartika', 'username' => 'yunik', 'email' => 'yuni.kartika@sma.belajar.id', 'no_telp' => '082898765443', 'instansi' => 'SMAN 1 Bogor'],
            ['name' => 'Rizal Utama', 'username' => 'rizalu', 'email' => 'rizal.utama@smk.belajar.id', 'no_telp' => '082998765444', 'instansi' => 'SMKN 2 Depok'],
            ['name' => 'Sari Wulandari', 'username' => 'sariw', 'email' => 'sari.wulandari@smk.belajar.id', 'no_telp' => '083098765445', 'instansi' => 'SMKN 1 Tangerang'],
        ];

        $createdUsers = [];
        foreach ($students as $student) {
            $createdUsers[] = User::create([
                'username' => $student['username'],
                'name' => $student['name'],
                'email' => $student['email'],
                'no_telp' => $student['no_telp'],
                'asal_instansi' => $student['instansi'],
                'role' => 'user',
                'face_data' => 'dummy_face_data',
                'password' => 'password',
            ]);
        }

        // ── Call HolidaySeeder ──
        $this->call(HolidaySeeder::class);

        // ── 3. Seeding Attendance & Logbooks for the last 30 days ──
        $startDate = Carbon::now()->subDays(30);
        
        $logbookTasks = [
            "Mengerjakan perbaikan bug pada halaman login dan registrasi",
            "Belajar materi dasar framework Laravel 11 dan implementasi MVC",
            "Membuat desain antarmuka (UI/UX) untuk dashboard menggunakan Figma",
            "Melakukan pengujian fitur (testing) dan menuliskan dokumentasi API",
            "Meeting rutin mingguan bersama mentor membahas progres mingguan",
            "Melakukan input data master mahasiswa ke dalam database",
            "Mempelajari integrasi Leaflet JS untuk tracking lokasi peta absensi",
            "Melakukan review kode (code review) bersama rekan kelompok",
            "Mendesain skema database relasional untuk sistem absensi baru",
            "Membantu mentor menyiapkan materi presentasi laporan kerja bulanan",
            "Membuat fitur ekspor laporan ke format Excel dengan style monokrom",
            "Melakukan debugging error pada middleware autentikasi pengguna",
            "Melakukan instalasi package tambahan dan setup environment baru",
            "Melakukan optimasi performa query database pada dashboard",
            "Merapikan layout blade template dan integrasi CSS vanilla"
        ];

        // Clean transparent base64 PNG signature
        $dummySignature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAAAyCAYAAACqKw8UAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH5gYMDQcKCBgVDAAAAB1pVFh0Q29tbWVudAAAAAAAQ3JlYXRlZCB3aXRoIEdJTVBkLmUHAAADSUlEQVR42u2aXUiTYRjHf+/77reZ27RpaWmhUUGFFhFCF3XRXSiGXaVdRFd1E3SRXUQQXnQTRFeFdRGFhVBhF0GEWUBURCGYmS/N2ea+77tOC0U/Nqe1NWev+d944Pnhgff9v+d9n+e8z3nOU1BQUFBQUFBQUFD+D0g05gJomqYoikJKiWmaKKX291sU7bVomqYoikJKiWmaKKX291sU7bVomqYoikJKWbT3bE/T1B91Wdb9nFPD97M9dK89GnMBNI2UkpQSXdfD3d93NMb4f/v7vV3N/+2P/+/4w/8Q/2P8gf8x/sD/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8j/EH/sf4A/9j/IH/Mf7A/1ixv7Ue/P0s4H+KxljR3n7Of5t/GX8A42mEAAAAAElFTkSuQmCC';

        for ($d = 0; $d <= 30; $d++) {
            $currentDate = $startDate->copy()->addDays($d);
            
            // Skip weekends completely
            if (\App\Helpers\HolidayHelper::isWeekend($currentDate)) {
                continue;
            }
            
            $holidayName = \App\Helpers\HolidayHelper::getHolidayName($currentDate);
            $dateStr = $currentDate->toDateString();
            
            foreach ($createdUsers as $user) {
                if ($holidayName !== null) {
                    // Create attendance record for holiday
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

                // Roll random attendance status:
                // 70% tepat_waktu, 12% terlambat, 8% tidak_hadir, 5% izin, 5% sakit
                $roll = rand(1, 100);
                
                if ($roll <= 70) {
                    $status = 'tepat_waktu';
                } elseif ($roll <= 82) {
                    $status = 'terlambat';
                } elseif ($roll <= 90) {
                    $status = 'tidak_hadir';
                } elseif ($roll <= 95) {
                    $status = 'izin';
                } else {
                    $status = 'sakit';
                }
                
                // Coordinates centered in Jakarta area (-6.2088, 106.8456) with random deviation
                $lat = -6.2088 + (rand(-1000, 1000) / 2000000);
                $lon = 106.8456 + (rand(-1000, 1000) / 2000000);
                
                $jamMasuk = null;
                $jamPulang = null;
                
                if ($status == 'tepat_waktu') {
                    // Clock in: 07:45:00 - 08:15:00
                    $hour = 7;
                    $min = rand(45, 59);
                    if (rand(0, 1) === 1) {
                        $hour = 8;
                        $min = rand(0, 15);
                    }
                    $sec = rand(0, 59);
                    $jamMasuk = sprintf('%02d:%02d:%02d', $hour, $min, $sec);
                    
                    // Clock out: 16:00:00 - 17:30:00
                    $hourOut = rand(16, 17);
                    $minOut = $hourOut === 17 ? rand(0, 30) : rand(0, 59);
                    $secOut = rand(0, 59);
                    $jamPulang = sprintf('%02d:%02d:%02d', $hourOut, $minOut, $secOut);
                } elseif ($status == 'terlambat') {
                    // Clock in: 08:16:00 - 10:00:00
                    $hour = rand(8, 9);
                    $min = $hour === 8 ? rand(16, 59) : rand(0, 59);
                    $sec = rand(0, 59);
                    $jamMasuk = sprintf('%02d:%02d:%02d', $hour, $min, $sec);
                    
                    // Clock out: 16:00:00 - 17:30:00
                    $hourOut = rand(16, 17);
                    $minOut = $hourOut === 17 ? rand(0, 30) : rand(0, 59);
                    $secOut = rand(0, 59);
                    $jamPulang = sprintf('%02d:%02d:%02d', $hourOut, $minOut, $secOut);
                }
                
                // Create attendance record
                Attendance::create([
                    'user_id' => $user->id,
                    'tanggal' => $dateStr,
                    'jam_masuk' => $jamMasuk,
                    'jam_pulang' => $jamPulang,
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'foto' => ($status === 'izin' || $status === 'sakit' || $status === 'tidak_hadir') ? null : 'attendances/dummy.jpg',
                    'status' => $status,
                    'device_info' => 'Chrome on Windows 11 (PC)',
                ]);
                
                // Generate Logbook if the user was present (tepat_waktu or terlambat)
                if ($status === 'tepat_waktu' || $status === 'terlambat') {
                    // Roll logbook review status:
                    // 85% approved, 10% pending, 5% rejected
                    $lbRoll = rand(1, 100);
                    $lbStatus = 'pending';
                    $ttd = null;
                    $adminId = null;
                    
                    if ($lbRoll <= 85) {
                        $lbStatus = 'approved';
                        $ttd = $dummySignature;
                        $adminId = $admin->id;
                    } elseif ($lbRoll <= 95) {
                        $lbStatus = 'pending';
                    } else {
                        $lbStatus = 'rejected';
                    }
                    
                    Logbook::create([
                        'user_id' => $user->id,
                        'tanggal' => $dateStr,
                        'deskripsi' => $logbookTasks[array_rand($logbookTasks)],
                        'foto_kegiatan' => 'logbooks/dummy_kegiatan.jpg',
                        'status' => $lbStatus,
                        'tanda_tangan_admin' => $ttd,
                        'admin_id' => $adminId,
                    ]);
                }
            }
        }
    }

    /**
     * Helper to generate a dummy JPEG image file with custom label.
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
            
            // Render basic text string in center
            imagestring($im, 3, 10, intval($height / 2) - 8, $text, $textColor);
            
            imagejpeg($im, $path, 80);
            imagedestroy($im);
        } else {
            // Fallback: write a 1x1 transparent GIF
            $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
            file_put_contents($path, $gif);
        }
    }
}
