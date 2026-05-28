<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Logbook;
use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class LogbookExport implements WithEvents, WithTitle, WithDrawings
{
    protected int $userId;
    protected int $startMonth;
    protected int $startYear;
    protected int $endMonth;
    protected int $endYear;
    protected array $tempFiles = [];
    protected ?array $reportData = null;

    public function __construct(int $userId, int $startMonth, int $startYear, int $endMonth, int $endYear)
    {
        $this->userId = $userId;
        $this->startMonth = $startMonth;
        $this->startYear = $startYear;
        $this->endMonth = $endMonth;
        $this->endYear = $endYear;
    }

    /**
     * Clean up temporary files on destruction.
     */
    public function __destruct()
    {
        foreach ($this->tempFiles as $tempFile) {
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }

    public function title(): string
    {
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        
        $startName = $monthNames[$this->startMonth] ?? $this->startMonth;
        $endName = $monthNames[$this->endMonth] ?? $this->endMonth;
        
        if ($this->startYear === $this->endYear) {
            return $startName . '-' . $endName . ' ' . $this->startYear;
        } else {
            return $startName . ' ' . $this->startYear . '-' . $endName . ' ' . $this->endYear;
        }
    }

    /**
     * Generate logo and save to a temporary file.
     */
    protected function generateLogo(): ?string
    {
        if (!function_exists('imagecreatetruecolor')) {
            return null;
        }

        try {
            $logoPath = tempnam(sys_get_temp_dir(), 'logo_') . '.png';
            $im = imagecreatetruecolor(60, 60);
            
            $bg = imagecolorallocate($im, 26, 26, 26); // Dark theme color #1a1a1a
            imagefill($im, 0, 0, $bg);
            
            $white = imagecolorallocate($im, 255, 255, 255);
            $gray = imagecolorallocate($im, 100, 100, 100);
            
            imagerectangle($im, 5, 5, 54, 54, $gray);
            
            for ($o = 0; $o <= 1; $o++) {
                imageline($im, 18, 30 + $o, 26, 40 + $o, $white);
                imageline($im, 26, 40 + $o, 42, 18 + $o, $white);
            }
            
            imagepng($im, $logoPath);
            imagedestroy($im);
            
            $this->tempFiles[] = $logoPath;
            return $logoPath;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Process base64 signature: convert white strokes to solid black, preserve alpha/transparency.
     */
    protected function processSignature(string $base64Data): ?string
    {
        try {
            $parts = explode(',', $base64Data, 2);
            $imgData = base64_decode($parts[1] ?? '');
            if (!$imgData) {
                return null;
            }

            $tempPath = tempnam(sys_get_temp_dir(), 'ttd_') . '.png';

            if (!function_exists('imagecreatefromstring')) {
                file_put_contents($tempPath, $imgData);
                $this->tempFiles[] = $tempPath;
                return $tempPath;
            }

            $src = @imagecreatefromstring($imgData);
            if (!$src) {
                file_put_contents($tempPath, $imgData);
                $this->tempFiles[] = $tempPath;
                return $tempPath;
            }

            $width = imagesx($src);
            $height = imagesy($src);

            $dst = imagecreatetruecolor($width, $height);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);

            $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefill($dst, 0, 0, $transparent);

            for ($x = 0; $x < $width; $x++) {
                for ($y = 0; $y < $height; $y++) {
                    $colorIdx = imagecolorat($src, $x, $y);
                    $colors = imagecolorsforindex($src, $colorIdx);
                    $alpha = $colors['alpha'];

                    if ($alpha < 120) {
                        $black = imagecolorallocatealpha($dst, 0, 0, 0, $alpha);
                        imagesetpixel($dst, $x, $y, $black);
                    }
                }
            }

            imagepng($dst, $tempPath);
            imagedestroy($src);
            imagedestroy($dst);

            $this->tempFiles[] = $tempPath;
            return $tempPath;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Calculate dimensions to fit inside a bounding box (contain mode).
     */
    protected function calculateFitDimensions(string $imagePath, int $maxWidth, int $maxHeight): array
    {
        if (!file_exists($imagePath) || !is_file($imagePath)) {
            return ['width' => $maxWidth, 'height' => $maxHeight];
        }
        
        $size = @getimagesize($imagePath);
        if (!$size) {
            return ['width' => $maxWidth, 'height' => $maxHeight];
        }
        
        $origWidth = $size[0];
        $origHeight = $size[1];
        
        if ($origWidth <= 0 || $origHeight <= 0) {
            return ['width' => $maxWidth, 'height' => $maxHeight];
        }

        $ratio = $origWidth / $origHeight;
        
        $width = $maxWidth;
        $height = intval($maxWidth / $ratio);
        
        if ($height > $maxHeight) {
            $height = $maxHeight;
            $width = intval($maxHeight * $ratio);
        }
        
        return ['width' => $width, 'height' => $height];
    }

    /**
     * Create a centered Drawing object inside a cell.
     */
    protected function createCenteredDrawing(
        string $imagePath,
        string $coordinate,
        int $maxWidth,
        int $maxHeight,
        int $colWidthPx,
        int $rowHeightPx,
        string $name,
        string $description
    ): ?Drawing {
        if (!file_exists($imagePath) || !is_file($imagePath)) {
            return null;
        }

        $dims = $this->calculateFitDimensions($imagePath, $maxWidth, $maxHeight);

        $drawing = new Drawing();
        $drawing->setName($name);
        $drawing->setDescription($description);
        $drawing->setPath($imagePath);
        $drawing->setWidth($dims['width']);
        $drawing->setHeight($dims['height']);
        $drawing->setCoordinates($coordinate);

        // Center horizontally
        $offsetX = max(0, intval(($colWidthPx - $dims['width']) / 2));
        $drawing->setOffsetX($offsetX);

        // Center vertically
        $offsetY = max(0, intval(($rowHeightPx - $dims['height']) / 2));
        $drawing->setOffsetY($offsetY);

        return $drawing;
    }

    /**
     * Get or build report data combining logbooks and attendances.
     */
    protected function getReportData(): array
    {
        if ($this->reportData !== null) {
            return $this->reportData;
        }

        $startDate = Carbon::create($this->startYear, $this->startMonth, 1)->startOfMonth();
        $endDate = Carbon::create($this->endYear, $this->endMonth, 1)->endOfMonth();

        // Fetch logbooks
        $logbooks = Logbook::where('user_id', $this->userId)
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(fn($item) => $item->tanggal->format('Y-m-d'));

        // Fetch attendances
        $attendances = Attendance::where('user_id', $this->userId)
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(fn($item) => $item->tanggal->format('Y-m-d'));

        $report = [];
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $dateStr = $current->toDateString();
            $lb = $logbooks->get($dateStr);
            $att = $attendances->get($dateStr);
            
            $report[] = [
                'date' => $current->copy(),
                'logbook' => $lb,
                'attendance' => $att,
            ];
            $current->addDay();
        }

        $this->reportData = $report;
        return $report;
    }

    /**
     * Generate drawings (logo, photos and signatures) for the Excel sheet.
     */
    public function drawings(): array
    {
        $drawings = [];
        
        // 1. Generate and add App Logo at A2
        $logoPath = $this->generateLogo();
        if ($logoPath && file_exists($logoPath)) {
            try {
                $logoDrawing = new Drawing();
                $logoDrawing->setName('Logo');
                $logoDrawing->setPath($logoPath);
                $logoDrawing->setHeight(36);
                $logoDrawing->setCoordinates('A2');
                $logoDrawing->setOffsetX(15);
                $logoDrawing->setOffsetY(5);
                $drawings[] = $logoDrawing;
            } catch (\Exception $e) {
                // Ignore logo draw failure
            }
        }

        $reportData = $this->getReportData();

        if (empty($reportData)) {
            return $drawings;
        }

        $headerRow = 7;
        $dataRow = $headerRow + 1;
        $drawingIndex = 1;

        // Constants for cell sizing in pixels
        $rowHeightPx = 113;    // 85 pt
        $fotoColWidthPx = 173; // Column width F (24 characters)
        $ttdColWidthPx = 131;  // Column width G (18 characters)

        foreach ($reportData as $row) {
            $lb = $row['logbook'];
            if ($lb) {
                // Foto Kegiatan (Column F): Max width 140px, Max height 90px
                if ($lb->foto_kegiatan) {
                    $fotoPath = storage_path('app/public/' . $lb->foto_kegiatan);
                    if (file_exists($fotoPath) && is_file($fotoPath)) {
                        $drawing = $this->createCenteredDrawing(
                            $fotoPath,
                            "F{$dataRow}",
                            140, // Max width
                            90,  // Max height
                            $fotoColWidthPx,
                            $rowHeightPx,
                            'Foto_' . $drawingIndex,
                            'Foto Kegiatan'
                        );
                        if ($drawing) {
                            $drawings[] = $drawing;
                        }
                    }
                }

                // Tanda Tangan (Column G): Max width 100px, Max height 50px
                if ($lb->tanda_tangan_admin && str_starts_with($lb->tanda_tangan_admin, 'data:image')) {
                    $tempPath = $this->processSignature($lb->tanda_tangan_admin);
                    if ($tempPath && file_exists($tempPath)) {
                        $drawing = $this->createCenteredDrawing(
                            $tempPath,
                            "G{$dataRow}",
                            100, // Max width
                            50,  // Max height
                            $ttdColWidthPx,
                            $rowHeightPx,
                            'TTD_' . $drawingIndex,
                            'Tanda Tangan'
                        );
                        if ($drawing) {
                            $drawings[] = $drawing;
                        }
                    }
                }
            }

            $drawingIndex++;
            $dataRow++;
        }

        return $drawings;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // ── Fetch user & data range ──
                $targetUser = User::findOrFail($this->userId);
                $reportData = $this->getReportData();

                // ── Top Margin ──
                $sheet->getRowDimension(1)->setRowHeight(10);

                // ── Title Area (Columns B to G next to the logo in A) ──
                $sheet->mergeCells('B2:G2');
                $sheet->setCellValue('B2', 'ATTENDORA — LAPORAN LOGBOOK & ABSENSI MAGANG');
                $sheet->getStyle('B2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 15, 'color' => ['rgb' => '111111'], 'name' => 'Arial'],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(25);

                // User details
                $sheet->mergeCells('B3:G3');
                $sheet->setCellValue('B3', 'Nama User: ' . $targetUser->name);
                $sheet->getStyle('B3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '222222'], 'name' => 'Arial'],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(18);

                $sheet->mergeCells('B4:G4');
                $sheet->setCellValue('B4', 'Asal Sekolah/Universitas: ' . $targetUser->asal_instansi);
                $sheet->getStyle('B4')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '222222'], 'name' => 'Arial'],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(4)->setRowHeight(18);

                // Period & export date
                $monthNames = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                ];
                $startMonthName = $monthNames[$this->startMonth] ?? $this->startMonth;
                $endMonthName = $monthNames[$this->endMonth] ?? $this->endMonth;
                
                if ($this->startYear === $this->endYear) {
                    $periodStr = $startMonthName . ' - ' . $endMonthName . ' ' . $this->startYear;
                } else {
                    $periodStr = $startMonthName . ' ' . $this->startYear . ' - ' . $endMonthName . ' ' . $this->endYear;
                }
                
                $periodText = 'Periode: ' . $periodStr . '  |  Tanggal Ekspor: ' . now()->translatedFormat('d F Y, H:i');
                $sheet->mergeCells('B5:G5');
                $sheet->setCellValue('B5', $periodText);
                $sheet->getStyle('B5')->applyFromArray([
                    'font' => ['size' => 9.5, 'color' => ['rgb' => '666666'], 'name' => 'Arial', 'italic' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(5)->setRowHeight(18);

                // Empty space row
                $sheet->getRowDimension(6)->setRowHeight(15);

                // ── Header Row (Row 7) ──
                $headers = [
                    'No',
                    'Tanggal',
                    'Jam Masuk',
                    'Jam Pulang',
                    'Deskripsi Kegiatan',
                    'Foto Kegiatan',
                    'Tanda Tangan Mentor/Admin'
                ];
                $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
                $headerRow = 7;

                foreach ($headers as $i => $header) {
                    $sheet->setCellValue($columns[$i] . $headerRow, $header);
                }

                // Header styling - Corporate Black and White style
                $sheet->getStyle("A{$headerRow}:G{$headerRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 10,
                        'color' => ['rgb' => 'FFFFFF'],
                        'name' => 'Arial'
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '000000']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '333333']
                        ],
                    ],
                ]);
                $sheet->getRowDimension($headerRow)->setRowHeight(32);

                // ── Data Rows ──
                $dataRow = $headerRow + 1;

                if (empty($reportData)) {
                    $lastRow = $dataRow;
                    $sheet->mergeCells("A{$lastRow}:G{$lastRow}");
                    $sheet->setCellValue("A{$lastRow}", 'Tidak ada data pada periode tersebut');
                    $sheet->getStyle("A{$lastRow}")->applyFromArray([
                        'font' => ['size' => 10.5, 'italic' => true, 'color' => ['rgb' => '777777'], 'name' => 'Arial'],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'DDDDDD']
                            ]
                        ]
                    ]);
                    $sheet->getRowDimension($lastRow)->setRowHeight(40);
                } else {
                    $index = 1;
                    foreach ($reportData as $row) {
                        $lb = $row['logbook'];
                        $att = $row['attendance'];
                        $date = $row['date'];

                        // Formatted values
                        $formattedDate = $date->translatedFormat('d M Y');
                        
                        $isWeekend = $date->isWeekend();
                        $holidayName = \App\Helpers\HolidayHelper::getHolidayName($date);
                        $isHoliday = $holidayName !== null;

                        if ($isHoliday) {
                            $jamMasuk = '-';
                            $jamPulang = '-';
                            $deskripsi = 'Libur Nasional'; // As requested by prompt: "Jika tanggal adalah hari libur: tampilkan 'Libur Nasional' pada kolom aktivitas/logbook"
                        } elseif ($isWeekend) {
                            $jamMasuk = '-';
                            $jamPulang = '-';
                            $deskripsi = 'Libur Akhir Pekan';
                        } else {
                            $jamMasuk = $att && $att->jam_masuk ? Carbon::parse($att->jam_masuk)->format('H:i') : '-';
                            $jamPulang = $att && $att->jam_pulang ? Carbon::parse($att->jam_pulang)->format('H:i') : '-';
                            
                            if ($lb) {
                                $deskripsi = $lb->deskripsi ?? '-';
                            } else {
                                if ($att) {
                                    if ($att->status === 'tidak_hadir') {
                                        $deskripsi = 'TIDAK HADIR / ALPA';
                                    } elseif ($att->status === 'izin') {
                                        $deskripsi = 'IZIN';
                                    } elseif ($att->status === 'sakit') {
                                        $deskripsi = 'SAKIT';
                                    } elseif ($att->status === 'libur_nasional') {
                                        $deskripsi = 'Libur Nasional';
                                    } else {
                                        $deskripsi = '-';
                                    }
                                } else {
                                    $deskripsi = '-';
                                }
                            }
                        }

                        // Write values to sheet
                        $sheet->setCellValue("A{$dataRow}", $index);
                        $sheet->setCellValue("B{$dataRow}", $formattedDate);
                        $sheet->setCellValue("C{$dataRow}", $jamMasuk);
                        $sheet->setCellValue("D{$dataRow}", $jamPulang);
                        $sheet->setCellValue("E{$dataRow}", $deskripsi);

                        // Fallbacks for image drawings
                        if (!$lb || !$lb->foto_kegiatan) {
                            $sheet->setCellValue("F{$dataRow}", '-');
                        }
                        if (!$lb || !$lb->tanda_tangan_admin) {
                            $sheet->setCellValue("G{$dataRow}", '-');
                        }

                        // Dynamic row height
                        $hasImage = false;
                        if (!$isWeekend && !$isHoliday && $lb) {
                            if ($lb->foto_kegiatan) {
                                $fotoPath = storage_path('app/public/' . $lb->foto_kegiatan);
                                if (file_exists($fotoPath) && is_file($fotoPath)) {
                                    $hasImage = true;
                                }
                            }
                            if ($lb->tanda_tangan_admin && str_starts_with($lb->tanda_tangan_admin, 'data:image')) {
                                $hasImage = true;
                            }
                        }

                        if ($hasImage) {
                            $sheet->getRowDimension($dataRow)->setRowHeight(85); // Height in points (~113 pixels)
                        } else {
                            $sheet->getRowDimension($dataRow)->setRowHeight(24); // Normal height
                        }

                        $index++;
                        $dataRow++;
                    }
                    $lastRow = $dataRow - 1;
                }

                // ── Apply Style for the Data Table ──
                if ($lastRow >= $headerRow + 1 && !empty($reportData)) {
                    $dataRange = "A" . ($headerRow + 1) . ":G{$lastRow}";

                    // General styling (vertical center align and light borders)
                    $sheet->getStyle($dataRange)->applyFromArray([
                        'font' => ['size' => 9.5, 'name' => 'Arial', 'color' => ['rgb' => '222222']],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'wrapText' => true
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'DDDDDD']
                            ]
                        ]
                    ]);

                    // Text-align left for columns E (Deskripsi)
                    $sheet->getStyle("E" . ($headerRow + 1) . ":E" . $lastRow)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                    // Text-align center for columns A, B, C, D, F, G (No, Tanggal, Jam, Foto, TTD)
                    foreach (['A', 'B', 'C', 'D', 'F', 'G'] as $col) {
                        $sheet->getStyle($col . ($headerRow + 1) . ":" . $col . $lastRow)
                            ->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }

                    // Alternating row backgrounds (light gray stripes)
                    for ($row = $headerRow + 1; $row <= $lastRow; $row++) {
                        if (($row - $headerRow) % 2 === 0) {
                            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F9FAFB']]
                            ]);
                        }
                    }
                }

                // ── Column Width Dimension Settings ──
                foreach (['A', 'B', 'C', 'D'] as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
                
                // Fixed widths for description and image columns
                $sheet->getColumnDimension('E')->setWidth(45); // Deskripsi Kegiatan
                $sheet->getColumnDimension('F')->setWidth(24); // Foto Kegiatan (approx 173px)
                $sheet->getColumnDimension('G')->setWidth(18); // TTD Admin/Mentor (approx 131px)

                // ── Render Statistics Summary Box at the End ──
                if (!empty($reportData)) {
                    $totalTepat = 0;
                    $totalLambat = 0;
                    $totalAlpa = 0;
                    $totalIzinSakit = 0;
                    $totalLogsApproved = 0;

                    foreach ($reportData as $row) {
                        $att = $row['attendance'];
                        $lb = $row['logbook'];

                        if ($att) {
                            if ($att->status === 'tepat_waktu') {
                                $totalTepat++;
                            } elseif ($att->status === 'terlambat') {
                                $totalLambat++;
                            } elseif ($att->status === 'tidak_hadir') {
                                $totalAlpa++;
                            } elseif ($att->status === 'izin' || $att->status === 'sakit') {
                                $totalIzinSakit++;
                            }
                        }
                        if ($lb && $lb->status === 'approved') {
                            $totalLogsApproved++;
                        }
                    }

                    $totalHadir = $totalTepat + $totalLambat;
                    $totalWorkingDays = $totalHadir + $totalAlpa;
                    $percentage = $totalWorkingDays > 0 ? round(($totalHadir / $totalWorkingDays) * 100, 1) : 0;

                    $summaryStart = $lastRow + 3;
                    
                    // Summary Title Header Box
                    $sheet->mergeCells("B{$summaryStart}:E{$summaryStart}");
                    $sheet->setCellValue("B{$summaryStart}", 'RINGKASAN KEHADIRAN & LOGBOOK MAGANG');
                    $sheet->getStyle("B{$summaryStart}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Arial'],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '000000']
                        ],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->getRowDimension($summaryStart)->setRowHeight(24);
                    
                    // Row 1: Hadir & Approved
                    $row1 = $summaryStart + 1;
                    $sheet->setCellValue("B{$row1}", 'Total Hari Hadir');
                    $sheet->setCellValue("C{$row1}", $totalHadir . ' Hari');
                    $sheet->setCellValue("D{$row1}", 'Logbook Approved');
                    $sheet->setCellValue("E{$row1}", $totalLogsApproved . ' Entri');
                    
                    // Row 2: Tepat Waktu & Tidak Hadir (Alpa)
                    $row2 = $summaryStart + 2;
                    $sheet->setCellValue("B{$row2}", 'Tepat Waktu');
                    $sheet->setCellValue("C{$row2}", $totalTepat . ' Hari');
                    $sheet->setCellValue("D{$row2}", 'Tidak Hadir (Alpa)');
                    $sheet->setCellValue("E{$row2}", $totalAlpa . ' Hari');
                    
                    // Row 3: Terlambat & Izin/Sakit
                    $row3 = $summaryStart + 3;
                    $sheet->setCellValue("B{$row3}", 'Terlambat');
                    $sheet->setCellValue("C{$row3}", $totalLambat . ' Hari');
                    $sheet->setCellValue("D{$row3}", 'Izin / Sakit');
                    $sheet->setCellValue("E{$row3}", $totalIzinSakit . ' Hari');

                    // Row 4: Persentase Kehadiran
                    $row4 = $summaryStart + 4;
                    $sheet->mergeCells("B{$row4}:C{$row4}");
                    $sheet->setCellValue("B{$row4}", 'Persentase Kehadiran');
                    $sheet->mergeCells("D{$row4}:E{$row4}");
                    $sheet->setCellValue("D{$row4}", $percentage . '%');
                    
                    // Styling the stats box range (B to E)
                    $summaryRange = "B{$row1}:E{$row4}";
                    $sheet->getStyle($summaryRange)->applyFromArray([
                        'font' => ['size' => 9.5, 'name' => 'Arial', 'color' => ['rgb' => '222222']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'CCCCCC']
                            ]
                        ]
                    ]);
                    
                    // Specific numeric centering, formatting, heights, and weights
                    foreach ([$row1, $row2, $row3, $row4] as $r) {
                        if ($r === $row4) {
                            $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                            $sheet->getStyle("D{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                            $sheet->getStyle("B{$r}")->getFont()->setBold(true);
                            $sheet->getStyle("D{$r}")->getFont()->setBold(true);
                        } else {
                            $sheet->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                            $sheet->getStyle("E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                            $sheet->getStyle("C{$r}")->getFont()->setBold(true);
                            $sheet->getStyle("E{$r}")->getFont()->setBold(true);
                        }
                        $sheet->getRowDimension($r)->setRowHeight(20);
                    }
                    
                    // Bold the "Persentase Kehadiran" label and highlight percentage value green
                    $sheet->getStyle("D{$row4}")->applyFromArray([
                        'font' => ['color' => ['rgb' => '22C55E'], 'bold' => true] // Sleek green color
                    ]);

                    $lastRow = $row4;
                }

                // ── Document Footer ──
                $footerRow = $lastRow + 2;
                $sheet->setCellValue("A{$footerRow}", 'Dokumen ini dibuat otomatis oleh Attendora — Smart Attendance & Internship Monitoring Platform');
                $sheet->getStyle("A{$footerRow}")->applyFromArray([
                    'font' => ['size' => 8.5, 'color' => ['rgb' => '888888'], 'italic' => true, 'name' => 'Arial'],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
                ]);

                $totalRow = $footerRow + 1;
                $sheet->setCellValue("A{$totalRow}", 'Total records: ' . count($reportData) . ' hari');
                $sheet->getStyle("A{$totalRow}")->applyFromArray([
                    'font' => ['size' => 8.5, 'color' => ['rgb' => '888888'], 'italic' => true, 'name' => 'Arial'],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
                ]);

                // Page setup
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
            }
        ];
    }
}
