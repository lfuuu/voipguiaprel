<?php

namespace app\controllers\json\api_billing;

use app\classes\JsonController;
use app\models\billing_api\ApiPricelist;
use app\models\billing_api\ApiPricelistItem;
use yii\db\Query;
use yii\db\Expression;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yii\web\HttpException;
use yii\web\Response;
use yii\web\ForbiddenHttpException;
use app\exceptions\FormValidationException;

class ApiPricelistController extends JsonController
{
    protected $modelName = 'app\models\billing_api\ApiPricelist';
    protected $idParamName = 'id';
    protected $nameParamName = 'name';
    protected $withDependencies = ['items'];
    protected $createPermission = 'api_billing_api_pricelist_create';
    protected $listPermission = 'api_billing_api_pricelist_list';
    protected $editPermission = 'api_billing_api_pricelist_edit';
    protected $deletePermission = 'api_billing_api_pricelist_delete';

    public function actionRead()
    {
        if (!\Yii::$app->user->can($this->listPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;

        $where = [];
        foreach ($this->readWhere as $param) {
            $where[$param] = $this->request[$param];
        }

        $items =
            $modelName::find()
                ->select($this->readSelect)
                ->orderBy($this->nameParamName)
                ->andWhere($where)
                ->andWhere(['is_active' => true])
                ->asArray()
                ->all();

        $items = $this->performAfterReadActions($items);

        return $items;
    }

    public function actionSave()
    {
        if (!\Yii::$app->user->can($this->editPermission) && !\Yii::$app->user->can($this->createPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;

        $result = [];

        if (isset($this->request[$this->idParamName])) {
            if (!\Yii::$app->user->can($this->editPermission)) {
                throw new ForbiddenHttpException('Access denied');
            }

            $item = $modelName::findOne($this->request[$this->idParamName]);

            if ($item === null) {
                if ($this->throwExceptionOnEmptyItemInSave) {
                    throw new HttpException(404, $this->modelName . ' не найден');
                } else {
                    if (!\Yii::$app->user->can($this->createPermission)) {
                        throw new ForbiddenHttpException('Access denied');
                    }

                    $item = $modelName::create();
                    $result['log'] = ['data_before' => []];
                }
            } else {
                $result['log'] = ['data_before' => self::getDataForLog($item)];
            }
        } else {
            if (!\Yii::$app->user->can($this->createPermission)) {
                throw new ForbiddenHttpException('Access denied');
            }

            $item = $modelName::create();
            $result['log'] = ['data_before' => []];
        }

        $item->load($this->request, '');

        $this->performBeforeSaveActions($item, $this->request);

        $transaction = $modelName::getDb()->beginTransaction();
        try {
            if (!$item->save()) {
                throw new FormValidationException($item);
            }

            // ---- СОХРАНЕНИЕ/ОБНОВЛЕНИЕ ПУНКТОВ + СБОР ИХ ФАКТИЧЕСКИХ ID ----
            $savedIds = [];

            if (isset($this->request['items'])) {
                foreach ($this->request['items'] as $apiItem) {

                    // Гарантируем привязку к текущему прайс-листу
                    $apiItem['pricelist_id'] = $item->id;

                    $currentApiItem = null;
                    if (!empty($apiItem['id'])) {
                        $currentApiItem = ApiPricelistItem::findOne(['id' => (int)$apiItem['id']]);
                    }

                    if ($currentApiItem === null) {
                        $currentApiItem = ApiPricelistItem::create($apiItem, $item);
                    } else {
                        foreach ($apiItem as $field => $value) {
                            $currentApiItem->$field = $value;
                        }
                    }

                    if (!$currentApiItem->save()) {
                        throw new FormValidationException($currentApiItem);
                    }

                    // Важно: добавляем реальный id (включая новые)
                    $savedIds[] = (int)$currentApiItem->id;
                }

                // ---- СИНХРОНИЗАЦИЯ (УДАЛЕНИЕ ОТСУТСТВУЮЩИХ) ----
                if (count($savedIds) > 0) {
                    ApiPricelistItem::deleteAll([
                        'and',
                        ['pricelist_id' => $item->id],
                        ['not in', 'id', $savedIds],
                    ]);
                } else {
                    // Пришёл пустой список — удалить все строки прайс-листа
                    ApiPricelistItem::deleteAll(['pricelist_id' => $item->id]);
                }
            }
            // -----------------------------------------------

            $this->performAfterSaveActions($item, $this->request);

            $transaction->commit();
        } catch (\Throwable $e) {
            if ($transaction->getIsActive()) {
                $transaction->rollBack();
            }
            throw $e;
        }

        $result['log']['data_after'] = self::getDataForLog($item);

        return $result;
    }

    public function actionReadArchive()
    {
        if (!\Yii::$app->user->can($this->listPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;

        $where = [];
        foreach ($this->readWhere as $param) {
            $where[$param] = $this->request[$param];
        }

        $items =
            $modelName::find()
                ->select($this->readSelect)
                ->orderBy($this->nameParamName)
                ->andWhere($where)
                ->andWhere(['is_active' => false])
                ->asArray()
                ->all();

        $items = $this->performAfterReadActions($items);

        return $items;
    }

    public function actionDelete()
    {
        if (!\Yii::$app->user->can($this->deletePermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;

        $item = $modelName::findOne($this->request[$this->idParamName]);
        $item->is_active = false;
        if (!$item->save()) {
            throw new FormValidationException($item);
        }
    }

    public function actionRestore()
    {
        if (!\Yii::$app->user->can($this->editPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;

        $item = $modelName::findOne($this->request[$this->idParamName]);
        $item->is_active = true;
        if (!$item->save()) {
            throw new FormValidationException($item);
        }
    }

    public function actionCopy()
    {
        if (!\Yii::$app->user->can('api_billing_api_pricelist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $result = (new Query())
            ->select(['id' => new Expression('billing_api.clone_pricelist(:old_pricelist_id)')])
            ->addParams([':old_pricelist_id' => $this->request['id']])
            ->one();

        return ['id' => $result['id']];
    }

    public function actionCopyAndMultiply()
    {
        if (!\Yii::$app->user->can('api_billing_api_pricelist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $multiplier = isset($this->request['multiplier']) ? (float)$this->request['multiplier'] : 1;
        if ($multiplier <= 0) {
            throw new \Exception('Неверный множитель');
        }

        $result = (new Query())
            ->select(['id' => new Expression('billing_api.clone_pricelist(:old_pricelist_id, :multiplier)')])
            ->addParams([
                ':old_pricelist_id' => $this->request['id'],
                ':multiplier' => $multiplier
            ])
            ->one();

        return ['id' => $result['id']];
    }

    public function actionExportToExcel()
    {
        // Проверка прав на чтение
        if (!\Yii::$app->user->can($this->listPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        // Загрузить сам прайс-лист вместе с элементами
        /** @var ApiPricelist $item */
        $item = $this->modelName::find()
            ->with([
                'items.api',        // связь ApiPricelistItem → Api
                'items.apiMethod'   // связь ApiPricelistItem → ApiMethod
            ])
            ->andWhere(['id' => $this->request['id']])
            ->one();

        if (!$item || empty($item->items)) {
            throw new HttpException(400, 'Нет строк для экспорта.');
        }

        $rows = [];
        foreach ($item->items as $line) {
            $rows[] = [
                $line->api->name ?? '—',
                $line->apiMethod->name ?? '—',
                $line->price,
                $line->cost,
                $line->enabled ? 'Да' : 'Нет',
            ];
        }

        $fileName = 'Pricelist_' . $item->name . '_' . date('Ymd_His') . '.xlsx';

        $this->createPricelistExcelDocument($rows, $fileName, $item->name);
        return null; // поток уже отдан
    }

    /**
     * Генерация Excel-документа для прайс-листа
     */
    protected function createPricelistExcelDocument(array $data, string $fileName, string $title)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Заголовки
        $headers = ['API', 'Метод API', 'Цена', 'Себестоимость', 'Включено'];

        // Стили для заголовка (как в CDR)
        $headerStyle = [
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders'   => ['bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ];

        // Титул (опционально)
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getRowDimension(1)->setRowHeight(20);

        // Записать заголовки
        $col = 'A';
        foreach ($headers as $hdr) {
            $sheet->setCellValue("{$col}2", $hdr);
            $sheet->getStyle("{$col}2")->applyFromArray($headerStyle);
            $col++;
        }

        // Данные
        $rowNum = 3;
        foreach ($data as $row) {
            $colNum = 1;
            foreach ($row as $value) {
                $sheet->setCellValueByColumnAndRow($colNum, $rowNum, $value);
                $colNum++;
            }
            $rowNum++;
        }

        // Автоподбор ширины
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        foreach (range('A', $lastCol) as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        // Границы ячеек
        $sheet->getStyle("A2:{$lastCol}" . ($rowNum - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ]);

        // Отдать файл
        try {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            \Yii::error("Excel export error: {$e->getMessage()}", __METHOD__);
            throw new HttpException(500, 'Ошибка создания Excel-файла.');
        }
    }
}
