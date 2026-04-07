<?php

namespace App\Console\Commands;

use App\Models\QRCode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RegenerateQrImages extends Command
{
    protected $signature = 'qr:regenerate-images';
    protected $description = 'Regenerate all QR code images';

    public function handle()
    {
        $qrCodes = QRCode::all();
        $total = $qrCodes->count();
        $success = 0;
        
        $this->info("Memproses {$total} QR Code...");
        
        foreach ($qrCodes as $qrCode) {
            try {
                $url = url('/attendance/scan-page') . '?qr_code=' . $qrCode->code;
                
                // Generate QR code
                $qrCodeImage = $this->generateQrCodeImage($url, $qrCode->code);
                
                $imageName = 'qr-codes/' . $qrCode->code . '.png';
                
                // Save image
                if (!Storage::disk('public')->exists('qr-codes')) {
                    Storage::disk('public')->makeDirectory('qr-codes');
                }
                
                Storage::disk('public')->put($imageName, $qrCodeImage);
                $qrCode->update(['qr_code_image' => $imageName]);
                
                $success++;
                $this->info("✓ {$qrCode->code}");
                
            } catch (\Exception $e) {
                $this->error("✗ {$qrCode->code}: " . $e->getMessage());
            }
        }
        
        $this->info("Selesai! Berhasil: {$success} dari {$total}");
    }
    
    private function generateQrCodeImage($url, $code = null)
    {
        // Gunakan Google Charts API
        $googleChartsUrl = 'https://chart.googleapis.com/chart?' . http_build_query([
            'chs' => '300x300',
            'cht' => 'qr',
            'chl' => urlencode($url),
            'choe' => 'UTF-8',
            'chld' => 'H|2',
        ]);
        
        $context = stream_context_create([
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
            'http' => ['timeout' => 10, 'ignore_errors' => true]
        ]);
        
        $imageData = @file_get_contents($googleChartsUrl, false, $context);
        
        if ($imageData === false || strlen($imageData) < 100) {
            // Fallback ke GD
            $size = 300;
            $image = imagecreatetruecolor($size, $size);
            $white = imagecolorallocate($image, 255, 255, 255);
            $black = imagecolorallocate($image, 0, 0, 0);
            
            imagefilledrectangle($image, 0, 0, $size, $size, $white);
            imagerectangle($image, 5, 5, $size-5, $size-5, $black);
            
            $text = "QR CODE\nKode: " . ($code ?: 'N/A');
            $lines = explode("\n", $text);
            $font = 5;
            $lineHeight = 30;
            $startY = ($size - (count($lines) * $lineHeight)) / 2;
            
            foreach ($lines as $i => $line) {
                $textWidth = imagefontwidth($font) * strlen($line);
                $x = ($size - $textWidth) / 2;
                $y = $startY + ($i * $lineHeight);
                imagestring($image, $font, $x, $y, $line, $black);
            }
            
            ob_start();
            imagepng($image);
            $imageData = ob_get_clean();
            imagedestroy($image);
        }
        
        return $imageData;
    }
}