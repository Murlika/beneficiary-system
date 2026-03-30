<?php

namespace App\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

class Services extends ResourceController
{   
     protected $modelName = 'App\Models\ServiceModel';
    protected $format    = 'json';

    /**
     * Return an array of resource objects, themselves in array format.
     *
     * @return ResponseInterface
     */

    public function index()
    {
        $filters = [
            'search' => $this->request->getGet('search'),
            'status' => $this->request->getGet('status'),
            'date'   => $this->request->getGet('date'),
            'sort'   => $this->request->getGet('sort') ?? 'service_date', 
            'order'  => $this->request->getGet('order') ?? 'DESC',            
        ];

    dino_log('info', 'REGISTRY', 'FETCH_LIST', "Диплодок запрашивает список услуг", [
        'filters' => array_filter($filters), 
        'page'    => $this->request->getGet('page') ?? 1
    ]);

    try {
        $data = $this->model->getRegistry($filters);

        dino_log('debug', 'REGISTRY', 'FETCH_SUCCESS', "Извлечено окаменелостей: " . count($data));

        return $this->respond($data);

    } catch (\Exception $e) {
        dino_log('error', 'REGISTRY', 'FETCH_CRASH', "Обвал в шахте при поиске: " . $e->getMessage());
        return $this->failServerError('Ошибка при раскопках реестра... 🌋');
    }

    }

    /**
     * Return the properties of a resource object.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function show($id = null)
    {
        // Берем с JOIN, чтобы Angular сразу увидел имя бенефициара для автокомплита
        $record = $this->model
            ->select('services.*, beneficiaries.full_name as beneficiary_name')
            ->join('beneficiaries', 'beneficiaries.id = services.beneficiary_id')
            ->find($id);

        return $record ? $this->respond($record) : $this->failNotFound("Услуга не найдена 🦴");
    }


    /**
     * Return a new resource object, with default properties.
     *
     * @return ResponseInterface
     */
    public function new()
    {
        //
    }

    /**
     * Create a new resource object, from "posted" parameters.
     *
     * @return ResponseInterface
     */
    public function create()
    {
        $data = $this->request->getJSON(true);
        
        if ($id = $this->model->insert($data)) {
            return $this->respondCreated(['id' => $id, 'message' => 'Услуга зарегистрирована! ✨']);
        }
        
        return $this->failValidationErrors($this->model->errors());
    }

    /**
     * Return the editable properties of a resource object.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function edit($id = null)
    {
        //
    }

    /**
     * Add or update a model resource, from "posted" properties.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function update($id = null)
    {
        $data = $this->request->getJSON(true);

        if ($this->model->update($id, $data)) {
            return $this->respond(['message' => 'Данные услуги мутировали успешно 🧪']);
        }

        return $this->failValidationErrors($this->model->errors());
    }

    /**
     * Delete the designated resource object from the model.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function delete($id = null)
    {
        if ($this->model->delete($id)) {
            return $this->respondDeleted(['message' => 'Услуга аннигилирована 📦']);
        }
        return $this->failNotFound("Не удалось удалить... 🌋");
    }
}
