<?php

namespace App\Controllers\Api;

use App\Models\ProjectModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class ProjectController extends ResourceController
{
    protected $projectModel;

    public function __construct()
    {
        $this->projectModel = new ProjectModel();
    }

    public function list()
    {
        $page = $this->request->getVar('page') ?? 1;
        $perPage = $this->request->getVar('perPage') ?? 10;

        $userId = auth()->id();

        $projects = $this->projectModel->where(['user_id' => $userId])
            ->paginate($perPage, 'default', $page);
        $pager = $this->model->pager;

        if (!empty($projects)) {
            $response = [
                'status' => 200,
                'message' => 'Projects data',
                'data' => $projects,
                'pagination' => [
                    'currentPage' => $page,
                    'perPage' => $perPage,
                    'total' => $pager->getTotal(),
                    'lastPage' => $pager->getPageCount(),
                ],
            ];
        } else {
            $response = [
                'status' => 404,
                'message' => 'You not have any projects',
                'data' => [],
                'pagination' => null,
            ];
        }

        return $this->respond($response, $response['status']);
    }

    public function store()
    {
        $rules = [
            'name' => 'required',
            'budget'    => 'required',
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

        $userId = auth()->id();

        $data = [
            'user_id' => $userId,
            'name' => $this->request->getVar('name'),
            'budget' => $this->request->getVar('budget'),
        ];

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            if ($this->projectModel->insert($data)) {
                $db->transComplete();

                $response = [
                    'status' => 201,
                    'message' => 'Project has been saved',
                    'data' => $data
                ];
                return $this->respond($response, $response['status']);
            } else {
                throw new \Exception('Failed to create project');
            }
        } catch (\Exception $e) {
            $db->transRollback();

            $response = [
                'status' => 500,
                'message' => $e->getMessage(),
                'errors' => $this->projectModel->errors(),
                'data' => []
            ];
        }

        return $this->respond($response, $response['status']);
    }

    public function destroy($id = null)
    {
        $userId = auth()->id();

        $existingProject = $this->projectModel->find($id);
        if (!$existingProject) {
            $response = [
                'status' => 404,
                'message' => 'Project not found',
                'data' => [],
            ];
            return $this->respond($response, $response['status']);
        }

        if ($existingProject['user_id'] != $userId) {
            $response = [
                'status' => 401,
                'message' => 'You do not have permission to delete this project',
                'data' => [],
            ];
            return $this->respond($response, $response['status']);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            if ($this->projectModel->delete($id)) {
                $db->transComplete();

                $response = [
                    'status' => 201,
                    'message' => 'Project deleted successfully',
                    'data' => [],
                ];
                return $this->respond($response, $response['status']);
            } else {
                throw new \Exception('Failed to delete project');
            }
        } catch (\Exception $e) {
            $db->transRollback();

            $response = [
                'status' => 400,
                'message' => $e->getMessage(),
                'errors' => $this->projectModel->errors(),
                'data' => [],
            ];
        }

        return $this->respond($response, $response['status']);
    }
}
