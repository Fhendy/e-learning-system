<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckQRSystem extends Command
{
    protected $signature = 'qr:check';
    protected $description = 'Check QR code system dependencies';

    public function handle()
    {
        $this->info('=== QR Code System Check ===');
        
        // Check PHP extensions
        $this->info('1. Checking PHP Extensions:');
        $this->line('   GD Library: ' . (function_exists('gd_info') ? '✓ Installed' : '✗ Missing'));
        $this->line('   Imagick: ' . (extension_loaded('imagick') ? '✓ Installed' : '✗ Missing'));
        
        if (function_exists('gd_info')) {
            $gd = gd_info();
            $this->line('   GD Version: ' . ($gd['GD Version'] ?? 'Unknown'));
            $this->line('   PNG Support: ' . ($gd['PNG Support'] ? '✓ Yes' : '✗ No'));
            $this->line('   JPEG Support: ' . ($gd['JPEG Support'] ? '✓ Yes' : '✗ No'));
        }
        
        // Check libraries
        $this->info('\n2. Checking Composer Packages:');
        $packages = [
            'simplesoftwareio/simple-qrcode',
            'endroid/qr-code',
            'bacon/bacon-qr-code',
            'intervention/image'
        ];
        
        foreach ($packages as $package) {
            $installed = $this->isPackageInstalled($package);
            $this->line('   ' . $package . ': ' . ($installed ? '✓ Installed' : '✗ Missing'));
        }
        
        // Check config
        $this->info('\n3. Checking Configuration:');
        $this->line('   IMAGE_DRIVER in .env: ' . env('IMAGE_DRIVER', 'not set'));
        $this->line('   Config image.driver: ' . config('image.driver', 'not set'));
        
        // Test QR generation
        $this->info('\n4. Testing QR Generation:');
        
        try {
            config(['image.driver' => 'gd']);
            $qr = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                ->size(100)
                ->generate('test');
                
            $this->line('   Simple QR Code: ✓ Working (' . strlen($qr) . ' bytes)');
        } catch (\Exception $e) {
            $this->line('   Simple QR Code: ✗ Failed - ' . $e->getMessage());
        }
        
        $this->info('\n=== Recommendations ===');
        
        if (!function_exists('gd_info')) {
            $this->error('• Install GD Library: sudo apt-get install php8.x-gd');
        }
        
        if (!extension_loaded('imagick')) {
            $this->warn('• Imagick not installed (not required but recommended)');
        }
        
        if (env('IMAGE_DRIVER') !== 'gd') {
            $this->error('• Set IMAGE_DRIVER=gd in .env file');
        }
        
        $this->info('\nRun: php artisan config:clear && php artisan cache:clear');
        
        return 0;
    }
    
    private function isPackageInstalled($package)
    {
        $composer = base_path('composer.json');
        $composerData = json_decode(file_get_contents($composer), true);
        
        $requirements = array_merge(
            $composerData['require'] ?? [],
            $composerData['require-dev'] ?? []
        );
        
        return array_key_exists($package, $requirements);
    }
}