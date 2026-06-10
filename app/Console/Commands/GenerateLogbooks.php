<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Logbook;
use Exception;

class GenerateLogbooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logbook:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate logbook entries based on existing attendance logs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting logbook generation...');

        // Base64 transparent signatures
        $adminSignature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQMDQcKCBgVDAAAAB1pVFh0Q29tbWVudAAAAAAAQ3JlYXRlZCB3aXRoIEdJTVBkLmUHAAADSUlEQVR42u2aXUiTYRjHf+/77reZ27RpaWmhUUGFFhFCF3XRXSiGXaVdRFd1E3SRXUQQXnQTRFeFdRGFhVBhF0GEWUBURCGYmS/N2ea+77tOC0U/Nqe1NWev+d944Pnhgff9v+d9n+e8z3nOU1BQUFBQUFBQUFD+D0g05gJomqYoikJKiWmaKKX291sU7bVomqYoikJKiWmaKKX291sU7bVomqYoikJKWbT3bE/T1B91Wdb9nFPD97M9dK89GnMBNI2UkpQSXdfD3d93NMb4f/v7vV3N/+2P/+/4w/8Q/2P8gf8x/sD/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8D/GH/gf4w/8j/EH/sf4A/9j/IH/Mf7A/1ixv7Ue/P0s4H+KxljR3n7Of5t/GX8A42mEAAAAAElFTkSuQmCC';
        $pembimbingSignature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQyCAYAAACqKw8UAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH5gYMDQcKCBgVDAAAAB1pVFh0Q29tbWVudAAAAAAAQ3JlYXRlZCB3aXRoIEdJTVBkLmUHAAADSUlEQVR42u3az0tUURgH8O+578yMZv7IaEqLqChwEUTYRlCif0GLVq1atWrVIrRoEUTQopUQtGgVREQhWkiFIIoiM6fJ+eG8e02LqPkwp7U15+7v5oHnhwfe7z3n3HPuuY8IIYQQQgghhBBCCH8HUp/bAaVURCnFKaU4pRRVVeW7rV5rD5VSUUr5hFKKU4pTSlFVVb7b6rX2UClVEaWUp5TiKaWYqqr8f9eT/V0l32vzevtP3Uv32n9j24HaRkpJSklKSerq6vJ6X5Ikyf733f3+v/2t/+2v7/92j9/9jfHfxx/4H+MP/I/xB/7H+AP/Y/yB/zH+wP8Yf+B/jD/wP9rf+N9aDv5sF/D2NsXy9vc3z71vvGf8BmqiHjP+AzVRD/gAAAABJRU5ErkJggg==';

        // Find Admin
        $adminUser = User::where('role', 'admin')->first();
        if (!$adminUser) {
            $this->error('Admin user not found. Please seed users first.');
            return 1;
        }

        // Prepare placeholder image in storage
        $placeholderPath = 'logbooks/placeholder.jpg';
        $fullPath = storage_path('app/public/' . $placeholderPath);
        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        if (!file_exists($fullPath)) {
            $this->line('Downloading placeholder image from Picsum...');
            try {
                $context = stream_context_create([
                    'http' => ['timeout' => 5]
                ]);
                $imgData = @file_get_contents('https://picsum.photos/800/600', false, $context);
                if ($imgData) {
                    file_put_contents($fullPath, $imgData);
                    $this->info('Placeholder image saved to storage.');
                } else {
                    throw new Exception("Picsum unavailable");
                }
            } catch (Exception $e) {
                $this->warn('Picsum download failed, generating local placeholder image...');
                if (function_exists('imagecreatetruecolor')) {
                    $im = imagecreatetruecolor(800, 600);
                    $bg = imagecolorallocate($im, 30, 30, 30);
                    imagefill($im, 0, 0, $bg);
                    $textColor = imagecolorallocate($im, 200, 200, 200);
                    imagestring($im, 5, 300, 280, 'Foto Kegiatan Placeholder', $textColor);
                    imagejpeg($im, $fullPath);
                    imagedestroy($im);
                    $this->info('Generated GD placeholder image.');
                } else {
                    file_put_contents($fullPath, base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA='));
                    $this->info('Generated tiny 1x1 fallback image.');
                }
            }
        }

        // Tasks data mapping by major
        $tasks = [
            'D3 Sistem Informasi' => [
                [
                    'judul' => 'Pengembangan fitur dashboard',
                    'deskripsi' => 'Merancang dan mengimplementasikan halaman visualisasi data statistik kehadiran magang pada dashboard admin agar memudahkan pemantauan harian.'
                ],
                [
                    'judul' => 'Perbaikan bug login',
                    'deskripsi' => 'Menganalisis kegagalan autentikasi pengguna pada form login, memperbaiki validasi token CSRF, serta memastikan session tersimpan dengan aman.'
                ],
                [
                    'judul' => 'Pengujian sistem',
                    'deskripsi' => 'Melakukan pengujian fungsionalitas (blackbox testing) pada seluruh fitur absensi GPS dan pengajuan cuti untuk memastikan sistem bebas error sebelum rilis.'
                ],
                [
                    'judul' => 'Dokumentasi database',
                    'deskripsi' => 'Menyusun skema relasi tabel database (ERD) serta menulis kamus data lengkap untuk tabel users, attendances, dan logbooks.'
                ],
                [
                    'judul' => 'Pembuatan laporan',
                    'deskripsi' => 'Menyusun laporan bulanan aktivitas magang mahasiswa, mengompilasi statistik kehadiran, serta menyerahkannya kepada koordinator.'
                ],
            ],
            'RPL' => [
                [
                    'judul' => 'Coding modul absensi',
                    'deskripsi' => 'Menulis baris kode backend untuk memproses data clock-in dan clock-out, serta mengintegrasikan perhitungan jarak koordinat GPS pengguna.'
                ],
                [
                    'judul' => 'API testing',
                    'deskripsi' => 'Menguji endpoint API absensi menggunakan Postman untuk memvalidasi respon status code, format JSON, serta penanganan error input.'
                ],
                [
                    'judul' => 'Database optimization',
                    'deskripsi' => 'Menambahkan indeks pada kolom foreign key tabel logbooks dan attendances untuk mengoptimalkan kecepatan query load data.'
                ],
                [
                    'judul' => 'UI improvement',
                    'deskripsi' => 'Memperbaiki desain tata letak antarmuka halaman absensi agar lebih responsif di perangkat mobile dan menambahkan efek animasi transisi halus.'
                ],
            ],
            'DKV' => [
                [
                    'judul' => 'Desain poster',
                    'deskripsi' => 'Membuat konsep dan merancang desain poster digital promosi program magang industri menggunakan perangkat lunak Adobe Illustrator.'
                ],
                [
                    'judul' => 'Desain banner',
                    'deskripsi' => 'Mendesain banner publikasi event webinar internal perusahaan dengan menyesuaikan palet warna korporat agar terlihat profesional.'
                ],
                [
                    'judul' => 'Editing konten media sosial',
                    'deskripsi' => 'Melakukan penyuntingan visual gambar feed Instagram untuk mengumumkan hari libur nasional menggunakan layout grid modern.'
                ],
                [
                    'judul' => 'UI mockup',
                    'deskripsi' => 'Merancang desain mockup antarmuka pengguna (UI) aplikasi absensi versi mobile di Figma dengan fokus pada kenyamanan navigasi.'
                ],
            ],
            'Humas' => [
                [
                    'judul' => 'Pengelolaan Instagram',
                    'deskripsi' => 'Menyusun kalender konten Instagram, merapikan deskripsi takarir (caption), serta membalas pesan masuk dari calon peserta magang.'
                ],
                [
                    'judul' => 'Dokumentasi kegiatan',
                    'deskripsi' => 'Mengambil dokumentasi foto berkualitas tinggi selama kunjungan monitoring pembimbing kampus ke kantor perusahaan.'
                ],
                [
                    'judul' => 'Press release',
                    'deskripsi' => 'Menulis draf siaran pers resmi mengenai peluncuran aplikasi absensi Attendora untuk disebarkan ke media partner lokal.'
                ],
                [
                    'judul' => 'Konten publikasi',
                    'deskripsi' => 'Menyiapkan materi teks publikasi untuk pengumuman tata tertib kehadiran magang di saluran komunikasi internal.'
                ],
            ],
            'Produksi TV' => [
                [
                    'judul' => 'Shooting',
                    'deskripsi' => 'Membantu pengambilan gambar video tutorial tata cara absen wajah menggunakan kamera mirrorless di studio produksi.'
                ],
                [
                    'judul' => 'Editing video',
                    'deskripsi' => 'Melakukan proses editing video profil kegiatan magang mingguan, memotong klip, serta menyelaraskan dengan musik latar.'
                ],
                [
                    'judul' => 'Pengolahan audio',
                    'deskripsi' => 'Melakukan penyaringan derau (noise reduction) pada rekaman suara narator video panduan penggunaan aplikasi absensi.'
                ],
                [
                    'judul' => 'Produksi konten',
                    'deskripsi' => 'Menyusun konsep storyboard dan mengarahkan alur cerita pembuatan konten video pendek kreatif seputar kedisiplinan kerja.'
                ],
            ],
        ];

        // Majors list
        $majors = ['Humas', 'D3 Sistem Informasi', 'RPL', 'DKV', 'Produksi TV'];

        // Get present attendances
        $attendances = Attendance::whereIn('status', ['tepat_waktu', 'terlambat', 'hadir'])->get();
        $this->info("Found {$attendances->count()} present attendance records.");

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($attendances as $att) {
            $user = $att->user;
            if (!$user) {
                continue;
            }

            // Consistent major based on user ID
            $major = $majors[$user->id % 5];
            $majorTasks = $tasks[$major];
            $selectedTask = $majorTasks[array_rand($majorTasks)];

            // Format deskripsi to contain both judul_kegiatan and deskripsi_kegiatan
            $formattedDeskripsi = "Judul: " . $selectedTask['judul'] . "\nDeskripsi: " . $selectedTask['deskripsi'];

            // Status distribution: 80% Approved, 15% Pending, 5% Rejected
            $roll = rand(1, 100);
            if ($roll <= 80) {
                $status = 'approved';
                $komentar = 'Kerja sangat baik dan sesuai target.';
                $ttdAdmin = $adminSignature;
                $ttdPembimbing = $pembimbingSignature;
                $adminId = $adminUser->id;
            } elseif ($roll <= 95) {
                $status = 'pending';
                $komentar = 'Perlu melengkapi dokumentasi kegiatan.';
                $ttdAdmin = null;
                $ttdPembimbing = null;
                $adminId = null;
            } else {
                $status = 'rejected';
                $komentar = 'Kegiatan belum sesuai dengan target harian.';
                $ttdAdmin = null;
                $ttdPembimbing = null;
                $adminId = $adminUser->id;
            }

            // Use updateOrCreate to prevent duplicates while filling missing fields
            $logbook = Logbook::updateOrCreate(
                [
                    'user_id' => $att->user_id,
                    'tanggal' => $att->tanggal->toDateString()
                ],
                [
                    'deskripsi' => $formattedDeskripsi,
                    'foto_kegiatan' => $placeholderPath,
                    'status' => $status,
                    'komentar_pembimbing' => $komentar,
                    'tanda_tangan_admin' => $ttdAdmin,
                    'tanda_tangan_pembimbing' => $ttdPembimbing,
                    'admin_id' => $adminId,
                ]
            );

            if ($logbook->wasRecentlyCreated) {
                $createdCount++;
            } else {
                $updatedCount++;
            }
        }

        // Stats summary
        $totalLogbooks = Logbook::count();
        $totalApproved = Logbook::where('status', 'approved')->count();
        $totalPending = Logbook::where('status', 'pending')->count();
        $totalRejected = Logbook::where('status', 'rejected')->count();
        
        $this->info("Logbook generation completed!");
        $this->line("====================================");
        $this->line("Total Logbook Baru Dibuat: " . $createdCount);
        $this->line("Total Logbook Sudah Ada (Dilewati): " . $updatedCount);
        $this->line("Total Logbook di Database: " . $totalLogbooks);
        $this->line("Total Approved: " . $totalApproved);
        $this->line("Total Pending: " . $totalPending);
        $this->line("Total Rejected: " . $totalRejected);
        $this->line("====================================");

        $this->info("10 Logbook Terbaru:");
        $latest = Logbook::with('user')->orderBy('tanggal', 'desc')->take(10)->get();
        foreach ($latest as $idx => $lb) {
            $name = $lb->user ? $lb->user->name : 'Unknown';
            $this->line(($idx + 1) . ". [{$lb->tanggal->format('Y-m-d')}] User: {$name} | Status: {$lb->status} | {$lb->deskripsi}");
        }

        return Command::SUCCESS;
    }
}
