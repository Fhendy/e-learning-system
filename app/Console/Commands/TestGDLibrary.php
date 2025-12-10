<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestGDLibrary extends Command
{
    protected $signature = 'test:gd';
    protected $description = 'Test GD Library installation';

    public function handle()
    {
        $this->info('Testing GD Library...');
        
        // Test 1: Check GD functions
        if (function_exists('gd_info')) {
            $gd_info = gd_info();
            $this->info('✓ GD Library is installed');
            $this->info('  Version: ' . ($gd_info['GD Version'] ?? 'Unknown'));
            $this->info('  PNG Support: ' . ($gd_info['PNG Support'] ? 'Yes' : 'No'));
            $this->info('  JPEG Support: ' . ($gd_info['JPEG Support'] ? 'Yes' : 'No'));
        } else {
            $this->error('✗ GD Library NOT installed');
        }
        
        // Test 2: Create simple image
        try {
            $im = imagecreatetruecolor(100, 100);
            $white = imagecolorallocate($im, 255, 255, 255);
            $black = imagecolorallocate($im, 0, 0, 0);
            
            imagefilledrectangle($im, 0, 0, 100, 100, $white);
            imagestring($im, 5, 10, 40, 'GD TEST', $black);
            
            $testFile = storage_path('app/public/gd-test.png');
            imagepng($im, $testFile);
            imagedestroy($im);
            
            if (file_exists($testFile)) {
                $this->info('✓ GD can create images');
                $this->info('  Test file: ' . $testFile);
                unlink($testFile);
            }
        } catch (\Exception $e) {
            $this->error('✗ GD image creation failed: ' . $e->getMessage());
        }
    }
}