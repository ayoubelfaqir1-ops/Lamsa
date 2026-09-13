<?php

namespace Modules\Auth\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Modules\Auth\Models\User;
use Spatie\Permission\Models\Role;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:create-admin {--name=} {--email=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new admin user interactively';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('==========================================');
        $this->info('   Create New Admin Account - Lamsa API   ');
        $this->info('==========================================');

        $name = $this->option('name') ?: $this->ask('Enter Admin Full Name', 'Admin User');
        $email = $this->option('email') ?: $this->ask('Enter Admin Email Address');

        $validator = Validator::make(['email' => $email], [
            'email' => ['required', 'email'],
        ]);

        if ($validator->fails()) {
            $this->error('Invalid email address provided.');
            return Command::FAILURE;
        }

        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            if (! $this->confirm("⚠️  A user with email [{$email}] already exists! Do you want to reset their password and elevate them to admin?")) {
                $this->warn('Admin creation cancelled.');
                return Command::SUCCESS;
            }
        }

        $password = $this->secret('Enter Admin Password');
        $confirmPassword = $this->secret('Confirm Admin Password');

        if ($password !== $confirmPassword) {
            $this->error('Passwords do not match.');
            return Command::FAILURE;
        }

        $passwordValidator = Validator::make(['password' => $password], [
            'password' => [Password::defaults()],
        ]);

        if ($passwordValidator->fails()) {
            foreach ($passwordValidator->errors()->all() as $error) {
                $this->error($error);
            }
            return Command::FAILURE;
        }

        Role::firstOrCreate(['name' => 'admin']);

        $admin = User::firstOrNew(['email' => $email]);
        $admin->name = $name;
        $admin->password = Hash::make($password);
        $admin->email_verified_at = now();
        $admin->save();

        $admin->assignRole('admin');

        Log::warning("Admin account [{$admin->email}] (ID: {$admin->id}) created/elevated via Artisan CLI command.");

        $this->newLine();
        $this->info("✅ Admin account [{$admin->email}] created successfully!");
        $this->info("User ID: {$admin->id} | Role: admin");

        return Command::SUCCESS;
    }
}
