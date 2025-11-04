<?php

namespace App\Console\Commands;

use App\Models\Customer\Dashboard\Orders\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoApproveOrders extends Command
{
    protected $signature = 'orders:auto-approve 
                            {--limit=100 : Maximum number of orders to process}
                            {--dry-run : Show what would be approved without actually approving}';

    protected $description = 'Automatically approve low-risk orders';

    public function handle()
    {
        $limit = $this->option('limit');
        $dryRun = $this->option('dry-run');

        $this->info("🤖 Starting auto-approval process...");
        
        if ($dryRun) {
            $this->warn("⚠️  DRY RUN MODE - No orders will be actually approved");
        }

        // Lấy các đơn có thể auto approve
        $orders = Order::with('orderGroup')
            ->pendingApproval()
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        if ($orders->isEmpty()) {
            $this->info("✅ No orders pending auto-approval.");
            return 0;
        }

        $this->info("Found {$orders->count()} pending orders. Analyzing...\n");

        $canApprove = [];
        $needManual = [];

        // Phân tích từng đơn
        foreach ($orders as $order) {
            // Tính risk score nếu chưa có
            if (is_null($order->risk_score)) {
                $order->risk_score = $order->calculateRiskScore();
                if (!$dryRun) {
                    $order->save();
                }
            }

            $riskLevel = $order->risk_level;
            
            if ($order->canAutoApprove()) {
                $canApprove[] = $order;
                $this->line("✓ Order #{$order->id} - Risk: {$riskLevel['label']} ({$order->risk_score}) - CAN AUTO APPROVE");
            } else {
                $needManual[] = $order;
                $this->line("✗ Order #{$order->id} - Risk: {$riskLevel['label']} ({$order->risk_score}) - NEEDS MANUAL REVIEW");
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->table(
            ['Status', 'Count'],
            [
                ['Can Auto Approve', count($canApprove)],
                ['Need Manual Review', count($needManual)],
            ]
        );

        if (empty($canApprove)) {
            $this->info("✅ No orders eligible for auto-approval.");
            return 0;
        }

        if ($dryRun) {
            $this->newLine();
            $this->warn("DRY RUN: Would have approved " . count($canApprove) . " orders.");
            return 0;
        }

        // Confirm trước khi approve
        if (!$this->confirm("Do you want to approve " . count($canApprove) . " orders?", true)) {
            $this->info("Operation cancelled.");
            return 0;
        }

        // Thực hiện approve
        DB::beginTransaction();
        try {
            $approvedCount = 0;
            $approvedOrders = [];
            
            $progressBar = $this->output->createProgressBar(count($canApprove));
            $progressBar->start();

            foreach ($canApprove as $order) {
                if ($order->autoApprove()) {
                    $approvedCount++;
                    $approvedOrders[] = $order->id;
                }
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine(2);
            
            DB::commit();
            
            $this->info("✅ Successfully auto-approved {$approvedCount} orders!");
            $this->info("Order IDs: " . implode(', ', $approvedOrders));
            
            Log::info("Auto approved {$approvedCount} orders via command", [
                'order_ids' => $approvedOrders
            ]);
            
            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->error("❌ Error during auto-approval: " . $e->getMessage());
            Log::error("Auto approval command failed: " . $e->getMessage());
            
            return 1;
        }
    }
}