<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class QrCodeService
{
    public function generate($url, $code = null)
    {
        // Try multiple methods
        
        // Method 1: Simple QR Code with GD
        try {
            $qrCode = \QrCode::format('png')
                ->size(300)
                ->margin(1)
                ->errorCorrection('H')
                ->encoding('UTF-8')
                ->generate($url);
                
            return $qrCode;
        } catch (\Exception $e) {
            Log::warning('Method 1 failed: ' . $e->getMessage());
        }
        
        // Method 2: Google Charts API
        try {
            $response = Http::timeout(10)->get('https://chart.googleapis.com/chart', [
                'chs' => '300x300',
                'cht' => 'qr',
                'chl' => $url,
                'choe' => 'UTF-8',
                'chld' => 'H|1'
            ]);
            
            if ($response->successful()) {
                return $response->body();
            }
        } catch (\Exception $e) {
            Log::warning('Method 2 failed: ' . $e->getMessage());
        }
        
        // Method 3: GD library directly
        return $this->generateWithGD($url, $code);
    }
    
    private function generateWithGD($url, $code)
    {
        $width = 300;
        $height = 300;
        
        $image = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        
        imagefilledrectangle($image, 0, 0, $width, $height, $white);
        
        // Add text
        $text = "SCAN ME\n" . ($code ?: substr($url, 0, 20));
        $lines = explode("\n", $text);
        
        $font = 5;
        $lineHeight = 20;
        $startY = ($height - (count($lines) * $lineHeight)) / 2;
        
        foreach ($lines as $i => $line) {
            $textWidth = imagefontwidth($font) * strlen($line);
            $x = ($width - $textWidth) / 2;
            $y = $startY + ($i * $lineHeight);
            imagestring($image, $font, $x, $y, $line, $black);
        }
        
        ob_start();
        imagepng($image);
        $data = ob_get_clean();
        imagedestroy($image);
        
        return $data;
    }
}