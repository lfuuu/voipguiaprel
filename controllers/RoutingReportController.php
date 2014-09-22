<?php

namespace app\controllers;

use app\classes\BaseController;
use app\forms\RoutingReportFilterForm;
use app\models\ConfigVersion;
use app\models\Operator;

class RoutingReportController extends BaseController
{
    public $enableCsrfValidation = false;

    public function actionMake()
    {
        return $this->processReport();
    }

    public function actionRecalc()
    {
        return $this->processReport(true);
    }

    public function processReport($forceRecalc = false)
    {
        $this->layout = 'empty';

        $filter = json_decode(file_get_contents('php://input'), true);

        $form = new RoutingReportFilterForm();
        $form->load($filter,  '');
        if ($form->validate()) {
            $version = ConfigVersion::findOne($form->configVersionId);

            $params = [
                ':versionId' => $version->id
            ];

            $where = '';
            if ($form->prefix) {
                $where .= " and r.prefix like :prefix ";
                $params[':prefix'] = $form->prefix . '%';
            }
            if ($form->destinationId !== null) {
                $where .= " and g.dest=:dest ";
                $params[':dest'] = $form->destinationId;
            }
            if ($form->countryId) {
                $where .= " and g.country=:country ";
                $params[':country'] = $form->countryId;
            }
            if ($form->regionId) {
                $where .= " and g.region=:region ";
                $params[':region'] = $form->regionId;
            }
            if ($form->mobFix == 't') {
                $where .= " and d.mob=true ";
            }
            if ($form->mobFix == 'f') {
                $where .= " and d.mob=false ";
            }

            $sql = "
                select r.prefix, g.name destination, d.mob, r.prices, r.locks, r.orders, r.routes
                from auth.select_routing_report(:versionId, '" . ($forceRecalc ? 'true' : 'false') . "') r
                left join public.voip_destinations d on r.prefix = d.defcode
                left join geo.geo g on g.id=d.geo_id
                where true {$where}
                order by r.prefix
                limit {$form->limit}
                offset {$form->offset}
            ";

            $report =
                ConfigVersion::getDb()
                    ->createCommand($sql, $params)
                    ->queryAll();

            foreach ($report as $k => $r) {
                $orders = substr($r['orders'], 1, strlen($r['orders']) - 2);
                $orders = $orders != '' ? explode(',', $orders) : array();
                $prices = substr($r['prices'], 1, strlen($r['prices']) - 2);
                $prices = $prices != '' ? explode(',', $prices) : array();
                $locks = substr($r['locks'], 1, strlen($r['locks']) - 2);
                $locks = $locks != '' ? explode(',', $locks) : array();
                $routes = substr($r['routes'], 1, strlen($r['routes']) - 2);
                $routes = $routes != '' ? explode(',', $routes) : array();

                $report[$k]['prices'] = $prices;
                $report[$k]['locks'] = $locks;
                $report[$k]['routes'] = $routes;
                $report[$k]['orders'] = $orders;
                $report[$k]['best_price'] = count($orders) > 0 ? $prices[$orders[0]] : '';
            }

            $operators = [];
            foreach(
                Operator::find()
                    ->andWhere(['config_version_id' => $version->id])
                    ->andWhere('auto_routing=true')
                    ->orderBy('code')
                    ->all()
                as $operator
            ) {
                $operators[$operator->id] = $operator;
            }

            return $this->render('index', ['report' => $report, 'operators' => $operators]);
        } else {
            return $this->render('index', ['errors' => $form->getErrors()]);
        }

    }
}
