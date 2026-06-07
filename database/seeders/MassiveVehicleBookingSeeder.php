<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * MassiveVehicleBookingSeeder
 *
 * Generates demo-scale data:
 *   - 100,000 vehicles (armada)
 *   - 500,000 bookings
 *   - 50,000 e-vouchers
 *   - KPI targets for all sales users, all months 2024–2026
 *
 * Run: php artisan db:seed --class=MassiveVehicleBookingSeeder
 * Est. time: ~3–5 min on PostgreSQL
 */
class MassiveVehicleBookingSeeder extends Seeder
{
    // ── Vehicle data pools ───────────────────────────────────────────────────

    private array $areaPlates = [
        'B','D','F','H','L','N','S','T','W','Z',
        'AB','AD','AE','AG','K','AA','BE','BG','BH','BK',
        'BL','BM','BN','BP','DA','DB','DC','DD','DE',
    ];

    private array $vehicleModels = [
        // Model => [brand, type, capacity, bbm]
        'Alphard'    => ['Toyota',       'executive', 6,  'bensin'],
        'Vellfire'   => ['Toyota',       'executive', 6,  'bensin'],
        'Fortuner'   => ['Toyota',       'short_trip',5,  'solar'],
        'Innova'     => ['Toyota',       'short_trip',7,  'solar'],
        'Hiace'      => ['Toyota',       'shuttle',   12, 'solar'],
        'Coaster'    => ['Toyota',       'bus',       30, 'solar'],
        'Travego'    => ['Mercedes-Benz','bus',       50, 'solar'],
        'Sprinter'   => ['Mercedes-Benz','shuttle',   16, 'solar'],
        'Rosa'       => ['Mitsubishi',   'bus',       25, 'solar'],
        'Elf'        => ['Isuzu',        'shuttle',   12, 'solar'],
        'NQR'        => ['Isuzu',        'bus',       40, 'solar'],
        'Canter'     => ['Mitsubishi',   'short_trip',8,  'solar'],
        'Touring'    => ['Scania',       'bus',       50, 'solar'],
        'Irizar'     => ['Scania',       'bus',       45, 'solar'],
        'Lion Coach' => ['MAN',          'bus',       50, 'solar'],
        'Accord'     => ['Honda',        'executive', 5,  'bensin'],
        'Odyssey'    => ['Honda',        'shuttle',   8,  'bensin'],
        'Staria'     => ['Hyundai',      'shuttle',   11, 'bensin'],
        'H350'       => ['Hyundai',      'shuttle',   15, 'solar'],
        'Grand Max'  => ['Daihatsu',     'short_trip',6,  'bensin'],
    ];

    private array $colors = ['Putih','Hitam','Silver','Abu-abu','Biru Navy','Biru Muda','Merah','Gold','Champagne','Ivory'];
    private array $transmissions = ['automatic','manual'];
    private array $statuses = ['available','available','available','available','available','available','available','on_trip','on_trip','maintenance'];
    private array $pools5 = [];

    private array $pickupLocations = [
        'Jl. Gatot Subroto No.1, Jakarta Selatan',
        'Jl. Sudirman Kav.52, Jakarta Pusat',
        'Jl. HR Rasuna Said Blok X-1, Jakarta Selatan',
        'Jl. TB Simatupang No.88, Jakarta Selatan',
        'Bandara Soekarno-Hatta Terminal 3, Tangerang',
        'Jl. Asia Afrika No.8, Bandung',
        'Jl. Pemuda No.17, Surabaya',
        'Jl. Malioboro No.52, Yogyakarta',
        'Jl. Imam Bonjol No.100, Semarang',
        'Jl. Gajah Mada No.38, Medan',
        'Jl. Sam Ratulangi No.10, Makassar',
        'Jl. Diponegoro No.75, Denpasar',
        'Jl. Ahmad Yani No.60, Batam',
        'Jl. Pahlawan No.12, Palembang',
        'Jl. A.Yani Km.5 No.9, Banjarmasin',
        'Hotel Grand Hyatt Jakarta, Jl. MH Thamrin',
        'Hotel Mulia Senayan, Jl. Asia Afrika',
        'SCBD Lot 7, Jakarta Selatan',
        'Kawasan Industri MM2100, Bekasi',
        'Gedung Bursa Efek Indonesia, Jakarta Selatan',
    ];

    private array $dropoffLocations = [
        'Bandara Soekarno-Hatta Terminal 1, Tangerang',
        'Bandara Soekarno-Hatta Terminal 2, Tangerang',
        'Bandara Soekarno-Hatta Terminal 3, Tangerang',
        'Bandara Halim Perdanakusuma, Jakarta',
        'Stasiun Gambir, Jakarta Pusat',
        'Stasiun Pasar Senen, Jakarta Pusat',
        'Pelabuhan Tanjung Priok, Jakarta Utara',
        'Hotel Mulia Bali, Nusa Dua',
        'Hotel Borobudur Jakarta, Lapangan Banteng',
        'Istana Negara, Jakarta Pusat',
        'Kompleks DPR/MPR, Jakarta Barat',
        'Kawasan EJIP, Bekasi Timur',
        'Kawasan Industri Karawang',
        'Kota Baru Parahyangan, Bandung Barat',
        'Universitas Gadjah Mada, Yogyakarta',
        'Surabaya Grand City Mall',
        'Bali Nusa Dua Convention Center',
        'Lombok International Airport',
        'Manado Town Square',
        'Makassar Trans Studio Mall',
    ];

    private array $bookingStatuses = [
        'completed','completed','completed','completed','completed','completed', // 60%
        'confirmed','confirmed',  // 13%
        'on_trip','on_trip',      // 13%
        'pending',                // 7%
        'cancelled',              // 7%
    ];

    private array $voucherTitles = [
        'E-Voucher Transport Corporate Annual',
        'E-Voucher Airport Transfer Premium',
        'E-Voucher Shuttle Karyawan Bulanan',
        'E-Voucher Fleet Charter Event',
        'E-Voucher Executive Transport VIP',
        'E-Voucher Perjalanan Dinas',
        'E-Voucher Group Tour Package',
        'E-Voucher Long Distance Corporate',
    ];

    // ── Main entry point ─────────────────────────────────────────────────────

    public function run(): void
    {
        $this->command->info('🚀 MassiveVehicleBookingSeeder starting...');
        $this->command->info('   This will seed 100k vehicles, 500k bookings, 50k vouchers, KPI targets.');

        $this->ensurePools();
        $this->seedVehicles();
        $this->seedBookings();
        $this->seedVouchers();
        $this->seedKpiTargets();

        $this->command->info('✅ MassiveVehicleBookingSeeder complete!');
        $this->printSummary();
    }

    // ── Ensure pools exist ───────────────────────────────────────────────────

    private function ensurePools(): void
    {
        $poolDefs = [
            ['name' => 'Pool Jakarta Selatan',  'location' => 'Jl. TB Simatupang No.1, Jaksel',  'capacity' => 30000],
            ['name' => 'Pool Jakarta Barat',    'location' => 'Jl. Daan Mogot Km.19, Jakbar',    'capacity' => 25000],
            ['name' => 'Pool Tangerang',        'location' => 'Jl. Industri Raya No.5, Tangerang','capacity' => 20000],
            ['name' => 'Pool Surabaya',         'location' => 'Jl. Raya Juanda No.10, Sidoarjo', 'capacity' => 15000],
            ['name' => 'Pool Bandung',          'location' => 'Jl. Soekarno Hatta No.200, Bdg',  'capacity' => 10000],
        ];

        foreach ($poolDefs as $p) {
            $existing = DB::table('pools')->where('name', $p['name'])->first();
            if (! $existing) {
                DB::table('pools')->insert(array_merge($p, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        $this->pools5 = DB::table('pools')->pluck('id')->toArray();
        $this->command->info('  ✓ Pools ready: ' . count($this->pools5));
    }

    // ── Vehicles ─────────────────────────────────────────────────────────────

    private function seedVehicles(): void
    {
        $target   = 100_000;
        $existing = DB::table('vehicles')->count();
        $toCreate = max(0, $target - $existing);

        if ($toCreate === 0) {
            $this->command->info("  ↳ Vehicles already at {$target}, skipping.");
            return;
        }

        $this->command->info("  → Seeding {$toCreate} vehicles (in 1,000-row chunks)...");

        $models      = array_keys($this->vehicleModels);
        $plateTrack  = []; // avoid duplicates in this batch
        $rows        = [];
        $now         = now()->toDateTimeString();
        $chunkSize   = 1000;
        $done        = 0;

        for ($i = 0; $i < $toCreate; $i++) {
            $modelName = $models[array_rand($models)];
            $spec      = $this->vehicleModels[$modelName];
            [$brand, $type, $cap, $bbm] = $spec;

            // Generate unique plate
            $plate = $this->randomPlate($plateTrack);

            // Transmission: 80% automatic for executive/bus
            $trans = ($type === 'executive' || $type === 'bus')
                ? (rand(1, 10) <= 8 ? 'automatic' : 'manual')
                : ($this->transmissions[array_rand($this->transmissions)]);

            // STNK & pajak: spread across 2024–2027
            $stnkBase  = Carbon::create(2024, 1, 1)->addDays(rand(0, 1460));
            $pajakBase = Carbon::create(2024, 1, 1)->addDays(rand(0, 1095));

            $rows[] = [
                'plate_number'     => $plate,
                'brand'            => $brand,
                'model'            => $modelName,
                'type'             => $type,
                'capacity'         => $cap,
                'color'            => $this->colors[array_rand($this->colors)],
                'transmission'     => $trans,
                'bbm_type'         => $bbm,
                'current_km'       => rand(5_000, 350_000),
                'year_manufactured'=> rand(2010, 2024),
                'stnk_expiry'      => $stnkBase->toDateString(),
                'pajak_expiry'     => $pajakBase->toDateString(),
                'status'           => $this->statuses[array_rand($this->statuses)],
                'pool_id'          => $this->pools5[array_rand($this->pools5)],
                'notes'            => "Unit armada {$brand} {$modelName} — siap operasional.",
                'created_at'       => $now,
                'updated_at'       => $now,
            ];

            if (count($rows) >= $chunkSize) {
                try {
                    DB::table('vehicles')->insertOrIgnore($rows);
                    $done += count($rows);
                    if ($done % 10_000 === 0) {
                        $this->command->info("    ... {$done}/{$toCreate} vehicles inserted");
                    }
                } catch (\Exception $e) {
                    $this->command->warn("    chunk error: " . $e->getMessage());
                }
                $rows = [];
            }
        }

        if ($rows) {
            DB::table('vehicles')->insertOrIgnore($rows);
        }

        $this->command->info('  ✓ Vehicles: ' . DB::table('vehicles')->count());
    }

    private function randomPlate(array &$track): string
    {
        $attempts = 0;
        do {
            $area   = $this->areaPlates[array_rand($this->areaPlates)];
            $num    = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $suffix = chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90));
            $plate  = "{$area} {$num} {$suffix}";
            $attempts++;
        } while (isset($track[$plate]) && $attempts < 20);

        $track[$plate] = true;
        return $plate;
    }

    // ── Bookings ─────────────────────────────────────────────────────────────

    private function seedBookings(): void
    {
        $target   = 500_000;
        $existing = DB::table('bookings')->count();
        $toCreate = max(0, $target - $existing);

        if ($toCreate === 0) {
            $this->command->info("  ↳ Bookings already at {$target}, skipping.");
            return;
        }

        $this->command->info("  → Seeding {$toCreate} bookings (in 2,000-row chunks)...");

        // Load IDs
        $clientIds  = DB::table('clients')->pluck('id')->toArray();
        $vehicleIds = DB::table('vehicles')->pluck('id')->toArray();
        $userIds    = DB::table('users')->whereIn('role', ['sales','manager','operational'])->pluck('id')->toArray();

        if (empty($clientIds))  $clientIds  = [1];
        if (empty($vehicleIds)) $vehicleIds = [1];
        if (empty($userIds))    $userIds    = [1];

        $vehicleTypes = ['short_trip','long_trip','executive','bus','shuttle'];
        $rows      = [];
        $now       = now()->toDateTimeString();
        $chunkSize = 2000;
        $done      = 0;
        $counter   = $existing + 1;

        for ($i = 0; $i < $toCreate; $i++) {
            $pickupDate   = Carbon::create(2024, 1, 1)->addDays(rand(0, 1095));
            $duration     = rand(1, 30);
            $status       = $this->bookingStatuses[array_rand($this->bookingStatuses)];
            $vehicleType  = $vehicleTypes[array_rand($vehicleTypes)];

            // Price based on vehicle type
            $price = match ($vehicleType) {
                'executive' => rand(1_200_000,  8_000_000),
                'bus'       => rand(5_000_000, 85_000_000),
                'shuttle'   => rand(3_000_000, 20_000_000),
                'long_trip' => rand(8_000_000, 60_000_000),
                default     => rand(500_000,    5_000_000),
            };

            $bookingNum = 'GB-' . $pickupDate->format('Y') . '-' . str_pad($counter, 7, '0', STR_PAD_LEFT);

            $pickupHour      = rand(5, 20);
            $pickupDatetime  = $pickupDate->copy()->setHour($pickupHour)->setMinute(rand(0, 59));
            $dropoffDatetime = $pickupDatetime->copy()->addDays($duration)->addHours(rand(1, 8));

            $rows[] = [
                'booking_number'   => $bookingNum,
                'client_id'        => $clientIds[array_rand($clientIds)],
                'vehicle_id'       => $vehicleIds[array_rand($vehicleIds)],
                'sales_id'         => $userIds[array_rand($userIds)],
                'created_by'       => $userIds[array_rand($userIds)],
                'status'           => $status,
                'vehicle_type'     => $vehicleType,
                'pickup_datetime'  => $pickupDatetime->toDateTimeString(),
                'dropoff_datetime' => $dropoffDatetime->toDateTimeString(),
                'destination'      => $this->dropoffLocations[array_rand($this->dropoffLocations)],
                'price'            => $price,
                'notes'            => 'Demo booking — ' . ucfirst($vehicleType) . ' service.',
                'created_at'       => $now,
                'updated_at'       => $now,
            ];

            $counter++;

            if (count($rows) >= $chunkSize) {
                try {
                    DB::table('bookings')->insertOrIgnore($rows);
                    $done += count($rows);
                    if ($done % 50_000 === 0) {
                        $this->command->info("    ... {$done}/{$toCreate} bookings inserted");
                    }
                } catch (\Exception $e) {
                    $this->command->warn("    chunk error: " . $e->getMessage());
                }
                $rows = [];
            }
        }

        if ($rows) {
            DB::table('bookings')->insertOrIgnore($rows);
        }

        $this->command->info('  ✓ Bookings: ' . DB::table('bookings')->count());
    }

    // ── Vouchers ─────────────────────────────────────────────────────────────

    private function seedVouchers(): void
    {
        $target   = 50_000;
        $existing = DB::table('vouchers')->count();
        $toCreate = max(0, $target - $existing);

        if ($toCreate === 0) {
            $this->command->info("  ↳ Vouchers already at {$target}, skipping.");
            return;
        }

        $this->command->info("  → Seeding {$toCreate} e-vouchers...");

        $clientIds  = DB::table('clients')->pluck('id')->toArray();
        $userIds    = DB::table('users')->pluck('id')->toArray();
        if (empty($clientIds)) $clientIds = [1];
        if (empty($userIds))   $userIds   = [1];

        // Voucher model: voucher_code, client_id, product_id, title, denomination,
        //                purchase_price, valid_from, valid_until, status,
        //                used_at, used_by_booking_id, issued_by, notes
        $voucherStatuses = ['available','available','available','available','used','used','used','expired','expired'];
        $rows      = [];
        $now       = now()->toDateTimeString();
        $chunkSize = 1000;
        $codeTrack = [];

        for ($i = 0; $i < $toCreate; $i++) {
            $status      = $voucherStatuses[array_rand($voucherStatuses)];
            $validFrom   = Carbon::create(2024, 1, 1)->addDays(rand(0, 500));
            $validUntil  = $validFrom->copy()->addMonths(rand(3, 18));
            $denomination = rand(5, 100) * 100_000; // 500k–10M

            $code = $this->randomVoucherCode($codeTrack);

            $rows[] = [
                'voucher_code'   => $code,
                'client_id'      => $clientIds[array_rand($clientIds)],
                'product_id'     => null,
                'title'          => $this->voucherTitles[array_rand($this->voucherTitles)],
                'denomination'   => $denomination,
                'purchase_price' => (int) ($denomination * 0.9), // 10% discount
                'valid_from'     => $validFrom->toDateString(),
                'valid_until'    => $validUntil->toDateString(),
                'status'         => $status,
                'used_at'        => $status === 'used' ? $validFrom->copy()->addDays(rand(1, 60))->toDateTimeString() : null,
                'used_by_booking_id' => null,
                'issued_by'      => $userIds[array_rand($userIds)],
                'notes'          => 'Demo voucher — issued for corporate transport.',
                'created_at'     => $now,
                'updated_at'     => $now,
            ];

            if (count($rows) >= $chunkSize) {
                try {
                    DB::table('vouchers')->insertOrIgnore($rows);
                } catch (\Exception $e) {
                    $this->command->warn("    voucher chunk error: " . $e->getMessage());
                }
                $rows = [];
            }
        }

        if ($rows) {
            DB::table('vouchers')->insertOrIgnore($rows);
        }

        $this->command->info('  ✓ Vouchers: ' . DB::table('vouchers')->count());
    }

    private function randomVoucherCode(array &$track): string
    {
        $attempts = 0;
        do {
            $code = 'GBV-' . strtoupper(substr(md5(uniqid('', true)), 0, 4))
                  . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 4));
            $attempts++;
        } while (isset($track[$code]) && $attempts < 10);
        $track[$code] = true;
        return $code;
    }

    // ── KPI Targets ──────────────────────────────────────────────────────────

    private function seedKpiTargets(): void
    {
        $this->command->info('  → Seeding KPI targets (all sales, 2024–2026)...');

        $salesUsers = DB::table('users')
            ->whereIn('role', ['sales', 'manager'])
            ->pluck('id')
            ->toArray();

        if (empty($salesUsers)) {
            $this->command->warn('  ↳ No sales users found, skipping KPI.');
            return;
        }

        $rows = [];
        $now  = now()->toDateTimeString();

        foreach ($salesUsers as $userId) {
            for ($year = 2024; $year <= 2026; $year++) {
                for ($month = 1; $month <= 12; $month++) {
                    // Skip future months with low probability of having actuals
                    $periodDate = Carbon::create($year, $month, 1);
                    $isPast     = $periodDate->isPast();

                    $targetRevenue  = rand(200, 800) * 1_000_000;
                    $targetDeals    = rand(15, 50);

                    // Actual: realistic if past, 0 if future
                    $ratio          = $isPast ? (rand(60, 130) / 100) : 0;
                    $actualRevenue  = (int) ($targetRevenue * $ratio);
                    $actualDeals    = $isPast ? (int) ($targetDeals * $ratio) : 0;

                    $existing = DB::table('sales_targets')
                        ->where('user_id', $userId)
                        ->where('period_year', $year)
                        ->where('period_month', $month)
                        ->exists();

                    if (! $existing) {
                        $rows[] = [
                            'user_id'        => $userId,
                            'period_year'    => $year,
                            'period_month'   => $month,
                            'target_revenue' => $targetRevenue,
                            'actual_revenue' => $actualRevenue,
                            'target_deals'   => $targetDeals,
                            'actual_deals'   => $actualDeals,
                            'created_at'     => $now,
                            'updated_at'     => $now,
                        ];
                    }

                    if (count($rows) >= 500) {
                        DB::table('sales_targets')->insertOrIgnore($rows);
                        $rows = [];
                    }
                }
            }
        }

        if ($rows) {
            DB::table('sales_targets')->insertOrIgnore($rows);
        }

        $this->command->info('  ✓ KPI targets: ' . DB::table('sales_targets')->count());
    }

    // ── Summary ──────────────────────────────────────────────────────────────

    private function printSummary(): void
    {
        $this->command->table(
            ['Table', 'Count'],
            [
                ['vehicles',     number_format(DB::table('vehicles')->count())],
                ['bookings',     number_format(DB::table('bookings')->count())],
                ['vouchers',     number_format(DB::table('vouchers')->count())],
                ['sales_targets',number_format(DB::table('sales_targets')->count())],
                ['pools',        number_format(DB::table('pools')->count())],
            ]
        );
    }
}
