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
            $table->softDeletes();

            $table->unsignedBigInteger('created_by')->nullable()->index('idx_created_by');
            $table->unsignedBigInteger('updated_by')->nullable()->index('idx_updated_by');
            $table->unsignedBigInteger('deleted_by')->nullable()->index('idx_deleted_by');

            $table->foreign('created_by','users_fk_001')->references('id')->on('users')->constrained()->onDelete('cascade');
            $table->foreign('updated_by','users_fk_002')->references('id')->on('users')->constrained()->onDelete('cascade');
            $table->foreign('deleted_by','users_fk_003')->references('id')->on('users')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
    }
};
