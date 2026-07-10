<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hires', function (Blueprint $table) {
            $table->string('charge_type')->default('weekly')->after('trailer_id');
            $table->decimal('monthly_rate', 12, 2)->default(0)->after('weekly_trailer');
            $table->decimal('eroad_rate', 12, 2)->default(0)->after('monthly_rate');
            $table->string('max_mileage')->nullable()->after('ruc_rate');
            $table->string('payment_method')->nullable()->after('max_mileage');
            $table->string('truck_vin')->nullable()->after('payment_method');
            $table->string('truck_colour')->nullable()->after('truck_vin');
            $table->string('trailer_colour')->nullable()->after('truck_colour');
            $table->string('guarantor_name')->nullable()->after('trailer_colour');
            $table->string('guarantor_address')->nullable()->after('guarantor_name');
            $table->string('guarantor_phone')->nullable()->after('guarantor_address');
        });
    }

    public function down(): void
    {
        Schema::table('hires', function (Blueprint $table) {
            $table->dropColumn([
                'charge_type', 'monthly_rate', 'eroad_rate', 'max_mileage', 'payment_method',
                'truck_vin', 'truck_colour', 'trailer_colour', 'guarantor_name', 'guarantor_address', 'guarantor_phone',
            ]);
        });
    }
};
