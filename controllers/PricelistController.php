<?php

namespace app\controllers;

use app\classes\BaseController;
use app\models\billing_uu\Pricelist;
use PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use \PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PricelistController extends BaseController
{
    private $_locations = [
        1 => 'Домашний регион',
        2 => 'Гостевой регион',
        3 => 'Международный регион'
    ];
    
    public function actionExcel($id)
    {
        if (!\Yii::$app->user->can('pricelist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $data = Pricelist::find()
            ->with('location.filterA.filterB.prefixPriceNoLimit')
            ->where(['id' => $id])
            ->asArray()
            ->one();
        
        $this->createExcelDocument($data);
    }
    
    public function actionExcelPrefixes($id)
    {
        if (!\Yii::$app->user->can('pricelist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $data = Pricelist::find()
            ->with('location.filterA')
            ->where(['id' => $id])
            ->asArray()
            ->one();
        
        $this->createExcelPrefixesDocument($data);
    }
    
    public function actionExcelLocations($id)
    {
        if (!\Yii::$app->user->can('pricelist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $data = Pricelist::find()
            ->alias('p')
            ->with('location')
            ->where(['p.id' => $id])
            ->asArray()
            ->one();
        
        $this->createExcelLocationsDocument($data);
    }
    
    private function createExcelDocument($pricelist)
    {
        $names = [
            "Направление A (ННП-фильтр)",
            "Направление B (ННП-фильтр)",
            "Валюта",
            "Цена номера B",
            "Цена номера B\n(Будущая 1)",
            "Дата начала\nдействия\n(Будущая 1)",
            "Статус",
            "Дата начала\nдействия"
        ];
        
        $currentRowNumber = 1;
        $minColumnNumber = 1;
        $maxColumnNumber = count($names);
        
        $spreadsheet = new Spreadsheet();
        
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Прайслист');
        for ($i = 0; $i < $maxColumnNumber; $i++) {
            $sheet->getColumnDimensionByColumn($i + 1)->setAutoSize(true);
        }
        
        Cell::setValueBinder(new AdvancedValueBinder());

        $pricelistHeader = $pricelist['name'];
        $sheet->setCellValueByColumnAndRow($minColumnNumber, $currentRowNumber, $pricelistHeader);
        $sheet->getRowDimension($currentRowNumber)->setRowHeight(30);
        $sheet->getStyleByColumnAndRow($minColumnNumber, $currentRowNumber, $minColumnNumber, $currentRowNumber)
            ->getFont()->setSize(22);
        $currentRowNumber += 2;
        
        $pricelistDate = $pricelist['date_created'];
        $sheet->setCellValueByColumnAndRow($minColumnNumber, $currentRowNumber, $pricelistDate);
        $sheet->getStyleByColumnAndRow($minColumnNumber, $currentRowNumber, $minColumnNumber, $currentRowNumber)
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyleByColumnAndRow($minColumnNumber, $currentRowNumber, $minColumnNumber, $currentRowNumber)
            ->getFont()->setSize(12);
    
        if ($pricelist['description']) {
            $currentRowNumber += 2;
            $pricelistDescription = $pricelist['description'];
            $sheet->setCellValueByColumnAndRow($minColumnNumber, $currentRowNumber, $pricelistDescription);
            $sheet->getStyleByColumnAndRow($minColumnNumber, $currentRowNumber, $minColumnNumber, $currentRowNumber)
                ->getFont()->setSize(12);
            $currentRowNumber += 4;
        } else {
            $currentRowNumber += 4;
        }
        
        $sheet->getStyleByColumnAndRow($minColumnNumber, $currentRowNumber, $maxColumnNumber, $currentRowNumber)
            ->getAlignment()->setWrapText(true);
        
        $sheet->getRowDimension($currentRowNumber)->setRowHeight(50);
    
        $sheet->getStyleByColumnAndRow($minColumnNumber, $currentRowNumber, $maxColumnNumber, $currentRowNumber)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
        $sheet->getStyleByColumnAndRow($minColumnNumber, $currentRowNumber, $maxColumnNumber, $currentRowNumber)
            ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        
        for ($i = 0; $i < count($names); $i++) {
            $sheet->setCellValueByColumnAndRow($minColumnNumber + $i, $currentRowNumber, $names[$i]);
        }
    
        $sheet->getStyleByColumnAndRow($minColumnNumber, $currentRowNumber, $maxColumnNumber, $currentRowNumber)
            ->getBorders()->getAllBorders()->setBorderStyle(true);
        
        $currentRowNumber += 1;
        
        $countryNames = [];
        
        foreach ($pricelist['location'] as $location) {
            if (empty($location['filterA'])) {
                continue;
            }
            
            foreach ($location['filterA'] as $filterA) {
                if (empty($filterA['filterB'])) {
                    continue;
                }
                
                if (isset($filterA['nnp_country_name_eng'])) {
                    $countryNames = array_merge($countryNames, explode(', ', $filterA['nnp_country_name_eng']));
                }

                if (!empty($filterA['description'])) {
                    $filterAHeader = trim($filterA['description']);
                    $filterACount = 0;
                } else {
                    $filterAHeader = '';
                    $filterAHeaderArray = [];
                    
                    if (isset($filterA['nnp_country_name_eng'])) {
                        $filterAHeaderArray = array_merge($filterAHeaderArray, explode(', ', $filterA['nnp_country_name_eng']));
                    }
    
                    if (isset($filterA['nnp_ndc_type_name'])) {
                        $filterAHeaderArray = array_merge($filterAHeaderArray, explode(', ', $filterA['nnp_ndc_type_name']));
                    }
    
                    if (isset($filterA['nnp_operator_name'])) {
                        $filterAHeaderArray = array_merge($filterAHeaderArray, explode(', ', $filterA['nnp_operator_name']));
                    }
    
                    if (isset($filterA['nnp_region_name'])) {
                        $filterAHeaderArray = array_merge($filterAHeaderArray, explode(', ', $filterA['nnp_region_name']));
                    }
    
                    if (isset($filterA['nnp_city_name'])) {
                        $filterAHeaderArray = array_merge($filterAHeaderArray, explode(', ', $filterA['nnp_city_name']));
                    }
                    
                    $filterACount = 0;
                    foreach ($filterAHeaderArray as $item) {
                        if (strlen($filterAHeader) > 100 * (1 + $filterACount)) {
                            $filterAHeader .= "\n" . $item;
                            $filterACount++;
                        } else {
                            $filterAHeader .= " " . $item;
                        }
                    }
                    
                    $filterAHeader = trim($filterAHeader);
                }
                
                if (empty($filterAHeader)) {
                    $filterAHeader = 'Пустой фильтр A';
                }

                $sheet->setCellValueByColumnAndRow($minColumnNumber, $currentRowNumber, $filterAHeader);
                $filterAStartRowNumber = $currentRowNumber;
                
                foreach ($filterA['filterB'] as $filterB) {
                    if (empty($filterB['prefixPriceNoLimit'])) {
                        continue;
                    }
    
                    if (!empty($filterB['description'])) {
                        $filterBText = trim($filterB['description']);
                        $filterBCount = 0;
                    } else {
                        $filterBText = '';
                        $filterBTextArray = [];
        
                        if (isset($filterB['nnp_country_name_eng'])) {
                            $filterBTextArray = array_merge($filterBTextArray, explode(', ', $filterB['nnp_country_name_eng']));
                        }
        
                        if (isset($filterB['nnp_ndc_type_name'])) {
                            $filterBTextArray = array_merge($filterBTextArray, explode(', ', $filterB['nnp_ndc_type_name']));
                        }
        
                        if (isset($filterB['nnp_operator_name'])) {
                            $filterBTextArray = array_merge($filterBTextArray, explode(', ', $filterB['nnp_operator_name']));
                        }
        
                        if (isset($filterB['nnp_region_name'])) {
                            $filterBTextArray = array_merge($filterBTextArray, explode(', ', $filterB['nnp_region_name']));
                        }
        
                        if (isset($filterB['nnp_city_name'])) {
                            $filterBTextArray = array_merge($filterBTextArray, explode(', ', $filterB['nnp_city_name']));
                        }
        
                        $filterBCount = 0;
                        foreach ($filterBTextArray as $item) {
                            if (strlen($filterBText) > 100 * (1 + $filterBCount)) {
                                $filterBText .= "\n" . $item;
                                $filterBCount++;
                            } else {
                                $filterBText .= " " . $item;
                            }
                        }
    
                        $filterBText = trim($filterBText);
                    }
                    
                    if (empty($filterBText)) {
                        $filterBText = 'Пустой фильтр B';
                    }
                    
                    $sheet->setCellValueByColumnAndRow($minColumnNumber + 1, $currentRowNumber, $filterBText);
                    $filterBStartRowNumber = $currentRowNumber;
    
                    $simplifiedPrefixList = [];
                    
                    foreach ($filterB['prefixPriceNoLimit'] as $prefixPrice) {
                        if (array_key_exists($prefixPrice['prefix_b'], $simplifiedPrefixList)) {
                            $simplifiedPrefixList[$prefixPrice['prefix_b']][] = $prefixPrice;
                        } else {
                            $simplifiedPrefixList[$prefixPrice['prefix_b']] = [];
                            $simplifiedPrefixList[$prefixPrice['prefix_b']][] = $prefixPrice;
                        }
                    }
                    
                    foreach ($simplifiedPrefixList as $prefixPrice) {
                        $sheet->setCellValueByColumnAndRow($minColumnNumber + 2, $currentRowNumber, $pricelist['currency_id']);
                        $sheet->setCellValueByColumnAndRow($minColumnNumber + 3, $currentRowNumber, $prefixPrice[0]['b_number_price']);
    
                        if (isset($prefixPrice[1])) {
                            $direction = $prefixPrice[1]['b_number_price'] > $prefixPrice[0]['b_number_price'] ? 'Повышение' : 'Понижение';
                            $sheet->setCellValueByColumnAndRow($minColumnNumber + 4, $currentRowNumber, $prefixPrice[1]['b_number_price']);
                            $sheet->setCellValueByColumnAndRow($minColumnNumber + 5, $currentRowNumber, $prefixPrice[1]['date_from']);
                            $sheet->setCellValueByColumnAndRow($minColumnNumber + 6, $currentRowNumber, $direction);
                        }
    
                        $sheet->setCellValueByColumnAndRow($minColumnNumber + 7, $currentRowNumber, $prefixPrice[0]['date_from']);
                        
                        $currentRowNumber++;
                    }
                    
                    $filterBEndRowNumber = $currentRowNumber - 1;
                    $sheet->mergeCellsByColumnAndRow($minColumnNumber + 1, $filterBStartRowNumber, $minColumnNumber + 1, $filterBEndRowNumber);
                    
                    if (($filterBEndRowNumber - $filterBStartRowNumber) < $filterBCount) {
                        $sheet->getRowDimension($filterBStartRowNumber)->setRowHeight(15 * ($filterBCount - $filterBEndRowNumber + $filterBStartRowNumber + 1));
                    }
                    
                    $sheet->getStyleByColumnAndRow($minColumnNumber + 1, $filterBStartRowNumber, $minColumnNumber + 1, $filterBEndRowNumber)
                        ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                }
                
                if ($filterAStartRowNumber == $currentRowNumber) {
                    $filterAEndRowNumber = $currentRowNumber;
                } else {
                    $filterAEndRowNumber = $currentRowNumber - 1;
                }
                
                $sheet->mergeCellsByColumnAndRow($minColumnNumber, $filterAStartRowNumber, $minColumnNumber, $filterAEndRowNumber);
                
                if (($filterAEndRowNumber - $filterAStartRowNumber) < $filterACount) {
                    $sheet->getRowDimension($filterAStartRowNumber)->setRowHeight(15 * ($filterACount - $filterAEndRowNumber + $filterAStartRowNumber + 1));
                }
                
                $sheet->getStyleByColumnAndRow($minColumnNumber, $filterAStartRowNumber, $minColumnNumber, $filterAEndRowNumber)
                    ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            }
        }
        
        $spreadsheet->createSheet();
    
        $sheet = $spreadsheet->getSheet(1);
        $sheet->setTitle('Single line');
    
        $names = [
            "Направление (ННП-фильтр)",
            "Код",
            "Валюта",
            "Цена номера B",
            "Цена номера B\n(Будущая 1)",
            "Дата начала\nдействия\n(Будущая 1)",
            "Статус",
            "Цена номера B\n(Будущая 2)",
            "Дата начала\nдействия\n(Будущая 2)",
            "Статус",
            "Дата начала\nдействия"
        ];
    
        $currentRowNumber = 1;
        $minColumnNumber = 1;
        $maxColumnNumber = count($names);
    
        for ($i = 0; $i < $maxColumnNumber; $i++) {
            $sheet->getColumnDimensionByColumn($i + 1)->setAutoSize(true);
        }
    
        Cell::setValueBinder(new AdvancedValueBinder());
    
        $pricelistHeader = $pricelist['name'];
        $sheet->setCellValueByColumnAndRow($minColumnNumber, $currentRowNumber, $pricelistHeader);
        $sheet->getRowDimension($currentRowNumber)->setRowHeight(30);
        $sheet->getStyleByColumnAndRow($minColumnNumber, $currentRowNumber, $minColumnNumber, $currentRowNumber)
            ->getFont()->setSize(22);
        $currentRowNumber += 2;
    
        $pricelistDate = $pricelist['date_created'];
        $sheet->setCellValueByColumnAndRow($minColumnNumber, $currentRowNumber, $pricelistDate);
        $sheet->getStyleByColumnAndRow($minColumnNumber, $currentRowNumber, $minColumnNumber, $currentRowNumber)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyleByColumnAndRow($minColumnNumber, $currentRowNumber, $minColumnNumber, $currentRowNumber)
            ->getFont()->setSize(12);
    
        if ($pricelist['description']) {
            $currentRowNumber += 2;
            $pricelistDescription = $pricelist['description'];
            $sheet->setCellValueByColumnAndRow($minColumnNumber, $currentRowNumber, $pricelistDescription);
            $sheet->getStyleByColumnAndRow($minColumnNumber, $currentRowNumber, $minColumnNumber, $currentRowNumber)
                ->getFont()->setSize(12);
            $currentRowNumber += 4;
        } else {
            $currentRowNumber += 4;
        }
    
        $sheet->getStyleByColumnAndRow($minColumnNumber, $currentRowNumber, $maxColumnNumber, $currentRowNumber)
            ->getAlignment()->setWrapText(true);
    
        $sheet->getRowDimension($currentRowNumber)->setRowHeight(50);
    
        $sheet->getStyleByColumnAndRow($minColumnNumber, $currentRowNumber, $maxColumnNumber, $currentRowNumber)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
        $sheet->getStyleByColumnAndRow($minColumnNumber, $currentRowNumber, $maxColumnNumber, $currentRowNumber)
            ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    
        for ($i = 0; $i < count($names); $i++) {
            $sheet->setCellValueByColumnAndRow($minColumnNumber + $i, $currentRowNumber, $names[$i]);
        }
    
        $sheet->getStyleByColumnAndRow($minColumnNumber, $currentRowNumber, $maxColumnNumber, $currentRowNumber)
            ->getBorders()->getAllBorders()->setBorderStyle(true);
    
        $currentRowNumber += 1;
    
        foreach ($pricelist['location'] as $location) {
            if (empty($location['filterA'])) {
                continue;
            }
        
            foreach ($location['filterA'] as $filterA) {
                if (empty($filterA['filterB'])) {
                    continue;
                }
            
                foreach ($filterA['filterB'] as $filterB) {
                    if (empty($filterB['prefixPriceNoLimit'])) {
                        continue;
                    }
    
                    if (!empty($filterB['description'])) {
                        $filterBText = trim($filterB['description']);
                        $filterBCount = 0;
                    } else {
                        $filterBText = '';
                        $filterBTextArray = [];
        
                        if (isset($filterB['nnp_country_name_eng'])) {
                            $filterBTextArray = array_merge($filterBTextArray, explode(', ', $filterB['nnp_country_name_eng']));
                        }
        
                        if (isset($filterB['nnp_ndc_type_name'])) {
                            $filterBTextArray = array_merge($filterBTextArray, explode(', ', $filterB['nnp_ndc_type_name']));
                        }
        
                        if (isset($filterB['nnp_operator_name'])) {
                            $filterBTextArray = array_merge($filterBTextArray, explode(', ', $filterB['nnp_operator_name']));
                        }
        
                        if (isset($filterB['nnp_region_name'])) {
                            $filterBTextArray = array_merge($filterBTextArray, explode(', ', $filterB['nnp_region_name']));
                        }
        
                        if (isset($filterB['nnp_city_name'])) {
                            $filterBTextArray = array_merge($filterBTextArray, explode(', ', $filterB['nnp_city_name']));
                        }
        
                        $filterBCount = 0;
                        foreach ($filterBTextArray as $item) {
                            if (strlen($filterBText) > 100 * (1 + $filterBCount)) {
                                $filterBText .= "\n" . $item;
                                $filterBCount++;
                            } else {
                                $filterBText .= " " . $item;
                            }
                        }
        
                        $filterBText = trim($filterBText);
                    }
                
                    if (empty($filterBText)) {
                        $filterBText = 'Пустой фильтр B';
                    }
                    
                    $filterBStartRowNumber = $currentRowNumber;
                
                    $simplifiedPrefixList = [];
                
                    foreach ($filterB['prefixPriceNoLimit'] as $prefixPrice) {
                        if (array_key_exists($prefixPrice['prefix_b'], $simplifiedPrefixList)) {
                            $simplifiedPrefixList[$prefixPrice['prefix_b']][] = $prefixPrice;
                        } else {
                            $simplifiedPrefixList[$prefixPrice['prefix_b']] = [];
                            $simplifiedPrefixList[$prefixPrice['prefix_b']][] = $prefixPrice;
                        }
                    }
                
                    foreach ($simplifiedPrefixList as $prefixPrice) {
                        $sheet->setCellValueByColumnAndRow($minColumnNumber + 1, $currentRowNumber, $prefixPrice[0]['prefix_b']);
                        $sheet->setCellValueByColumnAndRow($minColumnNumber + 2, $currentRowNumber, $pricelist['currency_id']);
                        $sheet->setCellValueByColumnAndRow($minColumnNumber + 3, $currentRowNumber, $prefixPrice[0]['b_number_price']);
                    
                        if (isset($prefixPrice[1])) {
                            $direction = $prefixPrice[1]['b_number_price'] > $prefixPrice[0]['b_number_price'] ? 'Повышение' : 'Понижение';
                            $sheet->setCellValueByColumnAndRow($minColumnNumber + 4, $currentRowNumber, $prefixPrice[1]['b_number_price']);
                            $sheet->setCellValueByColumnAndRow($minColumnNumber + 5, $currentRowNumber, $prefixPrice[1]['date_from']);
                            $sheet->setCellValueByColumnAndRow($minColumnNumber + 6, $currentRowNumber, $direction);
                        
                            if (isset($prefixPrice[2])) {
                                $direction = $prefixPrice[2]['b_number_price'] > $prefixPrice[1]['b_number_price'] ? 'Повышение' : 'Понижение';
                                $sheet->setCellValueByColumnAndRow($minColumnNumber + 7, $currentRowNumber, $prefixPrice[2]['b_number_price']);
                                $sheet->setCellValueByColumnAndRow($minColumnNumber + 8, $currentRowNumber, $prefixPrice[2]['date_from']);
                                $sheet->setCellValueByColumnAndRow($minColumnNumber + 9, $currentRowNumber, $direction);
                            }
                        }
                    
                        $sheet->setCellValueByColumnAndRow($minColumnNumber + 10, $currentRowNumber, $prefixPrice[0]['date_from']);
                    
                        $currentRowNumber++;
                    }
                
                    $filterBEndRowNumber = $currentRowNumber - 1;
    
                    for ($i = $filterBStartRowNumber; $i <= $filterBEndRowNumber; $i++) {
                        $sheet->setCellValueByColumnAndRow($minColumnNumber, $i, $filterBText);
                        
                        if ($filterBCount > 0) {
                            $sheet->getRowDimension($i)->setRowHeight(15 * ($filterBCount + 1));
                        }
                    }
                    
                    
                    $sheet->getStyleByColumnAndRow($minColumnNumber, $filterBStartRowNumber, $minColumnNumber, $filterBEndRowNumber)
                        ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                }
            }
        }
    
        $sheet->getStyleByColumnAndRow($minColumnNumber, $currentRowNumber, $maxColumnNumber, $currentRowNumber)
            ->getFont()->setName('Times New Roman');
    
        $countryNames = array_unique($countryNames);
        sort($countryNames);
    
        $sheet->getStyleByColumnAndRow($minColumnNumber, $currentRowNumber, $maxColumnNumber, $currentRowNumber)
            ->getFont()->setName('Times New Roman');
    
        $spreadsheet->createSheet();
    
        $sheet = $spreadsheet->getSheet(2);
        $sheet->setTitle('Страны');
        $currentRowNumber = 1;
    
        foreach ($countryNames as $name) {
            $sheet->setCellValueByColumnAndRow(1, $currentRowNumber, $name);
            $currentRowNumber++;
        }
    
        $spreadsheet->setActiveSheetIndex(0);
        
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="file.xlsx"');
        $writer->save('php://output');
    }
    
    private function createExcelPrefixesDocument($pricelist)
    {
        $apiUrl = 'http://reg10.mcntelecom.ru:8032/test/nnpcalc?';
        $maxColumnNumber = 3;
        
        $spreadsheet = new Spreadsheet();
        
        $firstSheetFilled = false;
        
        foreach ($pricelist['location'] as $location) {
            if (empty($location['filterA'])) {
                continue;
            }
    
            foreach ($location['filterA'] as $filterA) {
                $apiParams = [
                    'cmd' => 'annotatePricelistv2',
                    'id' => $filterA['id']
                ];
    
                $request = $apiUrl . http_build_query($apiParams);
    
                $response = file_get_contents($request);
                
                if (empty($response)) {
                    continue;
                }
    
                $processedResponse = explode("\n", $response);
    
                if (!$firstSheetFilled) {
                    $sheet = $spreadsheet->getActiveSheet();
                    $firstSheetFilled = true;
                } else {
                    $sheet = $spreadsheet->createSheet();
                }
    
                for ($i = 0; $i < $maxColumnNumber; $i++) {
                    $sheet->getColumnDimensionByColumn($i + 1)->setAutoSize(true);
                }
    
                $filterAHeader = trim((isset($filterA['nnp_country_name']) ? ($filterA['nnp_country_name']) : '') .
                    (isset($filterA['nnp_ndc_type_name']) ? (' ' . $filterA['nnp_ndc_type_name']) : '') .
                    (isset($filterA['nnp_operator_name']) ? (' ' . $filterA['nnp_operator_name']) : '') .
                    (isset($filterA['nnp_region_name']) ? (' ' . $filterA['nnp_region_name']) : '') .
                    (isset($filterA['nnp_city_name']) ? (' ' . $filterA['nnp_country_name']) : ''));
    
                if (empty($filterAHeader)) {
                    $filterAHeader = 'Пустой фильтр A';
                }
                
                if (mb_strlen($filterAHeader) > 31) {
                    $filterAHeader = mb_substr($filterAHeader, 0, 28) . '...';
                }
    
                $sheet->setTitle($filterAHeader);
    
                $row = 1;
    
                foreach ($processedResponse as $item) {
                    $itemArray = explode(',', $item);
        
                    for ($i = 0; $i < $maxColumnNumber; $i++) {
                        if (isset($itemArray[$i])) {
                            $sheet->setCellValueByColumnAndRow($i + 1, $row, trim(strip_tags($itemArray[$i])));
                        }
                    }
        
                    $row++;
                }
            }
        }
                
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="file.xlsx"');
        $writer->save('php://output');
    }
    
    private function createExcelLocationsDocument($pricelist)
    {
        $rowNumber = 1;
        $maxColumnNumber = 4;
    
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        for ($i = 0; $i < $maxColumnNumber; $i++) {
            $sheet->getColumnDimensionByColumn($i + 1)->setAutoSize(true);
        }
    
        $sheet->setCellValueByColumnAndRow(1, $rowNumber, 'Страна');
        $sheet->setCellValueByColumnAndRow(2, $rowNumber, 'Код оператора (MNC)');
        $sheet->setCellValueByColumnAndRow(3, $rowNumber, 'Название оператора');
        $sheet->setCellValueByColumnAndRow(4, $rowNumber, 'Стоимость');
    
        $rowNumber++;
        
        foreach ($pricelist['location'] as $location) {
            $sheet->setCellValueByColumnAndRow(1, $rowNumber, $location['mcc_string']);
            $sheet->setCellValueByColumnAndRow(2, $rowNumber, $location['mnc_string']);
            $sheet->setCellValueByColumnAndRow(3, $rowNumber, $location['mnc_name_string']);
            $sheet->setCellValueByColumnAndRow(4, $rowNumber, $location['delta_price']);
    
            $rowNumber++;
        }
        
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="file.xlsx"');
        $writer->save('php://output');
    }
}
