<?php
use App\Model\Role;
use App\Model\User;

class CreateRolesRightsUsers
{
    public function run()
    {
        Role::create([
            'name'        => 'admin',
            'description' => 'Administrator',
        ]);
        Role::create([
            'name'        => 'user',
            'description' => 'User',
        ]);

        $user = new User();

        $user->email     = 'admin@example.com';
        $user->full_name = 'Administrator';
        $user->password  = password_hash('qwerty', PASSWORD_DEFAULT, ['cost' => 13]);
        $user->role_id   = User::ROLE_ADMIN;
        $user->status    = User::STATUS_ACTIVE;
        $user->save();

        $user = new User();

        $user->email     = 'user@example.com';
        $user->full_name = 'User';
        $user->password  = password_hash('qwerty', PASSWORD_DEFAULT, ['cost' => 13]);
        $user->role_id   = User::ROLE_USER;
        $user->status    = User::STATUS_ACTIVE;
        $user->save();
    }
}
