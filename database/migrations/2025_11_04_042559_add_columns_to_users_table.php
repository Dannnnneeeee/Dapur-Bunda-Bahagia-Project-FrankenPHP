<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('tempat_lahir')->nullable()->after('password');
            $table->date('tgl_lahir')->nullable()->after('tempat_lahir');
            $table->string('no_telp', 20)->nullable()->after('tgl_lahir');
            $table->enum('gender', ['L', 'P'])->nullable()->after('no_telp');
            $table->string('foto')->nullable()->after('gender');


            $table->text('alamat')->nullable()->after('foto');
            $table->string('kota')->nullable()->after('alamat');
            $table->string('provinsi')->nullable()->after('kota');
            $table->string('kode_pos', 10)->nullable()->after('provinsi');

            // Status & Activity
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('kode_pos');
            $table->timestamp('last_login_at')->nullable()->after('status');

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'tempat_lahir',
                'tgl_lahir',
                'no_telp',
                'gender',
                'foto',
                'alamat',
                'kota',
                'provinsi',
                'kode_pos',
                'status',
                'last_login_at',
            ]);
        });
    }
};
