<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kecamatan', function (Blueprint $table) {
            if (!Schema::hasColumn('kecamatan', 'token')) {
                $table->string('token', 100)->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('kecamatan', 'whatsapp')) {
                $table->json('whatsapp')->nullable()->after('token');
            }
        });

        if (!Schema::hasTable('whatsapp')) {
            Schema::create('whatsapp', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lokasi')->index();
                $table->string('nama', 100)->nullable();
                $table->string('token', 100)->nullable();
                $table->string('device_id', 50)->nullable();
                $table->string('device_key', 100)->nullable();
                $table->string('status', 20)->default('disconnected');
                $table->string('phone_number', 30)->nullable();
                $table->timestamp('last_seen')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique('lokasi');
            });
        } else {
            Schema::table('whatsapp', function (Blueprint $table) {
                if (!Schema::hasColumn('whatsapp', 'phone_number')) {
                    $table->string('phone_number', 30)->nullable()->after('status');
                }
                if (!Schema::hasColumn('whatsapp', 'last_seen')) {
                    $table->timestamp('last_seen')->nullable()->after('phone_number');
                }
                if (!Schema::hasColumn('whatsapp', 'created_at')) {
                    $table->timestamp('created_at')->nullable()->after('last_seen');
                }
                if (!Schema::hasColumn('whatsapp', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable()->after('created_at');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp');

        Schema::table('kecamatan', function (Blueprint $table) {
            if (Schema::hasColumn('kecamatan', 'whatsapp')) {
                $table->dropColumn('whatsapp');
            }
            if (Schema::hasColumn('kecamatan', 'token')) {
                $table->dropColumn('token');
            }
        });
    }
};
