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

        // ── 34 Official Interns ──
        $interns = [
            // SMKN 71 Jakarta (DKV) - 2025-09-01 to 2025-12-31
            ['name' => 'Dhanisa Nur Olistya F.', 'username' => 'dhanisanur', 'email' => 'dhanisa.nur@smkn71.sch.id', 'instansi' => 'SMKN 71 Jakarta', 'major' => 'dkv', 'start' => '2025-09-01', 'end' => '2025-12-31'],
            ['name' => 'Devina Reiska Putri', 'username' => 'devinar', 'email' => 'devina.reiska@smkn71.sch.id', 'instansi' => 'SMKN 71 Jakarta', 'major' => 'dkv', 'start' => '2025-09-01', 'end' => '2025-12-31'],
            ['name' => 'Maheswari Andhara Sutikno', 'username' => 'maheswaria', 'email' => 'maheswari.andhara@smkn71.sch.id', 'instansi' => 'SMKN 71 Jakarta', 'major' => 'dkv', 'start' => '2025-09-01', 'end' => '2025-12-31'],
            ['name' => 'Najma Aula Milady', 'username' => 'najmaa', 'email' => 'najma.aula@smkn71.sch.id', 'instansi' => 'SMKN 71 Jakarta', 'major' => 'dkv', 'start' => '2025-09-01', 'end' => '2025-12-31'],
            
            // SMKN 71 Jakarta (RPL) - 2025-09-01 to 2025-12-31
            ['name' => 'Adela Suci Wulandari Hasibuan', 'username' => 'adelas', 'email' => 'adela.suci@smkn71.sch.id', 'instansi' => 'SMKN 71 Jakarta', 'major' => 'rpl', 'start' => '2025-09-01', 'end' => '2025-12-31'],
            ['name' => 'Indah Callista Excella', 'username' => 'indahc', 'email' => 'indah.callista@smkn71.sch.id', 'instansi' => 'SMKN 71 Jakarta', 'major' => 'rpl', 'start' => '2025-09-01', 'end' => '2025-12-31'],
            ['name' => 'Rennard Adityatama', 'username' => 'rennarda', 'email' => 'rennard.aditya@smkn71.sch.id', 'instansi' => 'SMKN 71 Jakarta', 'major' => 'rpl', 'start' => '2025-09-01', 'end' => '2025-12-31'],
            ['name' => 'Reyhan Saputra', 'username' => 'reyhans', 'email' => 'reyhan.saputra@smkn71.sch.id', 'instansi' => 'SMKN 71 Jakarta', 'major' => 'rpl', 'start' => '2025-09-01', 'end' => '2025-12-31'],
            
            // UPN Veteran Jakarta (SI) - 2025-09-01 to 2025-12-31
            ['name' => 'Isfiani Inayah', 'username' => 'isfianii', 'email' => 'isfiani.inayah@student.upnvj.ac.id', 'instansi' => 'Universitas Pembangunan Nasional "Veteran" Jakarta', 'major' => 'rpl', 'start' => '2025-09-01', 'end' => '2025-12-31'],
            ['name' => 'Zanneti Dwi Darmawan', 'username' => 'zannetid', 'email' => 'zanneti.dwi@student.upnvj.ac.id', 'instansi' => 'Universitas Pembangunan Nasional "Veteran" Jakarta', 'major' => 'rpl', 'start' => '2025-09-01', 'end' => '2025-12-31'],
            ['name' => 'Abdul Rauf Ghufron', 'username' => 'abdulraufg', 'email' => 'abdul.rauf@student.upnvj.ac.id', 'instansi' => 'Universitas Pembangunan Nasional "Veteran" Jakarta', 'major' => 'rpl', 'start' => '2025-09-01', 'end' => '2025-12-31'],
            ['name' => 'Detarafa Putri Anindya', 'username' => 'detarafap', 'email' => 'detarafa.putri@student.upnvj.ac.id', 'instansi' => 'Universitas Pembangunan Nasional "Veteran" Jakarta', 'major' => 'rpl', 'start' => '2025-09-01', 'end' => '2025-12-31'],
            ['name' => 'Muhammad Radiek Fidiyanto', 'username' => 'radiekf', 'email' => 'radiek.f@student.upnvj.ac.id', 'instansi' => 'Universitas Pembangunan Nasional "Veteran" Jakarta', 'major' => 'rpl', 'start' => '2025-09-01', 'end' => '2025-12-31'],
            
            // UNJ FIS (Humas) - 2026-01-12 to 2026-05-23
            ['name' => 'Marcella Zeilanti Ramadhan', 'username' => 'marcellaz', 'email' => 'marcella.zeilanti@student.unj.ac.id', 'instansi' => 'Universitas Negeri Jakarta FIS', 'major' => 'humas', 'start' => '2026-01-12', 'end' => '2026-05-23'],
            ['name' => 'Mela Agustia', 'username' => 'melaa', 'email' => 'mela.agustia@student.unj.ac.id', 'instansi' => 'Universitas Negeri Jakarta FIS', 'major' => 'humas', 'start' => '2026-01-12', 'end' => '2026-05-23'],
            ['name' => 'Mohammad Arsha Syahdan', 'username' => 'arshas', 'email' => 'arsha.syahdan@student.unj.ac.id', 'instansi' => 'Universitas Negeri Jakarta FIS', 'major' => 'humas', 'start' => '2026-01-12', 'end' => '2026-05-23'],
            ['name' => 'Jihan Salzabilla Winata', 'username' => 'jihans', 'email' => 'jihan.salzabilla@student.unj.ac.id', 'instansi' => 'Universitas Negeri Jakarta FIS', 'major' => 'humas', 'start' => '2026-01-12', 'end' => '2026-05-23'],
            
            // SMKN 51 Jakarta (Produksi TV) - 2025-12-01 to 2026-05-29
            ['name' => 'Asyifa Indah Lestari', 'username' => 'asyifai', 'email' => 'asyifa.indah@smkn51.sch.id', 'instansi' => 'SMK Negeri 51 Jakarta', 'major' => 'tv', 'start' => '2025-12-01', 'end' => '2026-05-29'],
            ['name' => 'Athaar Ismu Adji', 'username' => 'athaari', 'email' => 'athaar.ismu@smkn51.sch.id', 'instansi' => 'SMK Negeri 51 Jakarta', 'major' => 'tv', 'start' => '2025-12-01', 'end' => '2026-05-29'],
            ['name' => 'Gidheon Adriano Sinulingga', 'username' => 'gidheons', 'email' => 'gidheon.adriano@smkn51.sch.id', 'instansi' => 'SMK Negeri 51 Jakarta', 'major' => 'tv', 'start' => '2025-12-01', 'end' => '2026-05-29'],
            ['name' => 'Mahdiyah Maulida Hayoto', 'username' => 'mahdiyahm', 'email' => 'mahdiyah.maulida@smkn51.sch.id', 'instansi' => 'SMK Negeri 51 Jakarta', 'major' => 'tv', 'start' => '2025-12-01', 'end' => '2026-05-29'],
            ['name' => 'Zahwa Alya Nur Septian', 'username' => 'zahwaa', 'email' => 'zahwa.alya@smkn51.sch.id', 'instansi' => 'SMK Negeri 51 Jakarta', 'major' => 'tv', 'start' => '2025-12-01', 'end' => '2026-05-29'],
            ['name' => 'Singga Septianti', 'username' => 'singgas', 'email' => 'singga.septianti@smkn51.sch.id', 'instansi' => 'SMK Negeri 51 Jakarta', 'major' => 'tv', 'start' => '2025-12-01', 'end' => '2026-05-29'],
            
            // SMKN 17 Jakarta (RPL) - 2026-01-01 to 2026-05-31
            ['name' => 'Nailah Salsabila Wityanto', 'username' => 'nailahs', 'email' => 'nailah.salsabila@smkn17.sch.id', 'instansi' => 'SMK Negeri 17 Jakarta', 'major' => 'rpl', 'start' => '2026-01-01', 'end' => '2026-05-31'],
            ['name' => 'Eka Catur Putrianur', 'username' => 'ekac', 'email' => 'eka.catur@smkn17.sch.id', 'instansi' => 'SMK Negeri 17 Jakarta', 'major' => 'rpl', 'start' => '2026-01-01', 'end' => '2026-05-31'],
            ['name' => 'Alya Dwi Andini', 'username' => 'alyad', 'email' => 'alya.dwi@smkn17.sch.id', 'instansi' => 'SMK Negeri 17 Jakarta', 'major' => 'rpl', 'start' => '2026-01-01', 'end' => '2026-05-31'],
            ['name' => 'Reva Rahmania', 'username' => 'revar', 'email' => 'reva.rahmania@smkn17.sch.id', 'instansi' => 'SMK Negeri 17 Jakarta', 'major' => 'rpl', 'start' => '2026-01-01', 'end' => '2026-05-31'],
            ['name' => 'Nafirah Fairuza Syarahil', 'username' => 'nafirahf', 'email' => 'nafirah.fairuza@smkn17.sch.id', 'instansi' => 'SMK Negeri 17 Jakarta', 'major' => 'rpl', 'start' => '2026-01-01', 'end' => '2026-05-31'],
            
            // SMKN 48 Jakarta (DKV) - 2026-01-05 to 2026-05-29
            ['name' => 'Muhammad Rayhan', 'username' => 'rayhanm', 'email' => 'muhammad.rayhan@smkn48.sch.id', 'instansi' => 'SMK Negeri 48 Jakarta', 'major' => 'dkv', 'start' => '2026-01-05', 'end' => '2026-05-29'],
            ['name' => 'Muhammad Wildan Djamaluddin', 'username' => 'wildand', 'email' => 'muhammad.wildan@smkn48.sch.id', 'instansi' => 'SMK Negeri 48 Jakarta', 'major' => 'dkv', 'start' => '2026-01-05', 'end' => '2026-05-29'],
            ['name' => 'Nabila Mardian', 'username' => 'nabilam', 'email' => 'nabila.mardian@smkn48.sch.id', 'instansi' => 'SMK Negeri 48 Jakarta', 'major' => 'dkv', 'start' => '2026-01-05', 'end' => '2026-05-29'],
            ['name' => 'Williem Cerolus', 'username' => 'willieme', 'email' => 'williem.cerolus@smkn48.sch.id', 'instansi' => 'SMK Negeri 48 Jakarta', 'major' => 'dkv', 'start' => '2026-01-05', 'end' => '2026-05-29'],
            ['name' => 'Chintya Putri Nur Hidayat', 'username' => 'chintyap', 'email' => 'chintya.putri@smkn48.sch.id', 'instansi' => 'SMK Negeri 48 Jakarta', 'major' => 'dkv', 'start' => '2026-01-05', 'end' => '2026-05-29'],
            ['name' => 'Octa Liga Herliana', 'username' => 'octal', 'email' => 'octa.liga@smkn48.sch.id', 'instansi' => 'SMK Negeri 48 Jakarta', 'major' => 'dkv', 'start' => '2026-01-05', 'end' => '2026-05-29'],
        ];

        $hashedPassword = Hash::make('magang123');
        $createdUsers = [];

        foreach ($interns as $intern) {
            $createdUsers[] = [
                'user' => User::create([
                    'username' => $intern['username'],
                    'name' => $intern['name'],
                    'email' => $intern['email'],
                    'no_telp' => '08' . rand(111111111, 999999999),
                    'asal_instansi' => $intern['instansi'],
                    'role' => 'user',
                    'face_data' => 'dummy_face_data',
                    'password' => $hashedPassword,
                ]),
                'major' => $intern['major'],
                'start' => Carbon::parse($intern['start'])->startOfDay(),
                'end' => Carbon::parse($intern['end'])->endOfDay(),
            ];
        }

        // Call HolidaySeeder to populate holidays table if empty
        $this->call(HolidaySeeder::class);

        // Major-specific logbooks (> 100 characters)
        $tasksData = [
            'dkv' => [
                "Desain poster promosi untuk event internal perusahaan menggunakan palet warna monokrom agar terkesan modern, elegan, dan profesional.",
                "Melakukan editing konten video pendek untuk dipublikasikan di media sosial Instagram dan TikTok dengan menambahkan teks transkrip lengkap.",
                "Membuat panduan identitas visual (branding guidelines) untuk logo sistem absensi baru agar konsisten di semua media cetak maupun digital.",
                "Merancang antarmuka UI/UX untuk dashboard absensi versi mobile, berfokus pada navigasi jempol yang ramah pengguna serta loading cepat.",
                "Membuat ilustrasi vektor orisinal untuk melengkapi kebutuhan aset visual halaman login aplikasi agar terlihat interaktif dan futuristik.",
                "Melakukan penyesuaian tata letak grid dan pilihan tipografi pada poster promosi agar informasi utama dapat dibaca lebih jelas dan terarah."
            ],
            'rpl' => [
                "Mengembangkan logika backend API untuk integrasi modul absensi GPS, memastikan data koordinat diproses secara tepat dan aman.",
                "Menulis skrip automated unit testing untuk memverifikasi alur registrasi user baru dengan berbagai variasi input data dan role.",
                "Menganalisis log error pada server dan melakukan debugging masalah bottleneck query saat load data dashboard admin secara berkala.",
                "Merancang arsitektur database relasional baru dan menambahkan relasi index pada tabel absensi agar proses select data jauh lebih cepat.",
                "Membuat visualisasi grafik statistik persentase kehadiran bulanan di dashboard menggunakan library Chart JS yang responsif.",
                "Mengimplementasikan middleware autentikasi role user untuk membatasi akses halaman absensi dan logbook khusus demi keamanan data."
            ],
            'humas' => [
                "Menyusun draf konten publikasi media sosial mingguan untuk mengumumkan pencapaian kinerja magang serta jadwal rapat koordinasi penting.",
                "Mengambil dokumentasi foto dan video berkualitas tinggi selama kegiatan kunjungan industri serta merapikan arsip visual humas.",
                "Menulis naskah press release resmi mengenai peluncuran platform absensi internal baru untuk dikirimkan ke portal berita lokal terpercaya.",
                "Menghubungi perwakilan universitas mitra magang untuk melakukan konfirmasi jadwal kunjungan monitoring serta evaluasi bulanan rutin.",
                "Menyusun laporan sentimen publik terkait rilis fitur baru di platform dari komentar-komentar pengguna di media sosial perusahaan.",
                "Mempersiapkan materi presentasi humas yang akan dibawakan dalam rapat evaluasi bersama mentor dan dewan pembimbing magang."
            ],
            'tv' => [
                "Melakukan proses editing video profil perusahaan dengan menambahkan efek transisi halus serta sinkronisasi musik latar yang menarik.",
                "Membantu proses shooting video tutorial penggunaan aplikasi absensi, bertindak sebagai pengatur tata cahaya dan penata kamera di studio.",
                "Memproduksi konten video pendek edukasi seputar tips kedisiplinan kerja untuk diunggah ke platform berbagi video internal.",
                "Melakukan dubbing suara (voice over) serta sinkronisasi audio untuk video dokumenter akhir tahun tim magang secara profesional.",
                "Menyusun storyboard detail untuk video promosi fitur baru sistem absensi agar memudahkan proses pengambilan gambar lapangan.",
                "Melakukan proses rendering video dengan format yang optimal agar ukuran file tidak terlalu besar saat diunggah ke media sosial."
            ]
        ];

        // Supervisor comments
        $approvedComments = [
            "Kerja sudah baik, pertahankan.",
            "Sudah sesuai instruksi.",
            "Hasil pekerjaan sangat baik.",
            "Sangat bagus, pengerjaan cepat dan tepat waktu.",
            "Laporan harian sangat detail dan progresnya terlihat jelas.",
            "Pekerjaan diselesaikan dengan baik dan rapi."
        ];

        // Base64 transparent signatures
        $adminSignature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAAAyCAYAAACqKw8UAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH5gYMDQcKCBgVDAAAAB1pVFh0Q29tbWVudAAAAAAAQ3JlYXRlZCB3aXRoIEdJTVBkLmUHAAADSUlEQVR42u2aXUiTYRjHf+/77reZ27RpaWmhUUGFFhFCF3XRXSiGXaVdRFd1E3SRXUQQXnQTRFeFdRGFhVBhF0GEWUBURCGYmS/N2ea+77tOC0U/Nqe1NWev+d944Pnhgff9v+d9n+e8z3nOU1BQUFBQUFBQUFD+D0g05gJomqYoikJKiWmaKKX291sU7bVomqYoikJKiWmaKKX291sU7bVomqYoikJKWbT3bE/T1B91Wdb9nFPD97M9dK89GnMBNI2UkpQSXdfD3d93NMb4f/v7vV3N/+2P/+/4w/8Q/2P8gf8x/sD/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8j/EH/sf4A/9j/IH/Mf7A/1ixv7Ue/P0s4H+KxljR3n7Of5t/GX8A42mEAAAAAElFTkSuQmCC';
        $pembimbingSignature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAAAyCAYAAACqKw8UAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH5gYMDQcKCBgVDAAAAB1pVFh0Q29tbWVudAAAAAAAQ3JlYXRlZCB3aXRoIEdJTVBkLmUHAAADSUlEQVR42u3az0tUURgH8O+578yMZv7IaEqLqChwEUTYRlCif0GLVq1atWrVIrRoEUTQopUQtGgVREQhWkiFIIoiM6fJ+eG8e02LqPkwp7U15+7v5oHnhwfe7z3n3HPuuY8IIYQQQgghhBBCCH8HUp/bAaVURCnFKaU4pRRVVeW7rV5rD5VSUUr5hFKKU4pTSlFVVb7b6rX2UClVEaWUp5TiKaWYqqr8f9eT/V0l32vzevtP3Uv32n9j24HaRkpJSklKSerq6vJ6X5Ikyf733f3+v/2t/+2v7/92j9/9jfHfxx/4H+MP/I/xB/7H+AP/Y/yB/zH+wP8Yf+B/jD/wP9rf+N9aDv5sF/D2NsXy9vc3z71vvGf8BmqiHjP+AzVRD/gAAAABJRU5ErkJggg==';

        foreach ($createdUsers as $data) {
            $user = $data['user'];
            $major = $data['major'];
            $currentDate = $data['start']->copy();
            $endDate = $data['end'];

            while ($currentDate->lte($endDate)) {
                $dateStr = $currentDate->toDateString();

                // Skip weekends
                if (\App\Helpers\HolidayHelper::isWeekend($currentDate)) {
                    $currentDate->addDay();
                    continue;
                }

                // Check holiday
                $holidayName = \App\Helpers\HolidayHelper::getHolidayName($currentDate);

                if ($holidayName !== null) {
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
                    $currentDate->addDay();
                    continue;
                }

                // Roll status: 85% tepat_waktu, 10% terlambat, 5% absent
                $roll = rand(1, 100);

                if ($roll <= 85) {
                    $status = 'tepat_waktu';
                } elseif ($roll <= 95) {
                    $status = 'terlambat';
                } else {
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
                        'izin' => ['Izin Kampus - Seminar Tugas Akhir', 'Izin Kampus - Sidang PKL', 'Izin Keperluan Keluarga', 'Izin Wawancara Akademik']
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
                    // Approved by both pembimbing & admin, with both signatures and comments
                    Logbook::create([
                        'user_id' => $user->id,
                        'tanggal' => $dateStr,
                        'deskripsi' => $tasksData[$major][array_rand($tasksData[$major])],
                        'foto_kegiatan' => 'logbooks/dummy_kegiatan.jpg',
                        'status' => 'approved',
                        'komentar_pembimbing' => $approvedComments[array_rand($approvedComments)],
                        'tanda_tangan_admin' => $adminSignature,
                        'tanda_tangan_pembimbing' => $pembimbingSignature,
                        'admin_id' => $admin->id,
                    ]);
                }

                $currentDate->addDay();
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
