<?php

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateUser
{
    /**
     * Update a user's data and role. The password is only changed by the user.
     *
     * @param  array{name: string, email: string, role: string, coordinator_id: int|string|null}  $attributes
     */
    public function handle(User $user, array $attributes): User
    {
        return DB::transaction(function () use ($user, $attributes): User {
            $user->fill(Arr::only($attributes, ['name', 'email', 'coordinator_id']))->save();
            $user->syncRoles([$attributes['role']]);

            return $user;
        });
    }
}
