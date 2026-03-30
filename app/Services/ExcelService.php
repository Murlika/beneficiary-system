<?php
namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use App\Models\ServiceModel;
use app\Services\AiService;

class ExcelService {

    protected $aiService;
    protected $serviceModel;

    public function __construct()
    {
        $this->aiService    = service('ai'); 
        $this->serviceModel = new \App\Models\ServiceModel();
    }

// App/Services/ExcelService.php

    public function export(array $filters = []) 
    {        
        // 🐾 След 1: Начало выгрузки с фильтрами
        dino_log('info', 'EXCEL', 'EXPORT_START', "Запуск генерации архива. Фильтры: " . json_encode($filters));

        // 🦖 Тянем данные через JOIN-метод (до 5000 костей)
        // Используем findForRegistry, чтобы были имена бенефициаров и заголовки услуг
        $result = $this->serviceModel->getRegistry($filters, 5000, 0);
        $data = $result['data'];
        
        dino_log('debug', 'EXCEL', 'DATA_FETCHED', "Извлечено " . count($data) . " костей для экспорта");

        if (empty($data)) {
            dino_log('warning', 'EXCEL', 'EMPTY_EXPORT', "Попытка экспорта пустой долины! 🏜️");
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Реестр услуг');

        // 🎩 ШАПКА
        $headers = ['ID', 'Дата', 'Благополучатель', 'Услуга', 'Сумма (₽)', 'Статус'];
        $sheet->fromArray($headers, NULL, 'A1');
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);

        // 🧬 НАПОЛНЕНИЕ
        $statusLabels = \App\Entities\Service::getStatusList(); // 👈 Берем переводы из Энтити
        $row = 2;
        
        foreach ($data as $item) {
            $sheet->setCellValue("A{$row}", $item['id']);
            $sheet->setCellValue("B{$row}", $item['service_date']);
            $sheet->setCellValue("C{$row}", $item['beneficiary_name']);
            $sheet->setCellValue("D{$row}", $item['service_title']);
            $sheet->setCellValue("E{$row}", (float)$item['amount']);
            // Магия статусов: если нет в словаре, пишем как есть
            $sheet->setCellValue("F{$row}", $statusLabels[$item['status']] ?? $item['status']);
            $row++;
        }

        // 📏 АВТО-ШИРИНА 
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        dino_log('info', 'EXCEL', 'FILE_READY', "Файл сформирован, отправляем диплодоку в браузер 📦");

        $fileName = 'dino_registry_' . date('Y-m-d_Hi') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        
        exit;
    }

    private function getReverseMaps(): array 
    {
        $typeModel = new \App\Models\ServiceTypeModel();
        
        return [
            // 'Название услуги' => ID
            'types'   => array_column($typeModel->findAll(), 'id', 'title'),
            
            // '🆕 Новая' => 'new'
            'statuses' => array_flip(\App\Entities\Service::getStatusList()), 
            
            // 'ФИО' => ID (берем только активных)
            'beneficiaries' => array_column((new \App\Models\BeneficiaryModel())->findAll(), 'id', 'full_name')
        ];
    }

    private function formatDate($rawDate): string
    {
        if (empty($rawDate)) {
            return date('Y-m-d');
        }

        // Магия Excel: он часто хранит даты как числа (например, 45381)
        if (is_numeric($rawDate)) {
            try {
                return date('Y-m-d', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($rawDate));
            } catch (\Exception $e) {
                return date('Y-m-d'); // Фолбэк, если число кривое
            }
        }

        // Если это строка (например, "30.03.2026")
        $timestamp = strtotime($rawDate);
        if ($timestamp === false) {
            // Пробуем явный формат, если strtotime не справился
            $d = \DateTime::createFromFormat('d.m.Y', $rawDate);
            return $d ? $d->format('Y-m-d') : date('Y-m-d');
        }

        return date('Y-m-d', $timestamp);
    }


    private function parseRow(array $row, array $maps, int $rowNum): array
    {
        $result = [
            'isValid' => false,
            'data'    => null,
            'error'   => null
        ];
        
        // 🛡️ Безопасная распаковка
        $dateRaw     = $row[0] ?? null;
        $fio         = $row[1] ?? null;
        $typeTitle   = $row[2] ?? null;
        $amountRaw   = $row[3] ?? 0;
        $statusLabel = $row[4] ?? null;
        $comment     = $row[5] ?? '';

       // проверка на пустую строку
        if (empty($fio) && empty($typeTitle)) {
            return [
                'isValid' => false,
                'error'   => "Строка  пустая"
            ];
        }
        
        // Используем твои ключи из getReverseMaps()
        $bId    = $maps['beneficiaries'][$fio] ?? null;
        $tId    = $maps['types'][$typeTitle] ?? null;
        $status = $maps['statuses'][$statusLabel] ?? null;

        if (!$bId || !$tId || !$status) {
            $missing = !$bId ? "бенефициар '$fio'" : (!$tId ? "услуга '$typeTitle'" : "статус");
            return [
                'isValid' => false,
                'error'   => "Не найден $missing"
            ];
        }

        $amountClean = (float) str_replace(',', '.', (string)$amountRaw);

        $result = [
            'isValid' => true,
            'error'   => null,
            'data' => [
                'beneficiary_id' => $bId,
                'type_id'        => $tId,
                'service_date'   => $this->formatDate($dateRaw),
                'amount'         => (float)$amountClean,
                'status'         => $status,
                'comment'        => $comment ?? '',
                'created_at'     => date('Y-m-d H:i:s')
            ]
        ]; 

        return $result;

    }



    public function import($file) {
        $fileName = $file->getName();

        set_time_limit(120);
        $db = \Config\Database::connect();
       
        $db->query("CREATE TEMPORARY TABLE IF NOT EXISTS temp_import_services AS SELECT * FROM services WHERE 1=0");
        $db->table('temp_import_services')->emptyTable();

        dino_log('info', 'EXCEL', 'IMPORT_START', "Начало дешифровки файла: $fileName");

        $maps = $this->getReverseMaps();

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            
            $totalInFile = count($rows);

            dino_log('debug', 'EXCEL', 'PARSE_READY', "Файл прочитан. Строк к обработке: $totalInFile");

            $errors = [];
            $totalImported = 0;
            $importedCount=0;
            $aiWaitlist = []; // 🚩 Мешок для подозрительных строк

            foreach (array_slice($rows, 1) as $index => $row) {
                if (empty(array_filter($row))) continue;
                if (empty($row) || !isset($row[0])) continue; 

                try {
                    $parsed = $this->parseRow($row, $maps, $index + 2);
                } catch (\Throwable $e) {
                    // 🦖 Диплодок не подавился, а просто отложил невкусный кусок
                    $errors[] = "Строка " . ($index + 2) . ": Критическая ошибка данных (пропущено)";
                    dino_log('error', 'IMPORT', 'ROW_CRASH', $e->getMessage(), ['row' => $row]);
                    continue; 
                }
                
                if ($parsed['isValid']) {
                    $toBatch[] = $parsed['data'];
                } else {
                    $aiWaitlist[] = ['row' => $row, 'rowNum' => $index + 2];
                    $errors[] = "Строка " . ($index + 2) . ": " . ($parsed['error'] ?? 'Неизвестная ошибка в строке');
                    dino_log('warning', 'EXCEL', 'ROW_SKIP', "Пропуск строки " . ($index + 2), ['reason' => $parsed['error'] ?? 'Неизвестная ошибка в строке']);
                    
                    continue;
                }
                
                // 📦 Чанкуем: если набралось 200 строк — сбрасываем в базу
                if (count($toBatch) >= 200) {
                    $db->table('services')->insertBatch($toBatch);
                    $toBatch = [];
                }
    
                $importedCount++;
            }



            // 🤖 МАГИЯ ИИ: Обрабатываем мешок одним махом
            //еще один шанс
            $aiResults = [];
            if (!empty($aiWaitlist)) {

                $aiResults = $this->aiService->batchMatchBeneficiaries($aiWaitlist, $maps);

                foreach ($aiResults as  $row) {
                    try {
                        $parsed = $this->parseRow($row['row'], $maps, $row['rowNum']);


                    } catch (\Throwable $e) {
                        $errors[] = "Строка " . ($row['rowNum']) . ": Критическая ошибка данных (пропущено)";
                        dino_log('error', 'IMPORT', 'AI_CRASH', $e->getMessage(), ['row' => $row['row']]);
                        continue; 
                    }

                    if ($parsed['isValid']) {
                        $toBatch[] = $parsed['data'];
                    } else {
                        $errors[] = "Строка " . ($row['rowNum']) . ": " . ($parsed['error'] ?? 'Неизвестная ошибка в строке');
                        dino_log('warning', 'EXCEL', 'AI_SKIP', "Пропуск строки " . ($row['rowNum']), ['reason' => $parsed['error'] ?? 'Неизвестная ошибка в строке']);
                        
                        continue;
                    }
                }
            } 

            if (!empty($toBatch)) {
               $db->table('services')->insertBatch($toBatch);
            }

            // 🚀 Финальный перенос из TEMP в основную таблицу одним SQL-запросом
            $totalImported = $db->table('temp_import_services')->countAllResults();
            if ($totalImported > 0) {
                $db->transStart();
                $db->query("INSERT INTO services (beneficiary_id, type_id, service_date, amount, 'status', comment) 
                            SELECT beneficiary_id, type_id, service_date, amount, 'status', comment 
                            FROM temp_import_services");
                $db->transComplete();
            }

            // ✨ Финальный отчет
            dino_log('info', 'EXCEL', 'IMPORT_COMPLETE', "Раскопки завершены!", [
                'file' => $fileName,
                'success' => $importedCount,
                'ai_changed' => $aiResults,
                'failed' => count($errors)
            ]);

            return ['success' => $importedCount, 'errors' => $errors, 'ai_changed' => $aiResults];

        } catch (\Exception $e) {
            // ☄️ Метеорит упал прямо на парсер
            dino_log('critical', 'EXCEL', 'IMPORT_CRASH', "Файл оказался ядовитым: " . $e->getMessage());
            throw $e;
        }
    }
}


