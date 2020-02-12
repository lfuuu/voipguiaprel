<?php

namespace app\controllers;

use app\classes\BaseController;
use app\models\billing_uu\Pricelist;
use app\models\Server;
use PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use \PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use yii\web\ForbiddenHttpException;

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

    public function actionExcelSingleLine($id)
    {
        if (!\Yii::$app->user->can('pricelist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $data = Pricelist::find()
            ->with('location.filterA.filterB.prefixPriceNoLimit')
            ->where(['id' => $id])
            ->asArray()
            ->one();

        $this->createExcelSingleLineDocument($data);
    }

    public function actionExcelFilterB($id)
    {
        if (!\Yii::$app->user->can('pricelist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $data = Pricelist::find()
            ->with('location.filterA.filterB.prefixPriceNoLimit')
            ->where(['id' => $id])
            ->asArray()
            ->one();

        $this->createExcelFilterBDocument($data);
    }

    public function actionExcelPrefixesNew($id, $minimize, $use_ranges)
    {
        if (!\Yii::$app->user->can('pricelist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $data = Pricelist::find()
            ->with('location.filterA')
            ->where(['id' => $id])
            ->asArray()
            ->one();
        
        $this->createExcelPrefixesNewDocument($data, $minimize, $use_ranges);
    }
    
    private function createExcelDocument($pricelist)
    {
        $spreadsheet = new Spreadsheet();

        $countryNames = $this->createPricelistSheet($spreadsheet, $pricelist);
        $this->createCountriesSheet($spreadsheet, $countryNames);
        
        $spreadsheet->setActiveSheetIndex(0);
        
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="file.xlsx"');
        $writer->save('php://output');
    }

    private function createExcelFilterBDocument($pricelist)
    {
        $spreadsheet = new Spreadsheet();

        $countryNames = $this->createPricelistFilterBSheet($spreadsheet, $pricelist);
        $this->createCountriesSheet($spreadsheet, $countryNames);

        $spreadsheet->setActiveSheetIndex(0);

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="file.xlsx"');
        $writer->save('php://output');
    }

    private function createExcelSingleLineDocument($pricelist)
    {
        $spreadsheet = new Spreadsheet();

        $this->createSingleLineSheet($spreadsheet, $pricelist);

        $spreadsheet->setActiveSheetIndex(0);

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="single_line.xlsx"');
        $writer->save('php://output');
    }
    
    private function createPricelistSheet(&$spreadsheet, $pricelist)
    {
        $apiUrl = 'http://reg10.mcntelecom.ru:8032/test/nnpcalc?';
        $names = [
            "Source country filter",
            "Destination",
            "Rating",
            "Prefix",
            "Price",
            "Сurrency",
            "Pending price",
            "Pending date",
            "Status",
            "Effective date",
//            "Info"
        ];
        
        $currentRowNumber = 1;
        $minColumnNumber = 1;
        $maxColumnNumber = count($names);
        
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('General');
        
        $currentRowNumber = $this->setHeader($sheet, $names, $pricelist, $minColumnNumber, $maxColumnNumber,
            $currentRowNumber);
        
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

//                $apiParams = [
//                    'cmd' => 'annotatePricelistv2',
//                    'id' => $filterA['id']
//                ];
//
//                $request = $apiUrl . http_build_query($apiParams);
//
//                $response = file_get_contents($request);
//
//                if (empty($response)) {
//                    continue;
//                }
//
//                $processedResponse = $this->processAnnotateResponse($response);

                if (isset($filterA['nnp_country_name_eng'])) {
                    $countryNames = array_merge($countryNames, explode(', ', $filterA['nnp_country_name_eng']));
                }
    
                list($filterAHeader, $filterACount) = $this->getFilterName($filterA);
                
                if (empty($filterAHeader)) {
                    $filterAHeader = 'Empty filter';
                }
                
                $sheet->setCellValueByColumnAndRow($minColumnNumber, $currentRowNumber, $filterAHeader);
                $filterAStartRowNumber = $currentRowNumber;
                
                foreach ($filterA['filterB'] as $filterB) {
                    if (empty($filterB['prefixPriceNoLimit'])) {
                        continue;
                    }
    
                    list($filterBText, $filterBCount) = $this->getFilterName($filterB);
                    
                    if (empty($filterBText)) {
                        $filterBText = 'Empty filter';
                    }
                    
                    $sheet->setCellValueByColumnAndRow($minColumnNumber + 1, $currentRowNumber, $filterBText);
                    if ($filterB['rating'] != 1) {
                        $sheet->setCellValueByColumnAndRow($minColumnNumber + 2, $currentRowNumber, $filterB['rating']);
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
                        $sheet->setCellValueByColumnAndRow($minColumnNumber + 3, $currentRowNumber,
                            $prefixPrice[0]['prefix_b']);
                        $sheet->setCellValueByColumnAndRow($minColumnNumber + 4, $currentRowNumber,
                            $prefixPrice[0]['b_number_price']);
                        $sheet->setCellValueByColumnAndRow($minColumnNumber + 5, $currentRowNumber,
                            $pricelist['currency_id']);
                        
                        if (isset($prefixPrice[1])) {
                            $direction = $prefixPrice[1]['b_number_price'] > $prefixPrice[0]['b_number_price'] ? 'Increase' : 'Decrease';
                            $sheet->setCellValueByColumnAndRow($minColumnNumber + 6, $currentRowNumber,
                                $prefixPrice[1]['b_number_price']);
                            $sheet->setCellValueByColumnAndRow($minColumnNumber + 7, $currentRowNumber,
                                $prefixPrice[1]['date_from']);
                            $sheet->setCellValueByColumnAndRow($minColumnNumber + 8, $currentRowNumber, $direction);
                        }
                        
                        $sheet->setCellValueByColumnAndRow($minColumnNumber + 9, $currentRowNumber,
                            $prefixPrice[0]['date_from']);
                        
                        $currentRowNumber++;
                    }
                    
                    $filterBEndRowNumber = $currentRowNumber - 1;
                    $sheet->mergeCellsByColumnAndRow($minColumnNumber + 1, $filterBStartRowNumber, $minColumnNumber + 1,
                        $filterBEndRowNumber);
                    $sheet->mergeCellsByColumnAndRow($minColumnNumber + 2, $filterBStartRowNumber, $minColumnNumber + 2,
                        $filterBEndRowNumber);
                    
                    if (($filterBEndRowNumber - $filterBStartRowNumber) < $filterBCount) {
                        $sheet->getRowDimension($filterBStartRowNumber)->setRowHeight(15 * ($filterBCount - $filterBEndRowNumber + $filterBStartRowNumber + 1));
                    }
                    
                    $sheet->getStyleByColumnAndRow($minColumnNumber + 1, $filterBStartRowNumber, $minColumnNumber + 1,
                        $filterBEndRowNumber)
                        ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyleByColumnAndRow($minColumnNumber + 2, $filterBStartRowNumber, $minColumnNumber + 2,
                        $filterBEndRowNumber)
                        ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                }
                
                if ($filterAStartRowNumber == $currentRowNumber) {
                    $filterAEndRowNumber = $currentRowNumber;
                } else {
                    $filterAEndRowNumber = $currentRowNumber - 1;
                }
                
                $sheet->mergeCellsByColumnAndRow($minColumnNumber, $filterAStartRowNumber, $minColumnNumber,
                    $filterAEndRowNumber);
                
                if (($filterAEndRowNumber - $filterAStartRowNumber) < $filterACount) {
                    $sheet->getRowDimension($filterAStartRowNumber)->setRowHeight(15 * ($filterACount - $filterAEndRowNumber + $filterAStartRowNumber + 1));
                }
                
                $sheet->getStyleByColumnAndRow($minColumnNumber, $filterAStartRowNumber, $minColumnNumber,
                    $filterAEndRowNumber)
                    ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

//                $sheet->mergeCellsByColumnAndRow($minColumnNumber + 10, $filterAStartRowNumber, $minColumnNumber + 10,
//                    $filterAEndRowNumber);
//                $sheet->setCellValueByColumnAndRow($minColumnNumber + 10, $filterAStartRowNumber, $processedResponse);
//                $sheet->getStyleByColumnAndRow($minColumnNumber + 10, $filterAStartRowNumber, $minColumnNumber + 10,
//                    $filterAEndRowNumber)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
            }
        }

        return $countryNames;
    }

    private function createPricelistFilterBSheet(&$spreadsheet, $pricelist)
    {
        $apiUrl = 'http://reg10.mcntelecom.ru:8032/test/nnpcalc?';
        $names = [
            "Source country filter",
            "Destination",
            "Rating",
            "Prefix",
            "Price",
            "Сurrency",
            "Pending price",
            "Pending date",
            "Status",
            "Effective date",
            "Info"
        ];

        $currentRowNumber = 1;
        $minColumnNumber = 1;
        $maxColumnNumber = count($names);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('General');

        $currentRowNumber = $this->setHeader($sheet, $names, $pricelist, $minColumnNumber, $maxColumnNumber,
            $currentRowNumber);

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

                list($filterAHeader, $filterACount) = $this->getFilterName($filterA);

                if (empty($filterAHeader)) {
                    $filterAHeader = 'Empty filter';
                }

                $sheet->setCellValueByColumnAndRow($minColumnNumber, $currentRowNumber, $filterAHeader);
                $filterAStartRowNumber = $currentRowNumber;

                foreach ($filterA['filterB'] as $filterB) {
                    if (empty($filterB['prefixPriceNoLimit'])) {
                        continue;
                    }

                    $apiParams = [
                        'cmd' => 'getPricelistFilterBPrefix',
                        'id' => $filterB['id'],
                        'minimize' => 1
                    ];

                    $request = $apiUrl . http_build_query($apiParams);

                    $response = file_get_contents($request);

                    if (empty($response)) {
                        continue;
                    }

                    $processedResponse = $this->processFilterBResponse($response);

                    list($filterBText, $filterBCount) = $this->getFilterName($filterB);

                    if (empty($filterBText)) {
                        $filterBText = 'Empty filter';
                    }

                    $sheet->setCellValueByColumnAndRow($minColumnNumber + 1, $currentRowNumber, $filterBText);
                    if ($filterB['rating'] != 1) {
                        $sheet->setCellValueByColumnAndRow($minColumnNumber + 2, $currentRowNumber, $filterB['rating']);
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
                        $sheet->setCellValueByColumnAndRow($minColumnNumber + 3, $currentRowNumber,
                            $prefixPrice[0]['prefix_b']);
                        $sheet->setCellValueByColumnAndRow($minColumnNumber + 4, $currentRowNumber,
                            $prefixPrice[0]['b_number_price']);
                        $sheet->setCellValueByColumnAndRow($minColumnNumber + 5, $currentRowNumber,
                            $pricelist['currency_id']);

                        if (isset($prefixPrice[1])) {
                            $direction = $prefixPrice[1]['b_number_price'] > $prefixPrice[0]['b_number_price'] ? 'Increase' : 'Decrease';
                            $sheet->setCellValueByColumnAndRow($minColumnNumber + 6, $currentRowNumber,
                                $prefixPrice[1]['b_number_price']);
                            $sheet->setCellValueByColumnAndRow($minColumnNumber + 7, $currentRowNumber,
                                $prefixPrice[1]['date_from']);
                            $sheet->setCellValueByColumnAndRow($minColumnNumber + 8, $currentRowNumber, $direction);
                        }

                        $sheet->setCellValueByColumnAndRow($minColumnNumber + 9, $currentRowNumber,
                            $prefixPrice[0]['date_from']);

                        $currentRowNumber++;
                    }

                    $filterBEndRowNumber = $currentRowNumber - 1;
                    $sheet->mergeCellsByColumnAndRow($minColumnNumber + 1, $filterBStartRowNumber, $minColumnNumber + 1,
                        $filterBEndRowNumber);
                    $sheet->mergeCellsByColumnAndRow($minColumnNumber + 2, $filterBStartRowNumber, $minColumnNumber + 2,
                        $filterBEndRowNumber);

                    if (($filterBEndRowNumber - $filterBStartRowNumber) < $filterBCount) {
                        $sheet->getRowDimension($filterBStartRowNumber)->setRowHeight(15 * ($filterBCount - $filterBEndRowNumber + $filterBStartRowNumber + 1));
                    }

                    $sheet->getStyleByColumnAndRow($minColumnNumber + 1, $filterBStartRowNumber, $minColumnNumber + 1,
                        $filterBEndRowNumber)
                        ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyleByColumnAndRow($minColumnNumber + 2, $filterBStartRowNumber, $minColumnNumber + 2,
                        $filterBEndRowNumber)
                        ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                }

                if ($filterAStartRowNumber == $currentRowNumber) {
                    $filterAEndRowNumber = $currentRowNumber;
                } else {
                    $filterAEndRowNumber = $currentRowNumber - 1;
                }

                $sheet->mergeCellsByColumnAndRow($minColumnNumber, $filterAStartRowNumber, $minColumnNumber,
                    $filterAEndRowNumber);

                if (($filterAEndRowNumber - $filterAStartRowNumber) < $filterACount) {
                    $sheet->getRowDimension($filterAStartRowNumber)->setRowHeight(15 * ($filterACount - $filterAEndRowNumber + $filterAStartRowNumber + 1));
                }

                $sheet->getStyleByColumnAndRow($minColumnNumber, $filterAStartRowNumber, $minColumnNumber,
                    $filterAEndRowNumber)
                    ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            }
        }

        return $countryNames;
    }

    private function processAnnotateResponse($response)
    {
        $responseArray = explode("\n", $response);

        $tempResult = [];
        $processedResponse = [];

        foreach ($responseArray as $item) {
            if ($item == '') {
                continue;
            }

            $itemArray = explode(',', $item);

            $name = trim(strip_tags($itemArray[1]));
            $value = trim(strip_tags($itemArray[0]));
            if (array_key_exists($name, $tempResult)) {
                $tempResult[$name][] = $value;
            } else {
                $tempResult[$name] = [$value];
            }
        }

        foreach ($tempResult as $tempKey => $tempItem) {
            $processedResponse[] = sprintf("%01.6f", $tempKey) . ': ' . implode(', ', $tempItem);
        }

        $processedResponse = implode("\n", $processedResponse);

        return $processedResponse;
    }

    private function processFilterBResponse($response)
    {
        return 'test filter b response';
    }
    
    private function createSingleLineSheet(&$spreadsheet, $pricelist)
    {
        $spreadsheet->createSheet();
        
        $sheet = $spreadsheet->getSheet(0);
        $sheet->setTitle('Single line');
        
        $names = [
            "Destination",
            "Codes",
            "Price",
            "Currency",
            "Pending price",
            "Pending date",
            "Status",
            "Effective date"
        ];
        
        $currentRowNumber = 1;
        $minColumnNumber = 1;
        $maxColumnNumber = count($names);
        
        $currentRowNumber = $this->setHeader($sheet, $names, $pricelist, $minColumnNumber, $maxColumnNumber,
            $currentRowNumber);
        
        $currentRowNumber += 1;

        $apiUrl = 'http://reg10.mcntelecom.ru:8032/';
        
        $apiParams = [
            'cmd' => 'getPricelistv2Prefix',
            'id' => $pricelist['id'],
        ];
        
        $request = $apiUrl . 'test/nnpcalc?' . http_build_query($apiParams);
        
        $response = json_decode(file_get_contents($request), true);

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
                    
                    list($filterBText, $filterBCount) = $this->getFilterName($filterB);
                    
                    if (empty($filterBText)) {
                        $filterBText = 'Пустой фильтр B';
                    }
                    
                    $filterBStartRowNumber = $currentRowNumber;
                    
                    $prefixListFromResponse = isset($response[$filterB['id']]) ? $response[$filterB['id']] : [];
                    $simplifiedPrefixPriceList = [];
                    $flattenedPrefixPriceNoLimit = [];

                    foreach ($filterB['prefixPriceNoLimit'] as $prefixPrice) {
                        if (array_key_exists($prefixPrice['prefix_b'], $flattenedPrefixPriceNoLimit)) {
                            $flattenedPrefixPriceNoLimit[$prefixPrice['prefix_b']][] = $prefixPrice;
                        } else {
                            $flattenedPrefixPriceNoLimit[$prefixPrice['prefix_b']] = [];
                            $flattenedPrefixPriceNoLimit[$prefixPrice['prefix_b']][] = $prefixPrice;
                        }
                    }
                    
                    krsort($flattenedPrefixPriceNoLimit);
                    
                    foreach ($prefixListFromResponse as $prefix) {
                        foreach ($flattenedPrefixPriceNoLimit as $prefixPriceKey => $prefixPrice) {
                            if ($prefixPriceKey == '') {
                                $simplifiedPrefixPriceList[$prefix] = $prefixPrice;
                            } elseif (substr($prefix, 0, strlen($prefixPriceKey)) == $prefixPriceKey) {
                                $simplifiedPrefixPriceList[$prefix] = $prefixPrice;
                            }
                        }
                    }
                    
                    foreach ($simplifiedPrefixPriceList as $prefixPriceKey => $prefixPrice) {
                        $sheet->setCellValueByColumnAndRow($minColumnNumber + 1, $currentRowNumber, $prefixPriceKey);
                        $sheet->setCellValueByColumnAndRow($minColumnNumber + 2, $currentRowNumber,
                            $prefixPrice[0]['b_number_price']);
                        $sheet->setCellValueByColumnAndRow($minColumnNumber + 3, $currentRowNumber,
                            $pricelist['currency_id']);
                        
                        if (isset($prefixPrice[1])) {
                            $direction = $prefixPrice[1]['b_number_price'] > $prefixPrice[0]['b_number_price'] ? 'Increase' : 'Decrease';
                            $sheet->setCellValueByColumnAndRow($minColumnNumber + 4, $currentRowNumber,
                                $prefixPrice[1]['b_number_price']);
                            $sheet->setCellValueByColumnAndRow($minColumnNumber + 5, $currentRowNumber,
                                $prefixPrice[1]['date_from']);
                            $sheet->setCellValueByColumnAndRow($minColumnNumber + 6, $currentRowNumber, $direction);
                        }
                        
                        $sheet->setCellValueByColumnAndRow($minColumnNumber + 7, $currentRowNumber,
                            $prefixPrice[0]['date_from']);
                        
                        $currentRowNumber++;
                    }
                    
                    $filterBEndRowNumber = $currentRowNumber - 1;
                    
                    for ($i = $filterBStartRowNumber; $i <= $filterBEndRowNumber; $i++) {
                        $sheet->setCellValueByColumnAndRow($minColumnNumber, $i, $filterBText);
                        
                        if ($filterBCount > 0) {
                            $sheet->getRowDimension($i)->setRowHeight(15 * ($filterBCount + 1));
                        }
                    }
                    
                    $sheet->getStyleByColumnAndRow($minColumnNumber, $filterBStartRowNumber, $minColumnNumber,
                        $filterBEndRowNumber)
                        ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                }
            }
        }
    }
    
    private function setHeader(&$sheet, $names, $pricelist, $minColumnNumber, $maxColumnNumber, $currentRowNumber)
    {
        for ($i = 0; $i < $maxColumnNumber; $i++) {
            $sheet->getColumnDimensionByColumn($i + 1)->setAutoSize(true);
        }
        
        Cell::setValueBinder(new AdvancedValueBinder());
        
        $pricelistHeader = $pricelist['name'];
        $sheet->setCellValueByColumnAndRow($minColumnNumber, $currentRowNumber, $pricelistHeader);
        $sheet->getRowDimension($currentRowNumber)->setRowHeight(30);
        $sheet->getStyleByColumnAndRow($minColumnNumber, $currentRowNumber, $minColumnNumber, $currentRowNumber)
            ->getFont()->setSize(22);
        $sheet->mergeCellsByColumnAndRow($minColumnNumber, $currentRowNumber, $maxColumnNumber,
            $currentRowNumber);
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
        
        return $currentRowNumber;
    }
    
    private function getFilterName($filter)
    {
        if (!empty($filter['description'])) {
            $filterText = trim($filter['description']);
            $filterCount = 0;
        } else {
            $filterText = '';
            $filterTextArray = [];
        
            if (isset($filter['nnp_country_name_eng'])) {
                $filterTextArray = array_merge($filterTextArray,
                    explode(', ', $filter['nnp_country_name_eng']));
            }
        
            if (isset($filter['nnp_ndc_type_name'])) {
                $filterTextArray = array_merge($filterTextArray,
                    explode(', ', $filter['nnp_ndc_type_name']));
            }
        
            if (isset($filter['nnp_operator_name'])) {
                $filterTextArray = array_merge($filterTextArray,
                    explode(', ', $filter['nnp_operator_name']));
            }
        
            if (isset($filter['nnp_region_name'])) {
                $filterTextArray = array_merge($filterTextArray,
                    explode(', ', $filter['nnp_region_name']));
            }
        
            if (isset($filter['nnp_city_name'])) {
                $filterTextArray = array_merge($filterTextArray,
                    explode(', ', $filter['nnp_city_name']));
            }
        
            $filterCount = 0;
            foreach ($filterTextArray as $item) {
                if (strlen($filterText) > 100 * (1 + $filterCount)) {
                    $filterText .= "\n" . $item;
                    $filterCount++;
                } else {
                    $filterText .= " " . $item;
                }
            }
        
            $filterText = trim($filterText);
        }
        
        return array($filterText, $filterCount);
    }
    
    private function createCountriesSheet(&$spreadsheet, $countryNames)
    {
        $spreadsheet->createSheet();
        
        $sheet = $spreadsheet->getSheet(1);
        $sheet->setTitle('Countries EU');
        $currentRowNumber = 1;
        
        $countryNames = array_unique($countryNames);
        sort($countryNames);
        
        foreach ($countryNames as $name) {
            $sheet->setCellValueByColumnAndRow(1, $currentRowNumber, $name);
            $currentRowNumber++;
        }
    }

    private function createExcelPrefixesNewDocument($pricelist, $minimize, $use_ranges)
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
                    'id' => $filterA['id'],
                    'minimize' => $minimize == 'true' ? 1 : 0,
                    'use_ranges' => $use_ranges == 'true' ? 1 : 0
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
                
                $filterAHeader = isset($filterA['description']) ? $filterA['description'] : (string)$filterA['id'];

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
