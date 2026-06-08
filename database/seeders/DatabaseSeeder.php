<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Client;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\Pool;
use App\Models\MaintenanceLog;
use App\Models\MeetingLog;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('email', 'gm@goldenbird.co.id')->exists()) {
            $this->command?->info('Base demo data already exists, skipping core seed.');
            $this->call(DemoMassiveSeeder::class);
            return;
        }

        // ==================== USERS: 1 GM + 5 MANAGER × 3 SALES + OPS + FINANCE ====================
        // Hierarki: GM (pucuk pimpinan) → 5 Sales Manager → masing-masing 3 Sales Representative.
        // Director DIHAPUS — wewenangnya digabung ke GM.
        $gm = User::create([
            'name' => 'Budi Santoso', 'email' => 'gm@goldenbird.co.id',
            'password' => bcrypt('password123'), 'role' => 'gm',
        ]);

        // 5 Sales Manager + nama 3 sales untuk masing-masing tim.
        $teams = [
            ['manager' => 'Ratna Dewi',      'email' => 'manager1@goldenbird.co.id', 'sales' => ['Andi Pratama', 'Sari Dewi', 'Reza Firmansyah']],
            ['manager' => 'Bambang Wibowo',  'email' => 'manager2@goldenbird.co.id', 'sales' => ['Dewi Lestari', 'Fajar Nugroho', 'Gita Permata']],
            ['manager' => 'Citra Anggraini', 'email' => 'manager3@goldenbird.co.id', 'sales' => ['Hadi Saputra', 'Indah Sari', 'Joko Prabowo']],
            ['manager' => 'Dimas Prasetyo',  'email' => 'manager4@goldenbird.co.id', 'sales' => ['Kartika Maharani', 'Lukman Hakim', 'Mira Susanti']],
            ['manager' => 'Eka Wahyuni',     'email' => 'manager5@goldenbird.co.id', 'sales' => ['Nanda Pratiwi', 'Oscar Tanjung', 'Putri Rahayu']],
        ];

        $salesUsers = [];     // semua sales representative
        $managerUsers = [];    // semua manager
        $salesSeq = 1;
        foreach ($teams as $tIdx => $team) {
            $manager = User::create([
                'name' => $team['manager'], 'email' => $team['email'],
                'password' => bcrypt('password123'), 'role' => 'manager',
                'manager_id' => $gm->id,
            ]);
            $managerUsers[] = $manager;

            foreach ($team['sales'] as $sIdx => $salesName) {
                $level = ['junior', 'senior', 'key_account'][$sIdx] ?? 'junior';
                $salesUsers[] = User::create([
                    'name' => $salesName,
                    'email' => 'sales' . $salesSeq . '@goldenbird.co.id',
                    'password' => bcrypt('password123'), 'role' => 'sales',
                    'manager_id' => $manager->id, 'sales_level' => $level,
                ]);
                $salesSeq++;
            }
        }

        $ops     = User::create(['name' => 'Hendra Wijaya', 'email' => 'ops@goldenbird.co.id', 'password' => bcrypt('password123'), 'role' => 'operational']);
        $finance = User::create(['name' => 'Maya Kusuma', 'email' => 'finance@goldenbird.co.id', 'password' => bcrypt('password123'), 'role' => 'finance']);

        // ID sales dinamis untuk dipakai blok di bawah (client/booking assignment).
        $sales_ids = collect($salesUsers)->pluck('id')->all();

        // ==================== 3 POOLS ====================
        Pool::create(['name' => 'Pool Jakarta', 'location' => 'Tanjung Priok, Jakarta', 'capacity' => 15, 'notes' => 'Pool utama Jakarta']);
        Pool::create(['name' => 'Pool Bandung', 'location' => 'Bandung, Jawa Barat', 'capacity' => 10, 'notes' => 'Pool cabang Bandung']);
        Pool::create(['name' => 'Pool Surabaya', 'location' => 'Surabaya, Jawa Timur', 'capacity' => 10, 'notes' => 'Pool cabang Surabaya']);

        // ==================== 30 CLIENTS ====================
        $companies = [
            ['PT Unilever Indonesia', 'FMCG', 'Jl. Layang Layang, Jakarta'],
            ['PT Bank Central Asia', 'Banking', 'Jl. MH Thamrin, Jakarta'],
            ['PT Tokopedia', 'Technology', 'Jl. Kemang Raya, Jakarta'],
            ['PT Freeport Indonesia', 'Mining', 'Papua'],
            ['PT Pertamina', 'Oil & Gas', 'Jl. Merdeka Barat, Jakarta'],
            ['PT Indomaret', 'Retail', 'Jl. Let Jend Soeprapto, Jakarta'],
            ['Hotel Indonesia Kempinski', 'Hospitality', 'Jl. MH Thamrin, Jakarta'],
            ['RS Siloam', 'Healthcare', 'Jl. Gatot Subroto, Jakarta'],
            ['PT Telkomsel', 'Telco', 'Jl. Jend Sudirman, Jakarta'],
            ['Kementerian BUMN', 'Government', 'Jakarta'],
            ['PT Astra International', 'Automotive', 'Jl. Gatot Subroto, Jakarta'],
            ['PT Bank Mandiri', 'Banking', 'Jakarta'],
            ['PT Indofood', 'FMCG', 'Jakarta'],
            ['PT Garuda Indonesia', 'Aviation', 'Jakarta'],
            ['PT PLN', 'Energy', 'Jakarta'],
            ['PT XL Axiata', 'Telco', 'Jakarta'],
            ['PT Samsung Indonesia', 'Electronics', 'Jakarta'],
            ['PT Toyota Astra', 'Automotive', 'Jakarta'],
            ['PT Jasa Marga', 'Infrastructure', 'Jakarta'],
            ['PT Bukalapak', 'E-Commerce', 'Jakarta'],
            ['PT Gojek', 'Technology', 'Jakarta'],
            ['PT Bank BNI', 'Banking', 'Jakarta'],
            ['PT Krakatau Steel', 'Steel', 'Serang'],
            ['PT Indika Energy', 'Energy', 'Jakarta'],
            ['PT Sinar Mas', 'Conglomerate', 'Jakarta'],
            ['PT Wings Group', 'FMCG', 'Magelang'],
            ['PT Kalbe Farma', 'Pharmaceutical', 'Jakarta'],
            ['PT Matahari Department', 'Retail', 'Jakarta'],
            ['PT Citilink', 'Aviation', 'Jakarta'],
            ['PT Bank BRI', 'Banking', 'Jakarta'],
        ];

        // $sales_ids sudah di-set dinamis di blok USERS (15 sales).
        $salesCount = count($sales_ids);
        foreach ($companies as $idx => $company) {
            Client::create([
                'company_name' => $company[0],
                'pic_name' => 'Contact ' . ($idx + 1),
                'phone' => '021' . random_int(10000000, 99999999),
                'email' => strtolower(str_replace(' ', '.', $company[0])) . '@company.id',
                'address' => $company[2],
                'industry' => $company[1],
                'status' => 'active',
                'assigned_sales_id' => $sales_ids[$idx % $salesCount],
                'notes' => 'Client ' . ($idx + 1),
            ]);
        }

        // ==================== 20 VEHICLES ====================
        $brands = [
            ['bigbird', 'Coach Bus Big', 45],
            ['bigbird', 'Coach Bus', 45],
            ['bigbird', 'Executive Coach', 35],
            ['bigbird', 'Luxury Coach', 35],
            ['bigbird', 'Standard Coach', 40],
            ['goldenbird', 'Premium Sedan', 6],
            ['goldenbird', 'Executive Sedan', 6],
            ['goldenbird', 'Luxury Sedan', 4],
            ['goldenbird', 'VIP Sedan', 4],
            ['goldenbird', 'Business Sedan', 6],
            ['cititrans', 'Executive Bus', 25],
            ['cititrans', 'Shuttle Bus', 20],
            ['cititrans', 'Commuter Bus', 25],
            ['cititrans', 'City Bus', 25],
            ['cititrans', 'Standard Bus', 25],
            ['executive', 'SUV Premium', 8],
            ['executive', 'Executive SUV', 8],
            ['executive', 'Luxury SUV', 6],
            ['executive', 'Business SUV', 8],
            ['executive', 'VIP SUV', 6],
        ];

        $pool_ids = [1, 2, 3];
        foreach ($brands as $idx => $brand) {
            Vehicle::create([
                'plate_number' => 'BB ' . str_pad($idx + 1, 4, '0', STR_PAD_LEFT) . ' XX',
                'brand' => $brand[0],
                'model' => $brand[1],
                'capacity' => $brand[2],
                'year' => 2024 - ($idx % 3),
                'status' => 'available',
                'pool_id' => $pool_ids[$idx % 3],
                'notes' => 'Vehicle ' . ($idx + 1),
            ]);
        }

        // ==================== 15 DRIVERS ====================
        $driver_names = ['Ahmad Suryanto', 'Budi Hartono', 'Citra Wijaya', 'Dedi Kusuma', 'Eka Putri', 
                        'Farah Nabila', 'Gunawan Setiawan', 'Haris Gunawan', 'Iwan Pratama', 'Joko Susanto',
                        'Karina Sehati', 'Laris Gunardi', 'Maryanto Wijaya', 'Nuri Azizah', 'Ongki Wijaya'];

        foreach ($driver_names as $idx => $name) {
            Driver::create([
                'name' => $name,
                'phone' => '082' . random_int(1000000000, 9999999999),
                'license_number' => 'SIM' . str_pad($idx + 1, 8, '0', STR_PAD_LEFT),
                'status' => 'available',
                'notes' => 'Driver ' . ($idx + 1),
            ]);
        }

        // ==================== 60 BOOKINGS ====================
        $statuses = ['completed', 'completed', 'completed', 'completed', 'confirmed', 'on_trip', 'cancelled'];
        $destinations = ['Bandung', 'Surabaya', 'Yogyakarta', 'Semarang', 'Medan', 'Makassar', 'Denpasar'];

        for ($i = 0; $i < 60; $i++) {
            $client = Client::inRandomOrder()->first();
            $vehicle = Vehicle::inRandomOrder()->first();
            $driver = Driver::inRandomOrder()->first();
            $sales = User::where('role', 'sales')->inRandomOrder()->first();
            $created_by = $sales;
            
            $pickup = Carbon::now()->subDays(random_int(1, 180))->setHour(random_int(7, 17));
            $dropoff = (clone $pickup)->addHours(random_int(2, 8));

            Booking::create([
                'booking_number' => 'BB-' . $pickup->format('Ymd') . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'client_id' => $client->id,
                'sales_id' => $sales->id,
                'created_by' => $created_by->id,
                'vehicle_id' => $vehicle->id,
                'driver_id' => $driver->id,
                'pickup_datetime' => $pickup,
                'dropoff_datetime' => $dropoff,
                'destination' => $destinations[array_rand($destinations)],
                'vehicle_type' => $vehicle->brand,
                'price' => random_int(500000, 25000000),
                'status' => $statuses[array_rand($statuses)],
                'notes' => 'Booking ' . ($i + 1),
            ]);
        }

        // ==================== 50 INVOICES ====================
        $bookings = Booking::where('status', 'completed')->get();
        foreach ($bookings->take(50) as $booking) {
            Invoice::create([
                'invoice_number' => 'INV-' . Carbon::now()->format('Ymd') . '-' . str_pad($booking->id, 4, '0', STR_PAD_LEFT),
                'booking_id' => $booking->id,
                'client_id' => $booking->client_id,
                'amount' => $booking->price,
                'status' => ['paid', 'paid', 'paid', 'sent', 'draft'][array_rand(['paid', 'paid', 'paid', 'sent', 'draft'])],
                'due_date' => Carbon::now()->addDays(30),
                'paid_at' => random_int(0, 1) ? Carbon::now() : null,
                'notes' => 'Invoice for booking ' . $booking->booking_number,
            ]);
        }

        // ==================== 40 PAYMENTS ====================
        $invoices = Invoice::where('status', '!=', 'draft')->limit(40)->get();
        foreach ($invoices as $invoice) {
            Payment::create([
                'payment_number' => 'PAY-' . Carbon::now()->format('Ymd') . '-' . str_pad($invoice->id, 4, '0', STR_PAD_LEFT),
                'invoice_id' => $invoice->id,
                'amount' => $invoice->amount,
                'method' => ['transfer', 'cash', 'giro'][array_rand(['transfer', 'cash', 'giro'])],
                'payment_date' => Carbon::now(),
                'notes' => 'Payment for ' . $invoice->invoice_number,
            ]);
        }

        // ==================== 15 PURCHASE ORDERS ====================
        $vendors = ['PT Bengkel Motor', 'PT Spare Parts Mobil', 'PT Oli Kesindo', 'PT Ban Radial', 'PT Listrik Otomotif'];
        for ($i = 0; $i < 15; $i++) {
            PurchaseOrder::create([
                'po_number' => 'PO-' . str_pad($i + 1, 5, '0', STR_PAD_LEFT),
                'vendor' => $vendors[$i % 5],
                'item_description' => 'Vehicle Maintenance Item ' . ($i + 1),
                'amount' => random_int(1000000, 10000000),
                'status' => ['pending', 'approved', 'received'][array_rand(['pending', 'approved', 'received'])],
                'notes' => 'PO ' . ($i + 1),
            ]);
        }

        // ==================== 20 MAINTENANCE LOGS ====================
        $vehicles = Vehicle::all();
        foreach ($vehicles->take(20) as $vehicle) {
            MaintenanceLog::create([
                'vehicle_id' => $vehicle->id,
                'type' => ['routine', 'repair', 'modification'][array_rand(['routine', 'repair', 'modification'])],
                'description' => 'Maintenance for ' . $vehicle->model,
                'cost' => random_int(500000, 5000000),
                'vendor' => 'PT Bengkel Motor',
                'scheduled_date' => Carbon::now(),
                'completed_date' => random_int(0, 1) ? Carbon::now() : null,
                'status' => ['scheduled', 'in_progress', 'completed'][array_rand(['scheduled', 'in_progress', 'completed'])],
                'notes' => 'Maintenance ' . $vehicle->id,
            ]);
        }

        // ==================== 25 MEETING LOGS ====================
        $clients = Client::all();
        foreach ($clients->take(25) as $client) {
            MeetingLog::create([
                'client_id' => $client->id,
                'sales_id' => $client->assigned_sales_id,
                'meeting_date' => Carbon::now()->subDays(random_int(1, 30)),
                'notes' => 'Meeting with ' . $client->company_name,
                'outcome' => 'Discussed services and booking requirements',
                'follow_up_date' => Carbon::now()->addDays(random_int(1, 7)),
                'status' => random_int(0, 1) ? 'done' : 'pending',
            ]);
        }

        // ==================== MASSIVE DEMO DATA ====================
        $this->call(DemoMassiveSeeder::class);

        // ==================== MASSIVE VEHICLE + BOOKING + VOUCHER + KPI ====================
        $this->call(MassiveVehicleBookingSeeder::class);
    }
}
