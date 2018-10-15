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
    
    private function createExcelDocument($pricelist)
    {
        $currentRowNumber = 1;
        $columnNameNumber = 1;
        $minColumnNumber = 1;
        $maxColumnNumber = 3;
        
        $spreadsheet = new Spreadsheet();
    
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
    
        Cell::setValueBinder(new AdvancedValueBinder());
        
//        $sheet->setCellValueByColumnAndRow($columnNameNumber, $currentRowNumber, 'Прайслист');
        $pricelistHeader = $pricelist['name'] . ', валюта ' . $pricelist['currency_id'];
        $sheet->mergeCellsByColumnAndRow($minColumnNumber, $currentRowNumber, $maxColumnNumber, $currentRowNumber);
        $sheet->getStyleByColumnAndRow($minColumnNumber, $currentRowNumber, $maxColumnNumber, $currentRowNumber)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyleByColumnAndRow($minColumnNumber, $currentRowNumber, $maxColumnNumber, $currentRowNumber)
            ->getFont()->setItalic(true);
        $sheet->setCellValueByColumnAndRow($minColumnNumber, $currentRowNumber, $pricelistHeader);
        $currentRowNumber++;
    
        foreach ($pricelist['location'] as $location) {
            if (empty($location['filterA'])) {
                continue;
            }
    
//            $sheet->setCellValueByColumnAndRow($columnNameNumber, $currentRowNumber, 'Местоположение');
            $locationHeader = 'Местоположение: ' . $this->_locations[$location['location_id']];
            $sheet->mergeCellsByColumnAndRow($minColumnNumber, $currentRowNumber, $maxColumnNumber, $currentRowNumber);
            $sheet->getStyleByColumnAndRow($minColumnNumber, $currentRowNumber, $maxColumnNumber, $currentRowNumber)
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyleByColumnAndRow($minColumnNumber, $currentRowNumber, $maxColumnNumber, $currentRowNumber)
                ->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);
            $sheet->getStyleByColumnAndRow($minColumnNumber, $currentRowNumber, $maxColumnNumber, $currentRowNumber)
                ->getFont()->setItalic(true);
            $sheet->setCellValueByColumnAndRow($minColumnNumber, $currentRowNumber, $locationHeader);
            $currentRowNumber++;
            
            foreach ($location['filterA'] as $filterA) {
                if (empty($filterA['filterB'])) {
                    continue;
                }
                
                $filterAHeader = trim((isset($filterA['nnp_country_name']) ? ($filterA['nnp_country_name']) : '') .
                    (isset($filterA['nnp_ndc_type_name']) ? (' ' . $filterA['nnp_ndc_type_name']) : '') .
                    (isset($filterA['nnp_operator_name']) ? (' ' . $filterA['nnp_operator_name']) : '') .
                    (isset($filterA['nnp_region_name']) ? (' ' . $filterA['nnp_region_name']) : '') .
                    (isset($filterA['nnp_city_name']) ? (' ' . $filterA['nnp_country_name']) : ''));
    
                if (empty($filterAHeader)) {
                    $filterAHeader = trim(isset($filterA['nnp_destination_name']) ? ($filterA['nnp_destination_name']) : '');
                }
                
                if (empty($filterAHeader)) {
                    $filterAHeader = '';
                }
    
//                $sheet->setCellValueByColumnAndRow($columnNameNumber, $currentRowNumber, 'Фильтр А');
//                $sheet->mergeCellsByColumnAndRow($minColumnNumber, $currentRowNumber, $maxColumnNumber, $currentRowNumber);
//                $sheet->getStyleByColumnAndRow($minColumnNumber, $currentRowNumber, $maxColumnNumber, $currentRowNumber)
//                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
//                $sheet->setCellValueByColumnAndRow($minColumnNumber, $currentRowNumber, $filterAHeader);
//                $currentRowNumber++;
                foreach ($filterA['filterB'] as $filterB) {
                    if (empty($filterB['prefixPriceNoLimit'])) {
                        continue;
                    }
    
                    $filterBText = trim((isset($filterB['nnp_country_name']) ? ($filterB['nnp_country_name']) : '') .
                        (isset($filterB['nnp_ndc_type_name']) ? (' ' . $filterB['nnp_ndc_type_name']) : '') .
                        (isset($filterB['nnp_operator_name']) ? (' ' . $filterB['nnp_operator_name']) : '') .
                        (isset($filterB['nnp_region_name']) ? (' ' . $filterB['nnp_region_name']) : '') .
                        (isset($filterB['nnp_city_name']) ? (' ' . $filterB['nnp_country_name']) : ''));
    
                    if (empty($filterBText)) {
                        $filterBText = trim(isset($filterB['nnp_destination_name']) ? ($filterB['nnp_destination_name']) : '');
                    }
                    
                    if (empty($filterBText)) {
                        $filterBText = 'Пустой фильтр B';
                    }
                    
                    if (!empty($filterAHeader)) {
                        $filterBText .= "\n" . '(' . $filterAHeader . ')';
                    }
                    
                    $prefixCount = count($filterB['prefixPriceNoLimit']);
//                    $sheet->mergeCellsByColumnAndRow($columnNameNumber, $currentRowNumber, $columnNameNumber, $currentRowNumber + $prefixCount - 1);
//                    $sheet->setCellValueByColumnAndRow($columnNameNumber, $currentRowNumber, 'Фильтр B');
//                    $sheet->getStyleByColumnAndRow($columnNameNumber, $currentRowNumber, 1, $currentRowNumber + $prefixCount - 1)
//                        ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->mergeCellsByColumnAndRow($minColumnNumber, $currentRowNumber, $minColumnNumber, $currentRowNumber + $prefixCount - 1);
                    $sheet->getStyleByColumnAndRow($minColumnNumber, $currentRowNumber, $minColumnNumber, $currentRowNumber + $prefixCount - 1)
                        ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyleByColumnAndRow($minColumnNumber, $currentRowNumber, $minColumnNumber, $currentRowNumber + $prefixCount - 1)
                        ->getAlignment()->setWrapText(true);
                    $sheet->setCellValueByColumnAndRow($minColumnNumber, $currentRowNumber, $filterBText);
                    foreach ($filterB['prefixPriceNoLimit'] as $prefixPrice) {
                        $sheet->getStyleByColumnAndRow($minColumnNumber + 1, $currentRowNumber, $minColumnNumber + 1, $currentRowNumber)
                            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                        $sheet->setCellValueByColumnAndRow($minColumnNumber + 1, $currentRowNumber, $prefixPrice['prefix_b']);
                        $sheet->setCellValueByColumnAndRow($minColumnNumber + 2, $currentRowNumber, $prefixPrice['b_number_price']);
                        $currentRowNumber++;
                    }
                }
            }
        }
        
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="file.xlsx"');
        $writer->save('php://output');
    }
}
