<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Changes model_id from bigint to uuid(36) for UUID support
     */
    public function up(): void
    {
        // Modify model_has_permissions table
        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->dropIndex('model_has_permissions_model_id_model_type_index');
        });

        DB::statement('ALTER TABLE model_has_permissions ALTER COLUMN model_id TYPE varchar(36)');

        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');
        });

        // Modify model_has_roles table
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropIndex('model_has_roles_model_id_model_type_index');
        });

        DB::statement('ALTER TABLE model_has_roles ALTER COLUMN model_id TYPE varchar(36)');

        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert model_has_permissions
        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->dropIndex('model_has_permissions_model_id_model_type_index');
        });

        DB::statement('ALTER TABLE model_has_permissions ALTER COLUMN model_id TYPE bigint USING model_id::bigint');

        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');
        });

        // Revert model_has_roles
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropIndex('model_has_roles_model_id_model_type_index');
        });

        DB::statement('ALTER TABLE model_has_roles ALTER COLUMN model_id TYPE bigint USING model_id::bigint');

        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');
        });
    }
};
