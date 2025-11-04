<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class DownloadProvinces extends Command
{
    protected $signature = 'provinces:download';
    protected $description = 'Download and normalize Vietnam provinces data';

    public function handle()
    {
        $this->info('🌍 Đang tải dữ liệu tỉnh thành...');

        $sources = [
            'https://raw.githubusercontent.com/kenzouno1/DiaGioiHanhChinhVN/master/data.json',
            'https://provinces.open-api.vn/api/?depth=3',
        ];

        $data = null;

        foreach ($sources as $url) {
            try {
                $this->info("📡 Thử tải từ: {$url}");
                $response = Http::timeout(10)->get($url);

                if ($response->successful()) {
                    $data = $response->json();
                    $this->info("✅ Thành công từ: {$url}");
                    break;
                }
            } catch (\Exception $e) {
                $this->warn("⚠️ Lỗi tải: {$url} => " . $e->getMessage());
            }
        }

        if (!$data) {
            $this->error('❌ Không thể tải dữ liệu từ các nguồn!');
            return 1;
        }

       // ✅ Convert format nếu data có dạng Id/Name (GitHub source)
if (isset($data[0]['Id'])) {
    $this->info('🔄 Đang convert dữ liệu sang format chuẩn...');

    $data = array_values(array_filter(array_map(function ($p) {

        if (!is_array($p) || !isset($p["Id"], $p["Name"])) {
            return null;
        }

        return [
            "code" => $p["Id"],
            "name" => $p["Name"],
            "districts" => array_values(array_filter(array_map(function ($d) {

                if (!is_array($d) || !isset($d["Id"], $d["Name"])) {
                    return null;
                }

                return [
                    "code" => $d["Id"],
                    "name" => $d["Name"],
                    "wards" => array_values(array_filter(array_map(function ($w) {

                        if (!is_array($w) || !isset($w["Id"], $w["Name"])) {
                            return null;
                        }

                        return [
                            "code" => $w["Id"],
                            "name" => $w["Name"]
                        ];
                    }, $d["Wards"] ?? [])))
                ];
            }, $p["Districts"] ?? [])))
        ];
    }, $data)));
}


        // ✅ Tạo thư mục
        $path = public_path('data');
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        // ✅ Lưu file
        file_put_contents(
            $path . '/provinces.json',
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );

        $this->info('🎯 Dữ liệu tỉnh thành đã được chuẩn hóa và lưu tại: public/data/provinces.json');
        $this->info('✨ Hoàn thành!');
        return 0;
    }
}
