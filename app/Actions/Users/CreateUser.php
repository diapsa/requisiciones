<?php

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateUser
{
    /**
     * Create a pending user with one role. There is no public registration: the administrator registers everyone,
     * and the account stays inactive until the user chooses a password from the emailed invitation.
     *
     * @param  array{name: string, email: string, role: string, coordinator_id: int|string|null}  $attributes
     */
    public function handle(array $attributes): User
    {
        return DB::transaction(function () use ($attributes): User {
            $user = User::query()->create([
                ...Arr::only($attributes, ['name', 'email', 'coordinator_id']),
                'password' => Str::password(64),
            ]);

            $user->assignRole($attributes['role']);

            return $user;
        });
    }
}
