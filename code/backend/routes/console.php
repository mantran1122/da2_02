<?php

use Illuminate\Support\Facades\Artisan;
use App\Jobs\MineComboRulesJob;

Artisan::command('combo:mine-rules', function () {
    $this->info('Đang đẩy Job khai phá luật kết hợp FP-Growth vào hàng đợi Redis...');
    MineComboRulesJob::dispatch();
    $this->info('Job đã được dispatch thành công!');
})->purpose('Quét dữ liệu lịch sử gọi món và cập nhật luật gợi ý combo (FP-Growth)');
