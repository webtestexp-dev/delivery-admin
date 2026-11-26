<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateStaffUser extends Command
{
    protected $signature = 'staff:create';

    protected $description = 'Create an admin or delivery-person account';

    public function handle(): int
    {
        $name = trim((string) $this->ask('Name'));
        $email = strtolower(trim((string) $this->ask('Email')));
        $role = $this->choice('Role', ['admin', 'delivery_person'], 'admin');
        $password = (string) $this->secret('Password (minimum 12 characters)');
        $confirmation = (string) $this->secret('Confirm password');

        $validator = Validator::make(
            compact('name', 'email', 'password', 'confirmation'),
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', Password::min(12)],
                'confirmation' => ['same:password'],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create(['name' => $name, 'email' => $email, 'password' => $password]);
        $user->role = $role;
        $user->save();
        $this->info("Created {$role} account for {$email}.");

        return self::SUCCESS;
    }
}
