<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('portal_access_token', 64)->nullable()->unique()->after('email');
            $table->timestamp('portal_token_created_at')->nullable()->after('portal_access_token');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['portal_access_token', 'portal_token_created_at']);
        });
    }
};
