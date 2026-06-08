<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\ApprovalRequest;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Opportunity;
use App\Models\Payment;
use App\Models\Pool;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SalesTarget;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * DemoMassiveSeeder — generates realistic high-volume demo data.
 *
 * Target volume (configurable via DEMO_SCALE env):
 *   scale=1  → ~50k rows   (default, fast ~30s)
 *   scale=5  → ~250k rows  (medium  ~2min)
 *   scale=20 → ~1M   rows  (full    ~8min)
 *
 * Run: php artisan db:seed --class=DemoMassiveSeeder
 */
class DemoMassiveSeeder extends Seeder
{
    private int $scale;

    // Indonesian company name components
    private array $companyPrefixes = ['PT', 'CV', 'PT', 'PT', 'PT'];
    private array $companyNames = [
        'Maju Jaya', 'Karya Mandiri', 'Nusa Indah', 'Bumi Persada', 'Cahaya Abadi',
        'Duta Pratama', 'Eka Karya', 'Fajar Nusantara', 'Garuda Mas', 'Hijau Lestari',
        'Indah Permai', 'Jaya Makmur', 'Karya Utama', 'Lancar Jaya', 'Mitra Sejati',
        'Niaga Mandiri', 'Omega Prima', 'Prima Jaya', 'Qisthi Raya', 'Rajawali Mas',
        'Surya Gemilang', 'Tiga Berlian', 'Unggul Pratama', 'Visi Nusantara', 'Wahana Baru',
        'Xcelerate Indo', 'Yakin Sukses', 'Zenius Corp', 'Abadi Makmur', 'Bersatu Jaya',
        'Central Mandiri', 'Delta Persada', 'Emas Murni', 'Fortuna Raya', 'Global Mandiri',
        'Harmoni Nusa', 'Inovasi Karya', 'Jitu Sempurna', 'Kencana Mas', 'Luhur Sakti',
        'Mitra Utama', 'Naga Mas', 'Omega Jaya', 'Perdana Karya', 'Quantum Indo',
        'Raya Persada', 'Sentosa Jaya', 'Teguh Mandiri', 'Utama Prima', 'Visi Global',
    ];
    private array $industries = [
        'Banking', 'Technology', 'FMCG', 'Mining', 'Oil & Gas', 'Retail',
        'Hospitality', 'Healthcare', 'Telco', 'Government', 'Automotive',
        'Aviation', 'Energy', 'E-Commerce', 'Pharmaceutical', 'Infrastructure',
        'Manufacturing', 'Insurance', 'Education', 'Property',
    ];
    private array $cities = [
        'Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang', 'Makassar',
        'Palembang', 'Denpasar', 'Yogyakarta', 'Batam', 'Bogor', 'Tangerang',
        'Depok', 'Bekasi', 'Malang', 'Pekanbaru', 'Balikpapan', 'Banjarmasin',
    ];
    private array $dealTitles = [
        'Fleet Management Contract', 'Executive Transport Service', 'Corporate Shuttle Program',
        'VIP Airport Transfer', 'Event Transportation Package', 'Monthly Retainer Fleet',
        'Annual Transport Agreement', 'Dedicated Driver Service', 'Bus Charter Contract',
        'Employee Shuttle Service', 'Board Member Transport', 'Conference Transport',
        'Trade Show Logistics', 'Factory Tour Transport', 'Site Visit Transportation',
        'Roadshow Support Fleet', 'Daily Corporate Commute', 'Weekend Leisure Fleet',
        'Outbound Team Transport', 'Medical Staff Shuttle',
    ];

    public function run(): void
    {
        $this->scale = (int) env('DEMO_SCALE', 1);
        $this->command->info("🚀 DemoMassiveSeeder starting (scale={$this->scale})...");

        DB::statement('PRAGMA journal_mode=WAL;'); // SQLite WAL for speed
        DB::statement('PRAGMA synchronous=NORMAL;');

        $this->seedUsers();
        $this->seedClients();
        $this->seedProducts();
        $this->seedOpportunities();
        $this->seedActivityLogs();
        $this->seedApprovalRequests();
        $this->seedSalesTargets();
        $this->seedSubscriptions();

        $this->command->info('✅ DemoMassiveSeeder complete!');
        $this->printSummary();
    }

    // ── Users ────────────────────────────────────────────────────────────────

    private function seedUsers(): void
    {
        if (User::where('role', 'sales')->count() >= 3) {
            $this->command->info('  ↳ Users already exist, skipping.');
            return;
        }

        $this->command->info('  → Seeding users...');

        $salesNames = [
            'Andi Pratama', 'Sari Dewi', 'Reza Firmansyah', 'Budi Hartono', 'Citra Lestari',
            'Dedy Kurniawan', 'Eka Suharto', 'Fajar Nugroho', 'Gina Pratiwi', 'Hendra Wijaya',
        ];

        foreach ($salesNames as $i => $name) {
            User::firstOrCreate(
                ['email' => 'sales' . ($i + 1) . '@demo.crm'],
                [
                    'name'     => $name,
                    'password' => bcrypt('password'),
                    'role'     => 'sales',
                ]
            );
        }
    }

    // ── Clients ──────────────────────────────────────────────────────────────

    private function seedClients(): void
    {
        $target  = 100 * $this->scale;
        $existing = Client::count();
        $toCreate = max(0, $target - $existing);

        if ($toCreate === 0) {
            $this->command->info("  ↳ Clients already at target ({$target}), skipping.");
            return;
        }

        $this->command->info("  → Seeding {$toCreate} clients...");

        $salesIds = User::where('role', 'sales')->pluck('id')->toArray();
        if (empty($salesIds)) $salesIds = [1];

        $rows = [];
        $now  = now()->toDateTimeString();

        for ($i = 0; $i < $toCreate; $i++) {
            $prefix  = $this->companyPrefixes[array_rand($this->companyPrefixes)];
            $name    = $this->companyNames[array_rand($this->companyNames)];
            $suffix  = $this->companyNames[array_rand($this->companyNames)];
            $city    = $this->cities[array_rand($this->cities)];
            $industry= $this->industries[array_rand($this->industries)];

            $rows[] = [
                'company_name'      => $prefix . ' ' . $name . ' ' . $suffix,
                'pic_name'          => 'Bapak/Ibu ' . $name,
                'phone'             => '08' . rand(100000000, 999999999),
                'email'             => strtolower(str_replace(' ', '.', $name)) . rand(1, 999) . '@corp.id',
                'address'           => 'Jl. ' . $suffix . ' No. ' . rand(1, 200) . ', ' . $city,
                'industry'          => $industry,
                'status'            => rand(0, 9) < 8 ? 'active' : 'inactive',
                'assigned_sales_id' => $salesIds[array_rand($salesIds)],
                'notes'             => 'Demo client — ' . $industry . ' sector, ' . $city,
                'created_at'        => $now,
                'updated_at'        => $now,
            ];

            if (count($rows) >= 500) {
                DB::table('clients')->insert($rows);
                $rows = [];
            }
        }
        if ($rows) DB::table('clients')->insert($rows);

        $this->command->info('  ✓ Clients: ' . Client::count());
    }

    // ── Products ─────────────────────────────────────────────────────────────

    private function seedProducts(): void
    {
        $this->command->info('  → Seeding 6 produk wajib...');

        // Kategori sesuai jenisnya.
        $shortTerm = ProductCategory::firstOrCreate(['name' => 'Short Term', 'type' => 'short_term']);
        $longTerm  = ProductCategory::firstOrCreate(['name' => 'Long Term',  'type' => 'long_term']);
        $evoucher  = ProductCategory::firstOrCreate(['name' => 'E-Voucher',  'type' => 'evoucher']);
        $service   = ProductCategory::firstOrCreate(['name' => 'Service',    'type' => 'short_term']);

        // 6 produk fix dengan kpi_key untuk pemetaan target KPI per produk.
        // [name, kpi_key, category_id, base_price, sku]
        $products = [
            ['Mobil Short Term', 'mobil_short', $shortTerm->id, 1_200_000,   'PRD-MOBIL-ST'],
            ['Bis Short Term',   'bis_short',   $shortTerm->id, 5_000_000,   'PRD-BIS-ST'],
            ['E-Voucher',        'evoucher',    $evoucher->id,  500_000,     'PRD-EVOUCHER'],
            ['Mobil Long Term',  'mobil_long',  $longTerm->id,  25_000_000,  'PRD-MOBIL-LT'],
            ['Bis Long Term',    'bis_long',    $longTerm->id,  35_000_000,  'PRD-BIS-LT'],
            ['Supir',            'supir',       $service->id,   300_000,     'PRD-SUPIR'],
        ];

        foreach ($products as [$name, $kpiKey, $catId, $price, $sku]) {
            Product::updateOrCreate(
                ['kpi_key' => $kpiKey],
                [
                    'product_category_id' => $catId,
                    'name'                => $name,
                    'sku'                 => $sku,
                    'base_price'          => $price,
                    'unit'                => 'trip',
                    'is_active'           => true,
                    'description'         => $name . ' — layanan transportasi Golden Bird',
                ]
            );
        }
    }

    // ── Opportunities ─────────────────────────────────────────────────────────

    private function seedOpportunities(): void
    {
        $target  = 500 * $this->scale;
        $existing = Opportunity::count();
        $toCreate = max(0, $target - $existing);

        if ($toCreate === 0) {
            $this->command->info("  ↳ Opportunities already at target ({$target}), skipping.");
            return;
        }

        $this->command->info("  → Seeding {$toCreate} opportunities...");

        $clientIds  = Client::pluck('id')->toArray();
        $salesIds   = User::where('role', 'sales')->pluck('id')->toArray();
        $productIds = Product::pluck('id')->toArray();
        $stages     = ['prospecting', 'prospecting', 'prospecting', 'proposal', 'proposal', 'negotiation', 'won', 'lost'];

        if (empty($clientIds)) { $this->command->warn('No clients, skip opportunities.'); return; }
        if (empty($salesIds))  $salesIds = [1];

        $rows   = [];
        $now    = now();
        $yearMonth = $now->format('Ym');
        $seq    = Opportunity::where('opp_number', 'like', "OPP-{$yearMonth}-%")->count();

        for ($i = 0; $i < $toCreate; $i++) {
            $seq++;
            $stage     = $stages[array_rand($stages)];
            $salesId   = $salesIds[array_rand($salesIds)];
            $clientId  = $clientIds[array_rand($clientIds)];
            $productId = !empty($productIds) ? $productIds[array_rand($productIds)] : null;
            $value     = rand(5, 800) * 1_000_000;
            $daysAgo   = rand(0, 365);
            $created   = $now->copy()->subDays($daysAgo)->subHours(rand(0, 23));

            $row = [
                'opp_number'          => 'OPP-' . $yearMonth . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT),
                'client_id'           => $clientId,
                'sales_id'            => $salesId,
                'product_id'          => $productId,
                'title'               => $this->dealTitles[array_rand($this->dealTitles)] . ' — ' . rand(1, 50) . ' unit',
                'stage'               => $stage,
                'estimated_value'     => $value,
                'final_value'         => in_array($stage, ['won', 'lost']) ? $value * (rand(85, 100) / 100) : null,
                'pax'                 => rand(10, 500),
                'discount_percent'    => rand(0, 15),
                'discount_approved'   => rand(0, 1),
                'approved_by'         => null,
                'expected_close_date' => $now->copy()->addDays(rand(-30, 90))->toDateString(),
                'actual_close_date'   => in_array($stage, ['won', 'lost']) ? $created->copy()->addDays(rand(5, 60))->toDateString() : null,
                'lost_reason'         => $stage === 'lost' ? ['Harga terlalu tinggi', 'Kalah dari kompetitor', 'Budget klien dipotong', 'Proyek ditunda', 'Klien batal'][array_rand(['Harga terlalu tinggi', 'Kalah dari kompetitor', 'Budget klien dipotong', 'Proyek ditunda', 'Klien batal'])] : null,
                'notes'               => 'Demo opportunity #' . $seq,
                'booking_id'          => null,
                'subscription_id'     => null,
                'created_at'          => $created->toDateTimeString(),
                'updated_at'          => $created->toDateTimeString(),
            ];

            $rows[] = $row;

            if (count($rows) >= 200) {
                DB::table('opportunities')->insert($rows);
                $rows = [];
            }
        }
        if ($rows) DB::table('opportunities')->insert($rows);

        $this->command->info('  ✓ Opportunities: ' . Opportunity::count());
    }

    // ── Activity Logs ────────────────────────────────────────────────────────

    private function seedActivityLogs(): void
    {
        $target   = 2000 * $this->scale;
        $existing = ActivityLog::count();
        $toCreate = max(0, $target - $existing);

        if ($toCreate === 0) {
            $this->command->info("  ↳ Activity logs already at target, skipping.");
            return;
        }

        $this->command->info("  → Seeding {$toCreate} activity logs...");

        $salesIds       = User::where('role', 'sales')->pluck('id')->toArray();
        $clientIds      = Client::pluck('id')->toArray();
        $opportunityIds = Opportunity::pluck('id')->toArray();
        $types          = ['call', 'email', 'meeting', 'follow_up', 'proposal_sent', 'demo'];
        $subjects       = [
            'Follow up via telepon', 'Kirim proposal ke klien', 'Meeting presentasi produk',
            'Demo layanan fleet', 'Negosiasi harga kontrak', 'Tanda tangan MoU',
            'Kunjungan site klien', 'Update status deal', 'Konfirmasi kebutuhan kendaraan',
            'Presentasi ke manajemen', 'Diskusi kontrak tahunan', 'Check in bulanan',
        ];

        if (empty($salesIds))       $salesIds = [1];
        if (empty($clientIds))      { $this->command->warn('No clients for activity logs.'); return; }
        if (empty($opportunityIds)) { $this->command->warn('No opps for activity logs.'); return; }

        $rows = [];
        $now  = now();

        for ($i = 0; $i < $toCreate; $i++) {
            $created = $now->copy()->subDays(rand(0, 365))->subHours(rand(0, 23));
            $rows[] = [
                'sales_id'       => $salesIds[array_rand($salesIds)],
                'client_id'      => $clientIds[array_rand($clientIds)],
                'opportunity_id' => rand(0, 3) ? $opportunityIds[array_rand($opportunityIds)] : null,
                'type'           => $types[array_rand($types)],
                'subject'        => $subjects[array_rand($subjects)],
                'notes'          => 'Demo activity log #' . ($i + 1),
                'activity_date'  => $created->toDateString(),
                'created_at'     => $created->toDateTimeString(),
                'updated_at'     => $created->toDateTimeString(),
            ];

            if (count($rows) >= 500) {
                DB::table('activity_logs')->insert($rows);
                $rows = [];
            }
        }
        if ($rows) DB::table('activity_logs')->insert($rows);

        $this->command->info('  ✓ Activity logs: ' . ActivityLog::count());
    }

    // ── Approval Requests ────────────────────────────────────────────────────

    private function seedApprovalRequests(): void
    {
        $target   = 300 * $this->scale;
        $existing = ApprovalRequest::count();
        $toCreate = max(0, $target - $existing);

        if ($toCreate === 0) { $this->command->info("  ↳ Approval requests already at target, skipping."); return; }

        $this->command->info("  → Seeding {$toCreate} approval requests...");

        $opportunities = Opportunity::where('discount_percent', '>', 0)
            ->get(['id', 'sales_id', 'estimated_value', 'discount_percent']);
        if ($opportunities->isEmpty()) { $this->command->warn('No opps with discount for approvals.'); return; }

        $approverIds = User::whereIn('role', ['manager', 'gm'])->pluck('id')->toArray();
        if (empty($approverIds)) $approverIds = [1];

        $statuses = ['pending', 'pending', 'approved', 'approved', 'approved', 'rejected'];
        $rows = [];
        $now  = now();

        for ($i = 0; $i < $toCreate; $i++) {
            $created = $now->copy()->subDays(rand(0, 180));
            $status  = $statuses[array_rand($statuses)];
            $opportunity = $opportunities->random();
            $discountPercent = rand(5, 20);
            $originalPrice = (float) ($opportunity->estimated_value ?: rand(50, 500) * 1_000_000);
            $finalPrice = $originalPrice * (1 - ($discountPercent / 100));
            $decidedAt = $status !== 'pending' ? $created->copy()->addDays(rand(1, 5))->toDateTimeString() : null;

            $rows[] = [
                'opportunity_id'       => $opportunity->id,
                'requested_by'         => $opportunity->sales_id,
                'current_approver_id'  => $approverIds[array_rand($approverIds)],
                'type'                 => 'discount',
                'level'                => rand(1, 3),
                'discount_percent'     => $discountPercent,
                'original_price'       => $originalPrice,
                'final_price'          => $finalPrice,
                'status'               => $status,
                'notes'                => $status === 'approved' ? 'Disetujui sesuai kebijakan perusahaan' : null,
                'rejection_reason'     => $status === 'rejected' ? 'Diskon terlalu besar, revisi proposal' : null,
                'approved_at'          => $status === 'approved' ? $decidedAt : null,
                'rejected_at'          => $status === 'rejected' ? $decidedAt : null,
                'created_at'           => $created->toDateTimeString(),
                'updated_at'           => $created->toDateTimeString(),
            ];

            if (count($rows) >= 300) {
                DB::table('approval_requests')->insert($rows);
                $rows = [];
            }
        }
        if ($rows) DB::table('approval_requests')->insert($rows);

        $this->command->info('  ✓ Approval requests: ' . ApprovalRequest::count());
    }

    // ── Sales Targets ────────────────────────────────────────────────────────

    private function seedSalesTargets(): void
    {
        $this->command->info('  → Seeding sales targets...');

        $salesUsers = User::where('role', 'sales')->get();
        $now = now();

        foreach ($salesUsers as $sales) {
            for ($monthsBack = 0; $monthsBack < 12 * $this->scale; $monthsBack++) {
                $date = $now->copy()->subMonths($monthsBack);
                SalesTarget::firstOrCreate(
                    ['user_id' => $sales->id, 'period_month' => $date->month, 'period_year' => $date->year],
                    [
                        'target_meetings'      => rand(8, 20),
                        'target_calls'         => rand(40, 120),
                        'target_visits'        => rand(6, 16),
                        'target_opportunities' => rand(8, 24),
                        'target_won'           => rand(2, 8),
                        'target_revenue'       => rand(200, 800) * 1_000_000,
                        'actual_meetings'      => rand(4, 24),
                        'actual_calls'         => rand(20, 140),
                        'actual_visits'        => rand(3, 18),
                        'actual_opportunities' => rand(4, 28),
                        'actual_won'           => rand(1, 10),
                        'actual_revenue'       => rand(150, 850) * 1_000_000,
                    ]
                );
            }
        }

        $this->command->info('  ✓ Sales targets: ' . SalesTarget::count());
    }

    // ── Subscriptions ────────────────────────────────────────────────────────

    private function seedSubscriptions(): void
    {
        $target   = 50 * $this->scale;
        $existing = Subscription::count();
        $toCreate = max(0, $target - $existing);

        if ($toCreate === 0) { $this->command->info("  ↳ Subscriptions already at target, skipping."); return; }

        $this->command->info("  → Seeding {$toCreate} subscriptions...");

        $clientIds  = Client::pluck('id')->toArray();
        $salesIds   = User::where('role', 'sales')->pluck('id')->toArray();
        $productIds = Product::pluck('id')->toArray();

        if (empty($clientIds)) return;
        if (empty($salesIds))  $salesIds = [1];

        $rows = [];
        $now  = now();
        $statuses = ['active', 'active', 'active', 'expired', 'terminated'];

        for ($i = 0; $i < $toCreate; $i++) {
            $start   = $now->copy()->subMonths(rand(1, 24));
            $end     = $start->copy()->addMonths(rand(3, 24));
            $status  = $statuses[array_rand($statuses)];
            $value   = rand(20, 200) * 1_000_000;

            $rows[] = [
                'client_id'       => $clientIds[array_rand($clientIds)],
                'product_id'      => !empty($productIds) ? $productIds[array_rand($productIds)] : null,
                'sub_number'      => 'SUB-' . $now->format('Ym') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'start_date'      => $start->toDateString(),
                'end_date'        => $end->toDateString(),
                'monthly_rate'    => $value,
                'billing_cycle'   => 'monthly',
                'last_billed_at'  => $status === 'active' ? $now->copy()->subMonth()->toDateString() : null,
                'next_billing_date' => $status === 'active' ? $now->copy()->addMonth()->toDateString() : null,
                'status'          => $status,
                'auto_renew'      => rand(0, 1),
                'notes'           => 'Demo subscription #' . ($i + 1),
                'created_at'      => $start->toDateTimeString(),
                'updated_at'      => $start->toDateTimeString(),
            ];

            if (count($rows) >= 200) {
                DB::table('subscriptions')->insert($rows);
                $rows = [];
            }
        }
        if ($rows) DB::table('subscriptions')->insert($rows);

        $this->command->info('  ✓ Subscriptions: ' . Subscription::count());
    }

    // ── Summary ──────────────────────────────────────────────────────────────

    private function printSummary(): void
    {
        $this->command->table(
            ['Table', 'Count'],
            [
                ['users',             User::count()],
                ['clients',           Client::count()],
                ['products',          Product::count()],
                ['opportunities',     Opportunity::count()],
                ['activity_logs',     ActivityLog::count()],
                ['approval_requests', ApprovalRequest::count()],
                ['sales_targets',     SalesTarget::count()],
                ['subscriptions',     Subscription::count()],
            ]
        );
    }
}
