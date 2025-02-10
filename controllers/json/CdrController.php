<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\models\billing\DisconnectCause;
use app\models\calls_cdr\Cdr;
use yii\base\Request;
use yii\db\Expression;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yii\web\Response;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class CdrController extends JsonController
{
    public function actionRead()
    {
        if (!\Yii::$app->user->can('cdr_report_read')) {
            throw new ForbiddenHttpException('Access denied');
        }
        $srcNumber = $this->request['src_number'];
        $dstNumber = $this->request['dst_number'];
        $redirectNumber = $this->request['redirect_number'];
        $outRedirectNumber = $this->request['out_redirect_number'];
        $srcRoute = isset($this->request['src_route']) ? $this->request['src_route'] : null;
        $dstRoute = isset($this->request['dst_route']) ? $this->request['dst_route'] : null;
        $isTimeAbsolute = $this->request['is_time_absolute'];
        $timeFrom = $this->request['time_from'];
        $timeTo = $this->request['time_to'];
        $timeRelative = $this->request['time_relative'];
        $limit = $this->request['limit'];
        $hubId = $this->request['hub_id'];
        $mcnCallid = $this->request['mcn_callid'];
        $sortAsc = $this->request['sort_asc'];
        $disconnectCauseId = $this->request['disconnect_cause_id'];
        $showAll = $this->request['show_all'];
        $source = $this->request['source'];
        $sessionTime = $this->request['session_time'];
        $sessionCompare = $this->request['session_compare'];
        $where = [];
        if ($srcNumber) {
            $where['c.src_number'] = $srcNumber;
        }
        if ($dstNumber) {
            $where['c.dst_number'] = $dstNumber;
        }
        if ($redirectNumber) {
            $where['c.redirect_number'] = $redirectNumber;
        }
        if ($outRedirectNumber) {
            $where['c.out_redirect_number'] = $outRedirectNumber;
        }
        if ($hubId) {
            $where['s.hub_id'] = $hubId;
        }
        if ($mcnCallid) {
            $where['c.mcn_callid'] = $mcnCallid;
        }
        if ($srcRoute) {
            $where['c.src_route'] = $srcRoute;
        }
        if ($dstRoute) {
            $where['c.dst_route'] = $dstRoute;
        }
        if ($disconnectCauseId) {
            $where['c.disconnect_cause'] = $disconnectCauseId;
        }
        if (empty($where) && (empty($timeFrom) || empty($timeTo))) {
            return [];
        }
        if (empty($limit)) {
            $limit = 100;
        }
        $andWhere = '';
        $params = [];
        if ($isTimeAbsolute) {
            if (empty($timeFrom) && !empty($timeTo)) {
                $andWhere = 'c.connect_time <= :time_to';
                $params = [':time_to' => $timeTo];
            } elseif (!empty($timeFrom) && empty($timeTo)) {
                $andWhere = 'c.connect_time >= :time_from';
                $params = [':time_from' => $timeFrom];
            } elseif (!empty($timeFrom) && !empty($timeTo)) {
                $andWhere = 'c.connect_time >= :time_from and c.connect_time <= :time_to';
                $params = [':time_from' => $timeFrom, ':time_to' => $timeTo];
            }
        } else {
            if (!empty($timeRelative)) {
                $andWhere = 'c.connect_time >= (now() - INTERVAL \'' . (int)$timeRelative . ' seconds\') at time zone \'utc\'';
            }
        }
        $query = Cdr::find()
            ->alias('c')
            ->select([
                'c.*',
                'server_name' => new Expression("s.id || ': ' || s.name"),
                'disconnect_cause_description' => 'dc.description'
            ])
            ->innerJoin('public.server s', 's.id = c.server_id')
            ->leftJoin('billing.disconnect_cause dc', 'dc.cause_id = c.disconnect_cause')
            ->where($where)
            ->limit($limit)
            ->orderBy('c.connect_time ' . ($sortAsc ? 'ASC' : 'DESC'));
        if ($andWhere && $params) {
            $query->andWhere($andWhere)->addParams($params);
        } elseif ($andWhere) {
            $query->andWhere($andWhere);
        }
        if (!$showAll) {
            $query->andWhere('c.session_time > 0');
        }
        if ($source) {
            switch ($source) {
                case 'xml':
                    $query->andWhere('c.mcn_callid is null');
                    $query->andWhere("c.src_route not like '%Roaming%'");
                    $query->andWhere("c.dst_route not like '%Roaming%'");
                    break;
                case 'accounting':
                    $query->andWhere('c.mcn_callid is not null');
                    break;
                default:
                    break;
            }
        }
        if ($sessionCompare && $sessionTime && in_array($sessionCompare, ['>=', '=', '<=']) && is_numeric($sessionTime)) {
            $query->andWhere('c.session_time ' . $sessionCompare . ' ' . $sessionTime);
        }
        return $query->asArray()->all();
    }

    public function actionGet()
    {
        if (!\Yii::$app->user->can('cdr_report_read')) {
            throw new ForbiddenHttpException('Access denied');
        }
        $items = Cdr::find()
            ->alias('c')
            ->select([
                'c.*',
                'server_name' => new Expression("s.id || ': ' || s.name"),
                'disconnect_cause_description' => 'dc.description',
            ])
            ->with('callsRaw.currency')
            ->with('callsRaw.legTypeName')
            ->innerJoin('public.server s', 's.id = c.server_id')
            ->leftJoin('billing.disconnect_cause dc', 'dc.cause_id = c.disconnect_cause')
            ->leftJoin('billing.leg_type lt', 'lt.id = c.disconnect_cause')
            ->where(['c.mcn_callid' => $this->request['mcn_callid']])
            ->asArray()
            ->all();
        usort($items, function($a, $b) {
            return $a['connect_time'] < $b['connect_time'] ? 1 : -1;
        });
        $link = \Yii::$app->params['isEuropean'] ? 'https://stat.kompaas.tech/' : 'https://stat.mcn.ru/';
        $result = [
            'items' => $items,
            'link'  => $link,
        ];

        return $result;
    }

        public function actionGetLegs()
    {
        if (!\Yii::$app->user->can('cdr_report_read')) {
            throw new \yii\web\ForbiddenHttpException('Access denied');
        }

        $mcnCallid = \Yii::$app->request->get('mcn_callid');
        if (!$mcnCallid) {
            throw new \yii\web\HttpException(400, "Не указан mcn_callid");
        }

        $sql = "
    WITH
    callsraw_leg AS (
        SELECT
            r.id,
            CASE
                WHEN r.number_service_id IS NULL THEN r.server_id
                ELSE sn.server_id
            END AS node_id,
            connect_time,
            orig,
            trunk_id,
            t.name AS trunk_name,
            \"numA\",
            \"numB\",
            \"numC\",
            disconnect_cause,
            session_time,
            account_id,
            mcn_callid,
            contract_type_id,
            r.leg_type,
            r.trunk_service_id,
            r.number_service_id
        FROM calls_raw.calls_raw r
        JOIN auth.trunk t ON (r.trunk_id = t.id)
        LEFT JOIN billing.service_number sn ON (r.number_service_id = sn.id)
        WHERE account_id > 0
          AND mcn_callid = :mcn_callid
        ORDER BY connect_time
    ),
    
    callsraw_leg_origs AS (
        SELECT
            cl.*,
            ps.id || '/' || ps.name || '-' ||
                CASE WHEN cl.node_id > 21 THEN 'КУС' ELSE 'МГ' END AS node_name
        FROM callsraw_leg cl
        JOIN public.server ps ON (cl.node_id = ps.id)
        WHERE cl.orig
        ORDER BY cl.connect_time
        LIMIT 1
    ),
    
    callsraw_leg_term AS (
        SELECT
            cl.*,
            ps.id || '/' || ps.name || '-' ||
                CASE WHEN cl.node_id > 21 THEN 'КУС' ELSE 'МГ' END AS node_name
        FROM callsraw_leg cl
        JOIN public.server ps ON (cl.node_id = ps.id)
        WHERE NOT orig
        ORDER BY connect_time
    )
    
    SELECT
        '-------------- Оригинационное плечо -' AS orig_bar,
        o.node_id AS orig_node_id,
        o.node_name AS orig_node_name,
        o.trunk_name AS orig_trunk,
        o.\"numA\" AS orig_numa,
        o.\"numB\" AS orig_numb,
        o.\"numC\" AS orig_numc,
        o.\"account_id\" AS orig_account_id,
        o.disconnect_cause AS orig_disconnect_cause,
        o.contract_type_id AS orig_contract_type_id,
        o.leg_type AS orig_leg_type,
        o.trunk_service_id AS orig_trunk_service_id,
        o.number_service_id AS orig_number_service_id,
    
        '-------------- Терминационное плечо -' AS term_bar,
        t.node_id AS term_node_id,
        t.node_name AS term_node_name,
        t.trunk_name AS term_trunk,
        t.\"numA\" AS term_numa,
        t.\"numB\" AS term_numb,
        t.\"numC\" AS term_numc,
        t.\"account_id\" AS term_account_id,
        t.disconnect_cause AS term_disconnect_cause,
        t.contract_type_id AS term_contract_type_id,
        t.leg_type AS term_leg_type,
        t.trunk_service_id AS term_trunk_service_id,
        t.number_service_id AS term_number_service_id
    
    FROM callsraw_leg_origs o
    JOIN callsraw_leg_term t ON (o.mcn_callid = t.mcn_callid)
    ";

        $result = \Yii::$app->db->createCommand($sql)
            ->bindValue(':mcn_callid', $mcnCallid)
            ->queryAll();
    
        return $result;
    }
    

    public function actionDisconnectCauseList()
    {
        if (!\Yii::$app->user->can('cdr_report_read')) {
            throw new ForbiddenHttpException('Access denied');
        }
        return DisconnectCause::find()
            ->select(['id' => 'cause_id', 'name' => new Expression('cause_id || \': \' || value')])
            ->orderBy('cause_id')
            ->asArray()
            ->all();
    }

    public function actionReadAndExport()
    {
        $data = $this->actionRead();
        if (empty($data)) {
            throw new HttpException(400, "Нет данных для экспорта.");
        }

        $fileName = 'CDR_Report_' . date('Ymd_His') . '.xlsx';
        $filePath = '/workspace/voip_gui/web/files/' . $fileName;

        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        $this->createExcelDocument($data, $filePath);
    }

    protected function createExcelDocument($data, $fileName = 'file.xlsx')
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Определение стиля заголовка
        $headerStyle = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                'rotation' => 90,
                'startColor' => [
                    'argb' => 'FFA0A0A0',
                ],
                'endColor' => [
                    'argb' => 'FFFFFFFF',
                ],
            ],
        ];

        $headers = [
            'Server ID', 'NAS IP', 'SRC Number', 'DST Number', 'Setup Time', 
            'Session Time', 'Disconnect Cause', 'SRC Route', 'DST Route', 'MCN Callid', 'RedirectNum'
        ];

        $columnIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue("{$columnIndex}1", $header);
            $sheet->getStyle("{$columnIndex}1")->applyFromArray($headerStyle);
            $columnIndex++;
        }

        $rowIndex = 2;
        foreach ($data as $item) {
            $columnIndex = 1;
            foreach ($headers as $field) {
                if ($field == 'MCN Callid') {
                    $key = 'mcn_callid';
                } elseif ($field == 'RedirectNum') {
                    $key = 'redirect_number';
                } else {
                    $key = strtolower(str_replace(' ', '_', $field));
                }
                $sheet->setCellValueByColumnAndRow($columnIndex, $rowIndex, $item[$key] ?? 'N/A');
                $columnIndex++;
            }
            $rowIndex++;
        }

        $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex - 1);
        foreach (range('A', $lastColumn) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $allBordersStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];
        $sheet->getStyle('A1:' . $lastColumn . ($rowIndex - 1))->applyFromArray($allBordersStyle);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        try {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
            $writer->save('php://output');
            exit;
        } catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
            \Yii::error("Ошибка при создании Excel файла: {$e->getMessage()}", 'export');
            throw new HttpException(500, "Ошибка при создании Excel файла: {$e->getMessage()}");
        }
    }
}