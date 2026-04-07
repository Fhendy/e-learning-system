<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\QRCode;
use Illuminate\Support\Facades\Log;

class FixQRCodeTimes extends Command
{
    protected $signature = 'qr-codes:fix-times';
    protected $description = 'Fix broken time data in QR codes';

    public function handle()
    {
        $this->info('Starting QR Code time fix...');
        
        $count = QRCode::fixBrokenTimeData();
        
        $this->info("Fixed {$count} QR Codes with broken time data.");
        
        // Tampilkan beberapa contoh
        $examples = QRCode::take(3)->get();
        foreach ($examples as $qr) {
            $this->line("QR Code {$qr->id}: Start='{$qr->start_time}', End='{$qr->end_time}'");
        }
        
        return 0;
    }
}