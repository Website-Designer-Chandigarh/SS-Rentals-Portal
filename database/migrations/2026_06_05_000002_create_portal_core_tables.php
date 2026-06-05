<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('asset_type');
            $table->string('rego')->index();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->default('available')->index();
            $table->decimal('odometer', 12, 2)->default(0);
            $table->decimal('value', 12, 2)->default(0);
            $table->date('cof_expiry')->nullable();
            $table->date('rego_expiry')->nullable();
            $table->decimal('service_due_km', 12, 2)->default(0);
            $table->date('next_service')->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->decimal('ruc_balance', 12, 2)->default(0);
            $table->string('hirer_id')->nullable()->index();
            $table->date('hire_start')->nullable();
            $table->decimal('weekly_rate', 12, 2)->default(0);
            $table->string('location')->nullable();
            $table->decimal('weekly_km', 12, 2)->default(0);
            $table->decimal('rev_ytd', 12, 2)->default(0);
            $table->decimal('maint_ytd', 12, 2)->default(0);
            $table->unsignedInteger('downtime')->default(0);
            $table->decimal('instalment', 12, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('company')->index();
            $table->string('contact')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('nzbn')->nullable();
            $table->string('status')->default('active')->index();
            $table->string('truck_id')->nullable();
            $table->string('trailer_id')->nullable();
            $table->decimal('weekly_truck', 12, 2)->default(0);
            $table->decimal('weekly_trailer', 12, 2)->default(0);
            $table->decimal('mileage_rate', 8, 4)->default(0);
            $table->decimal('ruc_rate', 8, 4)->default(0);
            $table->decimal('outstanding', 12, 2)->default(0);
            $table->string('payment_terms')->nullable();
            $table->date('joined')->nullable();
            $table->decimal('ytd_revenue', 12, 2)->default(0);
            $table->string('credit_rating')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('hires', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('customer_id')->index();
            $table->string('truck_id')->nullable()->index();
            $table->string('trailer_id')->nullable()->index();
            $table->date('start')->nullable();
            $table->date('end')->nullable();
            $table->string('status')->default('active')->index();
            $table->decimal('weekly_truck', 12, 2)->default(0);
            $table->decimal('weekly_trailer', 12, 2)->default(0);
            $table->decimal('mileage_rate', 8, 4)->default(0);
            $table->decimal('ruc_rate', 8, 4)->default(0);
            $table->decimal('bond', 12, 2)->default(0);
            $table->boolean('bond_paid')->default(false);
            $table->date('invoiced_to')->nullable();
            $table->date('next_invoice')->nullable();
            $table->boolean('signed')->default(false);
            $table->boolean('insurance_verified')->default(false);
            $table->boolean('checklist_done')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('customer_id')->nullable()->index();
            $table->string('hire_id')->nullable()->index();
            $table->date('date')->nullable();
            $table->date('due')->nullable();
            $table->string('period')->nullable();
            $table->decimal('truck_hire', 12, 2)->default(0);
            $table->decimal('trailer_hire', 12, 2)->default(0);
            $table->decimal('mileage', 12, 2)->default(0);
            $table->decimal('ruc', 12, 2)->default(0);
            $table->decimal('damage', 12, 2)->default(0);
            $table->decimal('extras', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('status')->default('draft')->index();
            $table->string('xero_id')->nullable();
            $table->timestamps();
        });

        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('vehicle_id')->nullable()->index();
            $table->string('rego')->nullable();
            $table->string('type')->nullable();
            $table->text('description')->nullable();
            $table->date('date')->nullable();
            $table->decimal('cost', 12, 2)->default(0);
            $table->string('status')->default('scheduled')->index();
            $table->string('workshop')->nullable();
            $table->string('parts')->nullable();
            $table->decimal('odometer', 12, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('type')->nullable()->index();
            $table->string('customer_id')->nullable()->index();
            $table->string('vehicle_id')->nullable()->index();
            $table->date('date')->nullable();
            $table->string('size')->nullable();
            $table->boolean('signed')->default(false);
            $table->timestamps();
        });

        Schema::create('weekly_revenues', function (Blueprint $table) {
            $table->id();
            $table->string('week');
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('monthly_revenues', function (Blueprint $table) {
            $table->id();
            $table->string('month')->unique();
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('expenses', 12, 2)->default(0);
            $table->decimal('net', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('pnl_details', function (Blueprint $table) {
            $table->id();
            $table->string('month')->unique();
            foreach (['revenue', 'insurance', 'navman_ruc', 'ruc', 'other', 'repairs', 'flexi', 'heartland', 'advertising', 'gst', 'expenses', 'net'] as $column) {
                $table->decimal($column, 12, 2)->default(0);
            }
            $table->timestamps();
        });

        Schema::create('app_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->json('payload');
            $table->timestamps();
        });

        $this->seedInitialData();
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
        Schema::dropIfExists('pnl_details');
        Schema::dropIfExists('monthly_revenues');
        Schema::dropIfExists('weekly_revenues');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('maintenance_records');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('hires');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('vehicles');
    }

    private function seedInitialData(): void
    {
        $data = json_decode(file_get_contents(resource_path('data/portal-data.json')), true);

        if (Schema::hasTable('portal_data')) {
            $portalData = DB::table('portal_data')->pluck('payload', 'key');
            foreach ($portalData as $key => $payload) {
                $data[$key] = json_decode($payload, true) ?: ($data[$key] ?? []);
            }
        }

        foreach ($data['trucks'] ?? [] as $row) {
            DB::table('vehicles')->updateOrInsert(['id' => $row['id']], $this->vehicleRow($row, 'truck'));
        }

        foreach ($data['trailers'] ?? [] as $row) {
            DB::table('vehicles')->updateOrInsert(['id' => $row['id']], $this->vehicleRow($row, 'trailer'));
        }

        foreach ($data['customers'] ?? [] as $row) {
            DB::table('customers')->updateOrInsert(['id' => $row['id']], $this->customerRow($row));
        }

        foreach ($data['hires'] ?? [] as $row) {
            DB::table('hires')->updateOrInsert(['id' => $row['id']], $this->hireRow($row));
        }

        foreach ($data['invoices'] ?? [] as $row) {
            DB::table('invoices')->updateOrInsert(['id' => $row['id']], $this->invoiceRow($row));
        }

        foreach ($data['maintenance'] ?? [] as $row) {
            DB::table('maintenance_records')->updateOrInsert(['id' => $row['id']], $this->maintenanceRow($row));
        }

        foreach ($data['documents'] ?? [] as $row) {
            DB::table('documents')->updateOrInsert(['id' => $row['id']], $this->documentRow($row));
        }

        foreach ($data['weekly_revenue'] ?? [] as $row) {
            DB::table('weekly_revenues')->insert(array_merge($row, $this->timestamps()));
        }

        foreach ($data['monthly_revenue'] ?? [] as $row) {
            DB::table('monthly_revenues')->updateOrInsert(['month' => $row['month']], array_merge($row, $this->timestamps()));
        }

        foreach ($data['pnl_detail'] ?? [] as $row) {
            DB::table('pnl_details')->updateOrInsert(['month' => $row['month']], array_merge($row, $this->timestamps()));
        }

        DB::table('app_settings')->updateOrInsert(
            ['key' => 'portal'],
            ['payload' => json_encode($data['settings'] ?? []), 'created_at' => now(), 'updated_at' => now()],
        );
    }

    private function vehicleRow(array $row, string $assetType): array
    {
        return array_merge([
            'asset_type' => $assetType,
            'hirer_id' => $row['hirer'] ?? null,
        ], $this->only($row, [
            'rego', 'make', 'model', 'year', 'type', 'status', 'odometer', 'value', 'cof_expiry', 'rego_expiry',
            'service_due_km', 'next_service', 'insurance_expiry', 'ruc_balance', 'hire_start', 'weekly_rate',
            'location', 'weekly_km', 'rev_ytd', 'maint_ytd', 'downtime', 'instalment', 'note',
        ]), $this->timestamps());
    }

    private function customerRow(array $row): array
    {
        return array_merge([
            'truck_id' => $row['truck'] ?? null,
            'trailer_id' => $row['trailer'] ?? null,
        ], $this->only($row, [
            'company', 'contact', 'phone', 'email', 'address', 'nzbn', 'status', 'weekly_truck', 'weekly_trailer',
            'mileage_rate', 'ruc_rate', 'outstanding', 'payment_terms', 'joined', 'ytd_revenue', 'credit_rating', 'notes',
        ]), $this->timestamps());
    }

    private function hireRow(array $row): array
    {
        return array_merge([
            'customer_id' => $row['customer'] ?? null,
            'truck_id' => $row['truck'] ?? null,
            'trailer_id' => $row['trailer'] ?? null,
        ], $this->only($row, [
            'start', 'end', 'status', 'weekly_truck', 'weekly_trailer', 'mileage_rate', 'ruc_rate', 'bond', 'bond_paid',
            'invoiced_to', 'next_invoice', 'signed', 'insurance_verified', 'checklist_done', 'notes',
        ]), $this->timestamps());
    }

    private function invoiceRow(array $row): array
    {
        return array_merge([
            'customer_id' => $row['customer'] ?? null,
            'hire_id' => $row['hire'] ?? null,
        ], $this->only($row, [
            'date', 'due', 'period', 'truck_hire', 'trailer_hire', 'mileage', 'ruc', 'damage', 'extras', 'total', 'status', 'xero_id',
        ]), $this->timestamps());
    }

    private function maintenanceRow(array $row): array
    {
        return array_merge([
            'vehicle_id' => $row['vehicle'] ?? null,
        ], $this->only($row, [
            'rego', 'type', 'description', 'date', 'cost', 'status', 'workshop', 'parts', 'odometer',
        ]), $this->timestamps());
    }

    private function documentRow(array $row): array
    {
        return array_merge([
            'customer_id' => $row['customer'] ?? null,
            'vehicle_id' => $row['vehicle'] ?? null,
        ], $this->only($row, [
            'name', 'type', 'date', 'size', 'signed',
        ]), $this->timestamps());
    }

    private function only(array $row, array $keys): array
    {
        return array_intersect_key($row, array_flip($keys));
    }

    private function timestamps(): array
    {
        return ['created_at' => now(), 'updated_at' => now()];
    }
};
