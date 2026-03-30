<?php

namespace App\Controllers\Api;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use App\Models\BeneficiaryModel;

class Beneficiary extends ResourceController
{   
     protected $modelName = 'App\Models\BeneficiaryModel';
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
        ];

        $limit = (int) ($this->request->getGet('limit') ?? 20);
        $page  = (int) ($this->request->getGet('page') ?? 1);
        
        $offset = ($page - 1) * $limit;

        dino_log('info', 'BENEFICIARY', 'FETCH_PAGE', "Запрос страницы {$page} (лимит {$limit})", [
            'filters' => array_filter($filters)
        ]);

        $result = $this->model->findForRegistry($filters, $limit, $offset);

        return $this->respond($result);
    }


    public function search() 
    {
        $term = $this->request->getGet('term') ?? '';
        
        if (mb_strlen($term) < 2) {
            return $this->respond($this->model->orderBy('id', 'DESC')->limit(10)->findAll()); 
        }

        dino_log('debug', 'BENEFICIARY', 'AUTOCOMPLETE', "Поиск бенефициара по буквам: {$term}");

        $data = $this->model->search($term, 15);

        return $this->respond($data);
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
        $record = $this->model->find($id);
        
        if (!$record) {
            return $this->failNotFound("Динозавр с ID {$id} не найден в раскопках... 🦴");
        }

        //  Entity, он сам превратит JSONB в массив/объект
        return $this->respond($record);
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
        // 🧬 Раскапываем JSON из запроса
        $json = $this->request->getJSON(true);

        if (empty($json)) {
            return $this->fail('Пустой запрос... Метеорит украл данные? ☄️', 400);
        }

        // Готовим данные (склеиваем JSONB для Postgres)
        $data = [
            'type'       => $json['type'] ?? 'person',
            'full_name'  => $json['full_name'],
            'extra_data' => json_encode($json['extra_data'] ?? []), // Храним как JSON 🧠
        ];

        if ($id = $this->model->insert($data)) {
            dino_log('info', 'BENEFICIARY', 'CREATE_SUCCESS', "Родился новый бенефициар: {$data['full_name']} (ID: {$id})");
            return $this->respondCreated(['id' => $id, 'message' => 'Запись зарыта в базу! 🥚']);
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
        $json = $this->request->getJSON(true);

        if (!$this->model->find($id)) {
            return $this->failNotFound("Динозавр с ID {$id} не найден. 🦴");
        }

        $data = [
            'type'       => $json['type'],
            'full_name'  => $json['full_name'],
            'extra_data' => json_encode($json['extra_data'] ?? []),
        ];

        if ($this->model->update($id, $data)) {
            dino_log('info', 'BENEFICIARY', 'UPDATE_SUCCESS', "Бенефициар (ID: {$id}) мутировал успешно.");
            return $this->respond(['message' => 'Данные обновлены! 📜']);
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
        $record = $this->model->find($id);
        if (!$record) {
            return $this->failNotFound("Бенефициар №{$id} уже вымер или не найден 🦴");
        }

        // 🛡️ ТЗ п.6: Удаляем (Soft Delete)
        if ($this->model->delete($id)) {
            dino_log('info', 'BENEFICIARY', 'DELETE_SUCCESS', "Удален бенефициар: {$record->full_name} (ID: {$id})");
            return $this->respondDeleted(['message' => 'Запись успешно отправлена в архив 📦']);
        }

        return $this->failServerError('Не удалось совершить вымирание... 🌋');
    }

}
