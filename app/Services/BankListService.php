<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BankListService
{
    protected $apiUrl = 'https://api.vietqr.io/v2/banks';
    protected $cacheKey = 'vietqr_bank_list';
    protected $cacheDuration = 86400; // 24 hours

    /**
     * Lấy danh sách ngân hàng từ VietQR API
     * 
     * @param bool $forceRefresh Bắt buộc tải lại từ API
     * @return array Mảng danh sách ngân hàng
     */
    public function getBankList(bool $forceRefresh = false): array
    {
        if (!$forceRefresh && Cache::has($this->cacheKey)) {
            return Cache::get($this->cacheKey);
        }

        try {
            $response = Http::timeout(10)->get($this->apiUrl);

            if ($response->successful()) {
                $json = $response->json();
                
                if (isset($json['code']) && $json['code'] === '00' && isset($json['data'])) {
                    $banks = $json['data'];
                    
                    // Sắp xếp theo tên ngắn
                    usort($banks, function($a, $b) {
                        return strcmp($a['shortName'] ?? $a['name'], $b['shortName'] ?? $b['name']);
                    });
                    
                    Cache::put($this->cacheKey, $banks, $this->cacheDuration);
                    
                    Log::info('VietQR bank list cached successfully', [
                        'total_banks' => count($banks)
                    ]);
                    
                    return $banks;
                }
            }

            Log::warning('VietQR API returned unsuccessful response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            // Fallback về cache cũ nếu có
            if (Cache::has($this->cacheKey)) {
                return Cache::get($this->cacheKey);
            }

        } catch (\Exception $e) {
            Log::error('Failed to fetch bank list from VietQR API', [
                'error' => $e->getMessage()
            ]);

            // Fallback về cache cũ nếu có
            if (Cache::has($this->cacheKey)) {
                return Cache::get($this->cacheKey);
            }
        }

        // Fallback cuối cùng: danh sách ngân hàng phổ biến
        return $this->getFallbackBankList();
    }

    /**
     * Tìm ngân hàng theo mã
     * 
     * @param string $code Mã ngân hàng (bankCode)
     * @return array|null Thông tin ngân hàng hoặc null
     */
    public function findByCode(string $code): ?array
    {
        $banks = $this->getBankList();
        
        $code = strtoupper(trim($code));
        
        foreach ($banks as $bank) {
            if (strtoupper($bank['code']) === $code) {
                return $bank;
            }
        }
        
        return null;
    }

    /**
     * Tìm ngân hàng theo tên hoặc tên ngắn
     * 
     * @param string $name Tên ngân hàng
     * @return array|null Thông tin ngân hàng hoặc null
     */
    public function findByName(string $name): ?array
    {
        $banks = $this->getBankList();
        
        $name = strtolower(trim($name));
        
        foreach ($banks as $bank) {
            $bankName = strtolower($bank['name']);
            $shortName = strtolower($bank['shortName'] ?? '');
            
            if (str_contains($bankName, $name) || str_contains($shortName, $name)) {
                return $bank;
            }
        }
        
        return null;
    }

    /**
     * Lấy danh sách ngân hàng theo định dạng select option
     * 
     * @return array ['code' => 'name']
     */
    public function getBankOptions(): array
    {
        $banks = $this->getBankList();
        
        $options = [];
        foreach ($banks as $bank) {
            $options[$bank['code']] = ($bank['shortName'] ?? $bank['name']) . ' (' . $bank['code'] . ')';
        }
        
        return $options;
    }

    /**
     * Xóa cache danh sách ngân hàng
     * 
     * @return bool
     */
    public function clearCache(): bool
    {
        return Cache::forget($this->cacheKey);
    }

    /**
     * Danh sách ngân hàng fallback khi API không khả dụng
     * 
     * @return array
     */
    protected function getFallbackBankList(): array
    {
        return [
            [
                'id' => 17,
                'name' => 'Ngân hàng TMCP Ngoại thương Việt Nam',
                'code' => 'VCB',
                'bin' => '970436',
                'shortName' => 'Vietcombank',
                'logo' => 'https://api.vietqr.io/img/VCB.png',
                'transferSupported' => 1,
                'lookupSupported' => 1
            ],
            [
                'id' => 43,
                'name' => 'Ngân hàng TMCP Kỹ thương Việt Nam',
                'code' => 'TCB',
                'bin' => '970407',
                'shortName' => 'Techcombank',
                'logo' => 'https://api.vietqr.io/img/TCB.png',
                'transferSupported' => 1,
                'lookupSupported' => 1
            ],
            [
                'id' => 38,
                'name' => 'Ngân hàng TMCP Đầu tư và Phát triển Việt Nam',
                'code' => 'BIDV',
                'bin' => '970418',
                'shortName' => 'BIDV',
                'logo' => 'https://api.vietqr.io/img/BIDV.png',
                'transferSupported' => 1,
                'lookupSupported' => 1
            ],
            [
                'id' => 39,
                'name' => 'Ngân hàng TMCP Công thương Việt Nam',
                'code' => 'CTG',
                'bin' => '970415',
                'shortName' => 'VietinBank',
                'logo' => 'https://api.vietqr.io/img/CTG.png',
                'transferSupported' => 1,
                'lookupSupported' => 1
            ],
            [
                'id' => 4,
                'name' => 'Ngân hàng TMCP Á Châu',
                'code' => 'ACB',
                'bin' => '970416',
                'shortName' => 'ACB',
                'logo' => 'https://api.vietqr.io/img/ACB.png',
                'transferSupported' => 1,
                'lookupSupported' => 1
            ],
            [
                'id' => 26,
                'name' => 'Ngân hàng TMCP Quân đội',
                'code' => 'MB',
                'bin' => '970422',
                'shortName' => 'MBBank',
                'logo' => 'https://api.vietqr.io/img/MB.png',
                'transferSupported' => 1,
                'lookupSupported' => 1
            ],
            [
                'id' => 30,
                'name' => 'Ngân hàng TMCP Tiên Phong',
                'code' => 'TPB',
                'bin' => '970423',
                'shortName' => 'TPBank',
                'logo' => 'https://api.vietqr.io/img/TPB.png',
                'transferSupported' => 1,
                'lookupSupported' => 1
            ],
            [
                'id' => 36,
                'name' => 'Ngân hàng TMCP Quốc tế Việt Nam',
                'code' => 'VIB',
                'bin' => '970441',
                'shortName' => 'VIB',
                'logo' => 'https://api.vietqr.io/img/VIB.png',
                'transferSupported' => 1,
                'lookupSupported' => 1
            ],
        ];
    }
}