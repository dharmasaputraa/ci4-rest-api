<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

class UserSeeder extends Seeder
{
    public function run()
    {
        $userModel = new UserModel();

        $users = [
            [
                'username' => 'dharmasaputraa',
                'email'    => 'putudharma0@gmail.com',
                'password' => 'admin*123',
            ],
            [
                'username' => 'adminuser',
                'email'    => 'admin@example.com',
                'password' => 'admin*123',
            ],
        ];

        foreach ($users as $data) {
            $user = new User([
                'username' => $data['username'],
                'email'    => $data['email'],
                'password' => $data['password'],
            ]);
            $userModel->save($user);
        }
    }
}
