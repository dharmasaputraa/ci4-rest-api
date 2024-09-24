<?php

namespace App\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\Shield\Models\UserModel;

class UserController extends ResourceController
{
    // GET
    public function profile()
    {
        $userId = auth()->id();

        $userModel = new UserModel();
        $userData = $userModel->findById($userId);

        $response = [
            'status' => 200,
            'message' => 'Profile information of logged in user',
            'data' => [
                'user' => $userData
            ]
        ];

        return $this->respond($response, $response['status']);
    }
}
