
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('code', 20)->unique()->after('id');

            $table->string('name', 150)->after('code');

            $table->enum('type', [
                'asset',
                'liability',
                'equity',
                'revenue',
                'expense',
            ])->after('name');

            $table->enum('normal_balance', [
                'debit',
                'credit',
            ])->after('type');

            $table->foreignId('parent_id')
                ->nullable()
                ->after('normal_balance')
                ->constrained('accounts')
                ->nullOnDelete();

            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);

            $table->boolean('is_system')->default(false);

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropIndex(['type']);

            $table->dropConstrainedForeignId('parent_id');

            $table->dropColumn([
                'code',
                'name',
                'type',
                'normal_balance',
                'description',
                'is_active',
                'is_system',
            ]);
        });
    }
};
