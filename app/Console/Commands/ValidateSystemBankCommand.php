<?php

namespace App\Console\Commands;

use App\Helpers\SystemBankHelper;
use Illuminate\Console\Command;

class ValidateSystemBankCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:validate-bank
                            {--clear-cache : Clear system bank cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate system bank account setup';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Validating System Bank Account Setup...');
        $this->newLine();

        // Clear cache if requested
        if ($this->option('clear-cache')) {
            SystemBankHelper::clearCache();
            $this->info('✅ Cache cleared');
            $this->newLine();
        }

        // Validate setup
        $validation = SystemBankHelper::validateSetup();

        if ($validation['valid']) {
            $this->info('✅ System bank account setup is VALID');
            $this->newLine();
            
            // Display account info
            $account = SystemBankHelper::getAccount();
            if ($account) {
                $this->table(
                    ['Field', 'Value'],
                    [
                        ['Bank Name', $account->bank_name ?? $account->bank_short_name ?? 'N/A'],
                        ['Bank Code', $account->bank_code ?? 'N/A'],
                        ['Account Number', $account->account_number ?? 'N/A'],
                        ['Account Name', $account->account_name ?? 'N/A'],
                        ['Source', $account instanceof \App\Models\BankAccount ? 'Database' : 'Config'],
                    ]
                );
            }
        } else {
            $this->error('❌ System bank account setup has ISSUES');
            $this->newLine();
            
            // Display issues
            $this->error('Issues found:');
            foreach ($validation['issues'] as $issue) {
                $this->line('  • ' . $issue);
            }
            $this->newLine();
            
            // Display recommendations
            if (!empty($validation['recommendations'])) {
                $this->warn('Recommendations:');
                foreach ($validation['recommendations'] as $recommendation) {
                    $this->line('  → ' . $recommendation);
                }
            }
        }

        $this->newLine();
        
        // Test QR generation
        $this->info('🔍 Testing QR Code generation...');
        try {
            $qrUrl = SystemBankHelper::generateQrCode(100000, 'TEST QR');
            $this->info('✅ QR Code generation: SUCCESS');
            $this->line('   URL: ' . $qrUrl);
        } catch (\Exception $e) {
            $this->error('❌ QR Code generation: FAILED');
            $this->error('   Error: ' . $e->getMessage());
        }

        $this->newLine();
        
        return $validation['valid'] ? Command::SUCCESS : Command::FAILURE;
    }
}