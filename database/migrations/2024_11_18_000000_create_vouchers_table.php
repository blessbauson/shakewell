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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();

            $table->timestamps();
            $table->softDeletes();
            
            $table->unsignedBigInteger('user_id')->nullable()->index('idx_user_id');
            $table->unsignedBigInteger('created_by')->nullable()->index('idx_created_by');
            $table->unsignedBigInteger('updated_by')->nullable()->index('idx_updated_by');
            $table->unsignedBigInteger('deleted_by')->nullable()->index('idx_deleted_by');

            $table->foreign('user_id','vouchers_users_fk_000')->references('id')->on('users')->constrained()->onDelete('cascade');
            $table->foreign('created_by','vouchers_users_fk_001')->references('id')->on('users')->constrained()->onDelete('cascade');
            $table->foreign('updated_by','vouchers_users_fk_002')->references('id')->on('users')->constrained()->onDelete('cascade');
            $table->foreign('deleted_by','vouchers_users_fk_003')->references('id')->on('users')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};