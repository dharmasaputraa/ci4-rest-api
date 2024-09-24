<?php

namespace App\Controllers\Api\Auth;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

class AuthController extends ResourceController
{
    // POST
    // Use: To create and save users
    public function register()
    {
        $rules = [
            'username' => 'required|min_length[3]|max_length[20]|is_unique[users.username]',
            'email'    => 'required|valid_email|is_unique[auth_identities.secret]',
            'password' => 'required|min_length[8]',
        ];

        if (!$this->validate($rules)) {
            $response = [
                'status' => 422,
                'message' => 'Validation error',
                'errors' => $this->validator->getErrors(),
                'data' => []
            ];
            return $this->respond($response, $response['status']);
        }

        $userModel = new UserModel();
        $user = new User([
            'username' => $this->request->getVar('username'),
            'email'    => $this->request->getVar('email'),
            'password' => $this->request->getVar('password'),
        ]);

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            if ($userModel->save($user)) {
                $db->transComplete();

                $response = [
                    'status' => 201,
                    'message' => 'User registered successfully',
                    'data' => $user
                ];
            } else {
                throw new \Exception('Failed to register user');
            }
        } catch (\Exception $e) {
            $db->transRollback();

            $response = [
                'status' => 500,
                'message' => $e->getMessage(),
                'errors' => $userModel->errors(),
                'data' => []
            ];
        }

        return $this->respond($response, $response['status']);
    }

    // POST
    // Use: Login specific user to application
    // It generates a token value
    public function login()
    {
        if (auth()->loggedIn()) {
            auth()->logout();
        }

        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            $response = [
                'status' => 422,
                'message' => 'Validation error',
                'errors' => $this->validator->getErrors(),
                'data' => []
            ];
            return $this->respond($response, $response['status']);
        }

        $credentials = [
            'email'    => $this->request->getVar('email'),
            'password' => $this->request->getVar('password'),
        ];

        $authService = service('auth');
        $user = $authService->attempt($credentials);

        if (!$user) {
            $response = [
                'status' => 401,
                'message' => 'Invalid credentials',
                'data' => []
            ];
            return $this->respond($response, $response['status']);
        }

        $userModel = new UserModel();
        $user = $userModel->findById(auth()->id());
        $token = $user->generateAccessToken('access_token');
        $authToken = $token->raw_token;

        $response = [
            'status' => 200,
            'message' => 'User logged in successfully',
            'data' => [
                'token' => $authToken,
            ]
        ];
        return $this->respond($response, $response['status']);
    }

    // GET
    // Use: Logout use
    // Destroy token
    public function logout()
    {
        auth()->logout();
        auth()->revokeAllAccessTokens();

        $response = [
            'status' => 200,
            'message' => 'User logged out successfully',
            'data' => []
        ];
        return $this->respond($response, $response['status']);
    }

    public function accessDenied()
    {
        $response = [
            'status' => 401,
            'message' => 'Invalid access',
            'data' => []
        ];
        return $this->respond($response, $response['status']);
    }
}
