<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->string('email')->nullable()->after('organization_id');
            $table->string('role')->default('member')->after('email');
            $table->timestamp('expires_at')->nullable()->after('status');
            $table->timestamp('accepted_at')->nullable()->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn(['email', 'role', 'expires_at', 'accepted_at']);
        });
    }
};
