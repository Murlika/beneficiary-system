<?php
namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Services\ExcelService;
use App\Entities\Service;

class Excel extends ResourceController
{   

    protected $excelService;

public function __construct() {
        $this->excelService = new ExcelService(); 
        // TODO в идеале юзать service('ai') или через конструктор
    }

    
public function index() {
    dino_log('info', 'EXCEL', 'EXPORT_START', "Начинаем раскопки данных для выгрузки в файл... 🐾");
  
    $rawFilters = $this->request->getGet();
    $cleanFilters = [];

    if (!empty($rawFilters['search'])) {
        $cleanFilters['search'] = substr(trim($rawFilters['search']), 0, 100);
        unset($rawFilters['search']);
    }

    $allowedStatuses = array_keys(Service::getStatusList());
    
    if (!empty($rawFilters['status']) && in_array($rawFilters['status'], $allowedStatuses)) {
        $cleanFilters['status'] = $rawFilters['status'];
        unset($rawFilters['status']);
    }

    foreach ($rawFilters as $key => $value) {
        // Пропускаем системные и пустые кости
        //TODO проверять на допустимые поля in_array($key, $systemKeys)
        if ($value === '' || $value === null) {
            continue;
        }

        // Чистим значение (XSS и прочая дичь)
        $cleanFilters[$key] = substr(trim((string)$value), 0, 255);
    }

    try {
        $this->excelService->export($cleanFilters);
        dino_log('info', 'EXCEL', 'EXPORT_SUCCESS', "Все кости успешно упакованы в Excel-архив! 📦🦕");
    } catch (\Exception $e) {
        dino_log('error', 'EXCEL', 'EXPORT_CRASH', "Метеорит ударил по экспорту: " . $e->getMessage());
        return $this->fail('Не удалось создать файл выгрузки... 🌋');
    }
}


public function template()
{
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Шаблон импорта');

    // 🎩 1. Человеческие заголовки (вместо технических ID)
    $headers = ['Дата (ДД.ММ.ГГГГ)', 'Благополучатель (ФИО)', 'Услуга', 'Сумма (₽)', 'Статус', 'Комментарий'];
    $sheet->fromArray($headers, NULL, 'A1');

    // Стилизуем шапку (Indigo)
    $lastColLetter = 'F';
    $sheet->getStyle("A1:{$lastColLetter}1")->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']]
    ]);

    // 🧬 2. Готовим данные для выпадашек
    $statusLabels = array_values(\App\Entities\Service::getStatusList()); // ["🆕 Новая", "🛠️ В работе"...]
    $statusesString = '"' . implode(',', $statusLabels) . '"';

    // Подтягиваем типы услуг из базы для выпадашки
    $typeModel = new \App\Models\ServiceTypeModel();
    $types = array_column($typeModel->findAll(), 'title');
    $typesString = '"' . implode(',', $types) . '"';

    // 🛠 3. Накладываем "Ограничения" (Data Validation) на колонки
    // Пройдемся по первым 100 строкам, чтобы юзер мог просто выбирать
    for ($i = 2; $i <= 100; $i++) {
        // Выпадашка для Услуги (Колонка C)
        $validationType = $sheet->getCell("C{$i}")->getDataValidation();
        $validationType->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                       ->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP)
                       ->setAllowBlank(false)
                       ->setShowInputMessage(true)
                       ->setShowErrorMessage(true)
                       ->setShowDropDown(true)
                       ->setFormula1($typesString);

        // Выпадашка для Статуса (Колонка E)
        $validationStatus = $sheet->getCell("E{$i}")->getDataValidation();
        $validationStatus->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                         ->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP)
                         ->setAllowBlank(false)
                         ->setShowInputMessage(true)
                         ->setShowErrorMessage(true)
                         ->setShowDropDown(true)
                         ->setFormula1($statusesString);
    }

    // Пример заполнения 🦴
    $sheet->setCellValue('A2', date('d.m.Y'));
    $sheet->setCellValue('B2', 'Иванов Иван Иванович');
    $sheet->setCellValue('C2', $types[0] ?? 'Выберите услугу');
    $sheet->setCellValue('D2', '5000');
    $sheet->setCellValue('E2', $statusLabels[0] ?? '🆕 Новая');

    foreach (range('A', $lastColLetter) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="dino_import_template.xlsx"');
    
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}



public function upload()
{
    //TODO для реально тяжелых файлов используем ReadFilter
    set_time_limit(120);
    ini_set('memory_limit', '256M');

    $file = $this->request->getFile('excel_file');

    if (!$file || !$file->isValid()) {
        dino_log('warning', 'EXCEL', 'UPLOAD_FAIL', "Прислали битое яйцо или пустую корзину 🥚❌");
        return $this->fail('Файл битый, бро!');
    }
    if (!$file || $file->getError() === UPLOAD_ERR_INI_SIZE) {
        dino_log('warning', 'EXCEL', 'LARGE_FILE', "Попытка скормить диплодока: " . $file->getName() . " ({$file->getSize('mb')} MB)");
        return $this->fail('🌋 Файл слишком тяжелый! Лимит сервера: ' . ini_get('upload_max_filesize'), 413);
    }

    dino_log('info', 'EXCEL', 'IMPORT_PROCESS', "Начинаем расшифровку древних свитков из файла: " . $file->getName());

    try {
        $result = $this->excelService->import($file);

        dino_log('info', 'EXCEL', 'IMPORT_DONE', "Импорт завершен", [
            'success_count' => $result['success'] ?? 0,
            'error_count'   => count($result['errors'] ?? [])
        ]);

        return $this->respond([
            'message' => "Импорт завершен! Успешно раскопано: {$result['success']}",
            'errors'  => $result['errors'],
            'ai_changed' => $result['ai_changed']
        ]);

    } catch (\Exception $e) {
        // ☄️ Если сервис импорта не выжил
        dino_log('error', 'EXCEL', 'IMPORT_CRASH', "Сервис импорта вымер: " . $e->getMessage(), [
            'stack' => $e->getTraceAsString()
        ]);
        return $this->fail('Метеорит в процессе импорта! Сервер приуныл... 🌋');
    }
}

}