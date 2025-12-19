<?php
/**
 * Script untuk membuat admin user baru dengan bcrypt Laravel
 * Jalankan: php create_admin.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;

try {
    $adminId = Uuid::uuid4()->toString();
    $email = 'admin@roastmaster.com';
    $password = Hash::make('R0sty;4man'); // Bcrypt
    $now = now()->toIso8601String();
    
    // Cek apakah admin sudah ada
    $existing = DB::table('auth.users')->where('email', $email)->first();
    
    if ($existing) {
        echo "❌ Admin dengan email {$email} sudah ada!\n";
        echo "ID: {$existing->id}\n\n";
        echo "Update password? (y/n): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        
        if (trim($line) === 'y') {
            // Update password
            DB::table('auth.users')
                ->where('email', $email)
                ->update([
                    'encrypted_password' => $password,
                    'updated_at' => $now
                ]);
            
            echo "✅ Password admin berhasil diupdate dengan bcrypt!\n";
            echo "Email: {$email}\n";
            echo "Password: R0sty;4man\n";
            
            // Update/create profile
            DB::table('profiles')->updateOrInsert(
                ['id' => $existing->id],
                [
                    'id' => $existing->id,
                    'email' => $email,
                    'role' => 'admin',
                    'created_at' => $now,
                    'updated_at' => $now
                ]
            );
            
            echo "✅ Profile admin berhasil diupdate!\n";
        }
        exit;
    }
    
    // Insert admin baru
    DB::table('auth.users')->insert([
        'id' => $adminId,
        'email' => $email,
        'encrypted_password' => $password,
        'email_confirmed_at' => $now,
        'raw_user_meta_data' => json_encode(['name' => 'Admin Roastmaster']),
        'created_at' => $now,
        'updated_at' => $now,
        'last_sign_in_at' => null,
        'role' => 'authenticated',
        'aud' => 'authenticated'
    ]);
    
    echo "✅ Admin user berhasil dibuat!\n";
    echo "ID: {$adminId}\n";
    echo "Email: {$email}\n";
    echo "Password: R0sty;4man\n\n";
    
    // Create profile
    DB::table('profiles')->insert([
        'id' => $adminId,
        'email' => $email,
        'role' => 'admin',
        'created_at' => $now,
        'updated_at' => $now
    ]);
    
    echo "✅ Profile admin berhasil dibuat!\n";
    echo "\n🚀 Sekarang bisa login di Postman!\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
