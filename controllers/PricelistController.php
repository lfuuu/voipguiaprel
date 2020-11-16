<?php

namespace app\controllers;

use app\classes\BaseController;
use app\models\billing_uu\Pricelist;
use app\models\billing_uu\PricelistPrefixPrice;
use app\models\Server;
use PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use \PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use yii\web\ForbiddenHttpException;
Use app\classes\traits\PricelistView;
use yii\db\Query;

class PricelistController extends BaseController
{
    use PricelistView;
    
    private $_locations = [
        1 => 'Домашний регион',
        2 => 'Гостевой регион',
        3 => 'Международный регион'
    ];
    
    public function actionIndex($pricelistId)
    {
        $pricelist = Pricelist::find()
            ->with('location.filterA.filterB.prefixPriceNoLimit')
            ->where(['id' => $pricelistId])
            ->asArray()
            ->one();

        $mccIdArray = [];
        $simImsiPartnerIdArray = [];
        $simImsiProfileIdArray = [];
        $nnpCountryIdArray = [];
        $nnpDestinationIdArray = [];
        $nnpOperatorIdArray = [];
        $nnpRegionIdArray = [];
        $nnpCityIdArray = [];
        $nnpNdcTypeIdArray = [];

        $idArrays = [
            'nnp.mcc' => ['ids' => &$mccIdArray, 'name_field' => 'country', 'id_field' => 'mcc'],
            'billing_uu.sim_imsi_profile' => ['ids' => &$simImsiProfileIdArray, 'name_field' => 'name', 'id_field' => 'id'],
            'billing_uu.sim_imsi_partner' => ['ids' => &$simImsiPartnerIdArray, 'name_field' => 'name', 'id_field' => 'id'],
            'nnp.country' => ['ids' => &$nnpCountryIdArray, 'name_field' => 'name_rus', 'id_field' => 'code'],
            'nnp.destination' => ['ids' => &$nnpDestinationIdArray, 'name_field' => 'name', 'id_field' => 'id'],
            'nnp.operator' => ['ids' => &$nnpOperatorIdArray, 'name_field' => 'name', 'id_field' => 'id'],
            'nnp.region' => ['ids' => &$nnpRegionIdArray, 'name_field' => 'name', 'id_field' => 'id'],
            'nnp.city' => ['ids' => &$nnpCityIdArray, 'name_field' => 'name', 'id_field' => 'id'],
            'nnp.ndc_type' => ['ids' => &$nnpNdcTypeIdArray, 'name_field' => 'name', 'id_field' => 'id']
        ];

        foreach ($pricelist['location'] as $location) {
            self::processQueryArray($mccIdArray, $location['mcc']);
            self::processQueryArray($simImsiPartnerIdArray, $location['sim_partner']);
            self::processQueryArray($simImsiProfileIdArray, $location['sim_profile']);
            
            foreach ($location['filterA'] as $filterA) {
                self::processQueryArray($nnpCountryIdArray, $filterA['nnp_country']);
                self::processQueryArray($nnpDestinationIdArray, $filterA['nnp_destination']);
                self::processQueryArray($nnpOperatorIdArray, $filterA['nnp_operator']);
                self::processQueryArray($nnpRegionIdArray, $filterA['nnp_region']);
                self::processQueryArray($nnpCityIdArray, $filterA['nnp_city']);
                self::processQueryArray($nnpNdcTypeIdArray, $filterA['nnp_ndc_type']);
                
                foreach ($filterA['filterB'] as $filterB) {
                    self::processQueryArray($nnpCountryIdArray, $filterB['nnp_country']);
                    self::processQueryArray($nnpDestinationIdArray, $filterB['nnp_destination']);
                    self::processQueryArray($nnpOperatorIdArray, $filterB['nnp_operator']);
                    self::processQueryArray($nnpRegionIdArray, $filterB['nnp_region']);
                    self::processQueryArray($nnpCityIdArray, $filterB['nnp_city']);
                    self::processQueryArray($nnpNdcTypeIdArray, $filterB['nnp_ndc_type']);
                }
            }
        }
        
        foreach ($idArrays as $key => &$item) {
            $item['ids'] = array_unique($item['ids']);
            
            if (count($item['ids'])) {
                $tempIds = (new Query())->select(['id' => $item['id_field'], 'name' => $item['name_field']])->from($key)->where([$item['id_field'] => $item['ids']])->all();
                $item['ids'] = [];
                foreach ($tempIds as $tempId) {
                    $item['ids'][$tempId['id']] = $tempId['name'];
                }
            }
        }
        
        foreach ($pricelist['location'] as &$location) {
            $isBasic = ($location['id'] == $pricelist['basic_pricelist_location_id']);
            $location['text'] = self::formLocationText($location, $isBasic, $idArrays, '');
            
            foreach ($location['filterA'] as &$filterA) {
                $filterA['prefix_count'] = 0;
                $filterA['text'] = self::formFilterText($filterA, '', $idArrays);
                
                foreach ($filterA['filterB'] as &$filterB) {
                    $filterB['text'] = self::formFilterText($filterB, '', $idArrays);
                    $filterB['prefixes'] = [];
                    
                    foreach ($filterB['prefixPriceNoLimit'] as $prefixPrice) {
                        if (!isset($filterB['prefixes'][$prefixPrice['prefix_b']])) {
                            $filterB['prefixes'][$prefixPrice['prefix_b']] = [];
                        }
                        
                        $filterB['prefixes'][$prefixPrice['prefix_b']][] = $prefixPrice;
                    }
                    
                    $filterB['prefix_count'] = count($filterB['prefixes']);
                    $filterA['prefix_count'] += count($filterB['prefixes']);
                }
            }
        }
        
        return $this->render('index', [
            'pricelist' => $pricelist
        ]);
    }
    
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

        set_time_limit(0);
        
        $data = Pricelist::find()
            ->with('location.filterA.filterB.prefixPriceNoLimit')
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
                    
                    if ($filterBEndRowNumber > $filterBStartRowNumber) {
                        $sheet->mergeCellsByColumnAndRow($minColumnNumber + 1, $filterBStartRowNumber, $minColumnNumber + 1,
                            $filterBEndRowNumber);
                        $sheet->mergeCellsByColumnAndRow($minColumnNumber + 2, $filterBStartRowNumber, $minColumnNumber + 2,
                            $filterBEndRowNumber);
                    }
                    
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
                if ($filter['f_inv_nnp_country']) {
                    $filterTextArray[] = 'Except:';
                }

                $filterTextArray[] = $filter['nnp_country_name_eng'];
            }
        
            if (isset($filter['nnp_ndc_type_name'])) {
                if ($filter['f_inv_nnp_ndc_type']) {
                    $filterTextArray[] = 'Except:';
                }

                $filterTextArray[] = $filter['nnp_ndc_type_name'];
            }
        
            if (isset($filter['nnp_operator_name'])) {
                if ($filter['f_inv_nnp_operator']) {
                    $filterTextArray[] = 'Except:';
                }

                $filterTextArray[] = $filter['nnp_operator_name'];
            }
        
            if (isset($filter['nnp_region_name'])) {
                if ($filter['f_inv_nnp_region']) {
                    $filterTextArray[] = 'Except:';
                }

                $filterTextArray[] = $filter['nnp_region_name'];
            }
        
            if (isset($filter['nnp_city_name'])) {
                if ($filter['f_inv_nnp_city']) {
                    $filterTextArray[] = 'Except:';
                }

                $filterTextArray[] = $filter['nnp_city_name'];
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

    private function isFilterEmpty($filter)
    {
        if ($filter['nnp_country'] != '{}' || ($filter['nnp_ndc'] != '{}' && !empty($filter['nnp_ndc'])) || 
            $filter['nnp_operator'] != '{}' || $filter['nnp_region'] != '{}' || 
            $filter['nnp_city'] != '{}' || $filter['nnp_ndc_type'] != '{}' || 
            $filter['f_inv_nnp_country'] || $filter['f_inv_nnp_ndc'] || 
            $filter['f_inv_nnp_operator'] || $filter['f_inv_nnp_region'] || 
            $filter['f_inv_nnp_city'] || $filter['f_inv_nnp_ndc_type']) {
            return false;
        }
        
        return true;
    }
    
    private function createCountriesSheet(&$spreadsheet, $countryNames)
    {
        $spreadsheet->createSheet();
        
        $sheet = $spreadsheet->getSheet(1);
        $sheet->setTitle('Countries EU');
        $currentRowNumber = 1;
        
        if (!empty($countryNames)) {
            $countryNames = array_unique($countryNames);
            sort($countryNames);
            
            foreach ($countryNames as $name) {
                $sheet->setCellValueByColumnAndRow(1, $currentRowNumber, $name);
                $currentRowNumber++;
            }
        }
    }

    private function createExcelPrefixesNewDocument($pricelist, $minimize, $use_ranges)
    {
        $apiUrl = 'http://reg10.mcntelecom.ru:8032/test/nnpcalc?';
        $maxColumnNumber = 4;
        
        $spreadsheet = new Spreadsheet();
        
        $firstSheetFilled = false;
        
        foreach ($pricelist['location'] as $location) {
            if (empty($location['filterA'])) {
                continue;
            }

            foreach ($location['filterA'] as $filterA) {
                $prefixes = PricelistPrefixPrice::getGroupedByPrice($filterA['id']);
                
                if (empty($prefixes)) {
                    continue;
                }

                $doRequest = false;

                foreach ($filterA['filterB'] as $filterB) {
                    if (!$this->isFilterEmpty($filterB)) {
                        $doRequest = true;
                        break;
                    }
                }

                if ($doRequest) {
                    $this->fillPrefixesNewSpreadSheetFromRequest($filterA, $minimize, $use_ranges, $apiUrl, $firstSheetFilled, 
                                                                 $spreadsheet, $maxColumnNumber, $prefixes);
                } else {
                    $this->fillPrefixesNewSpreadSheetFromFilter($filterA, $minimize, $use_ranges, $apiUrl, $firstSheetFilled, 
                                                                 $spreadsheet, $maxColumnNumber);
                }
            }
        }
        
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="file.xlsx"');
        $writer->save('php://output');
    }

    private function fillPrefixesNewSpreadSheetFromRequest($filterA, $minimize, $use_ranges, $apiUrl, &$firstSheetFilled, 
                                                &$spreadsheet, $maxColumnNumber, $prefixes)
    {
        $apiParams = [
            'cmd' => 'annotatePricelistv2',
            'id' => $filterA['id'],
            'minimize' => $minimize == 'true' ? 1 : 0,
            'use_ranges' => $use_ranges == 'true' ? 1 : 0
        ];
        
        $request = $apiUrl . http_build_query($apiParams);
        
        ini_set('default_socket_timeout', 600);
        ini_set('memory_limit', '-1');
        $response = file_get_contents($request);
        
        if (empty($response)) {
            return;
        }

        $filterBDescriptionArray = [];

        foreach ($filterA['filterB'] as $filterB) {
            $filterBDescriptionArray[$filterB['id']] = $this->getFilterName($filterB);
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

        $sheet->getStyle('C:C')->getNumberFormat()->setFormatCode('0.000000');

        foreach ($processedResponse as $item) {
            $itemArray = explode(',', $item);

            if (isset($itemArray[1])) {
                $key = trim(strip_tags($itemArray[1]));

                if (empty($prefixes) || !isset($prefixes[$key])) {
                    $filterText = $this->getFilterName($filterB)[0];
                    $sheet->setCellValueByColumnAndRow(1, $row, $filterText);
                } else {
                    if (!empty($prefixes[$key]['description'])) {
                        $sheet->setCellValueByColumnAndRow(1, $row, $prefixes[$key]['description']);
                    } else {
                        $descriptionArray = [];

                        foreach (explode(',', $prefixes[$key]['ids']) as $filterBId) {
                            $descriptionArray[] = $filterBDescriptionArray[$filterBId][0];
                        }

                        $description = implode('; ', $descriptionArray);
                        $sheet->setCellValueByColumnAndRow(1, $row, $description);
                    }
                }
            }

            for ($i = 1; $i < $maxColumnNumber; $i++) {
                if (isset($itemArray[$i - 1])) {
                    $sheet->setCellValueByColumnAndRow($i + 1, $row, trim(strip_tags($itemArray[$i - 1])));
                }
            }

            if (isset($itemArray[1])) {
                if (empty($prefixes) || !isset($prefixes[trim(strip_tags($itemArray[1]))])) {
                    $sheet->setCellValueByColumnAndRow(4, $row, $filterA['description']);
                } else {
                    $sheet->setCellValueByColumnAndRow(4, $row, $prefixes[trim(strip_tags($itemArray[1]))]['date_from']);
                }
            }
            
            $row++;
        }
    }

    private function fillPrefixesNewSpreadSheetFromFilter($filterA, $minimize, $use_ranges, $apiUrl, &$firstSheetFilled, 
                                                &$spreadsheet, $maxColumnNumber)
    {
        $filterBDescriptionArray = [];

        foreach ($filterA['filterB'] as $filterB) {
            $filterBDescriptionArray[$filterB['id']] = $this->getFilterName($filterB);
        }

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

        foreach ($filterA['filterB'] as $filterB) {
            foreach ($filterB['prefixPriceNoLimit'] as $prefixPrice) {
                $sheet->setCellValueByColumnAndRow(1, $row, $filterB['description']);
                $sheet->setCellValueByColumnAndRow(2, $row, $prefixPrice['prefix_b']);
                $sheet->setCellValueByColumnAndRow(3, $row, $prefixPrice['b_number_price']);
                $sheet->setCellValueByColumnAndRow(4, $row, $prefixPrice['date_from']);

                $row++;
            }
        }
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
                
                ini_set('default_socket_timeout', 600);
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
