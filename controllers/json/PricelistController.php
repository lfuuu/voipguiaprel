 <?php

namespace app\controllers\json;

use app\models\billing_uu\Pricelist;
use app\models\billing_uu\PricelistFilterB;
use app\models\billing_uu\PricelistPrefixPrice;
use Yii;
use app\classes\JsonController;
use app\classes\views\PricelistView;
use app\exceptions\FormValidationException;
use app\models\billing_uu\PricelistFilterA;
use app\models\billing_uu\PricelistLocation;
use yii\base\Exception;
use yii\db\Expression;
use yii\db\IntegrityException;
use yii\db\Query;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class PricelistController extends JsonController
{
    const SEARCH_LIMIT_PER_PRICELIST = 5;

    public function actionList()
    {
        if (!\Yii::$app->user->can('pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }

        return
            Pricelist::find()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->asArray()
            ->all();
    }

    public function actionRead()
    {
        if (!\Yii::$app->user->can('pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $searchArray = $this->request['search_array'];
        $limit = $this->request['limit'];
        $offset = $this->request['offset'];

        $query = Pricelist::find()
            ->alias('p')
            ->select([
                'p.*',
                'g.name as group_name',
                'is_in_use' => new Expression('sum(case when atl.tariff_id is not null then 1 else 0 end) > 0'),
            ])
            ->leftJoin('billing_uu.pricelist_group g', 'g.id = p.pricelist_group_id')
            ->leftJoin('billing_uu.package_pricelist pp', 'pp.nnp_pricelist_id = p.id')
            ->leftJoin('billing_uu.package_sms sms', 'sms.nnp_pricelist_id = p.id')
            ->leftJoin('billing_uu.package_data data', 'data.nnp_pricelist_id = p.id')
            ->leftJoin('billing_uu.account_tariff_light_view atl', 'atl.tariff_id = pp.tariff_id or atl.tariff_id = sms.tariff_id or atl.tariff_id = data.tariff_id')
            ->orderBy('name')
            ->groupBy('p.id, g.id')
            ->asArray();

        $countQuery = Pricelist::find()
            ->select(['id'])
            ->distinct();

        if (isset($searchArray['group_id']) && $searchArray['group_id'] && $searchArray['group_id'] != 'all') {
            $query->where(['p.pricelist_group_id' => $searchArray['group_id']]);
            $countQuery->where(['pricelist_group_id' => $searchArray['group_id']]);
        }
        if (isset($searchArray['currency']) && $searchArray['currency']) {
            $query->where(['p.currency_id' => $searchArray['currency']]);
            $countQuery->where(['currency_id' => $searchArray['currency']]);
        }

        if (isset($searchArray['service_type_id']) && $searchArray['service_type_id']) {
            $query->andWhere(['p.service_type_id' => $searchArray['service_type_id']]);
            $countQuery->andWhere(['service_type_id' => $searchArray['service_type_id']]);
        }

        if (isset($searchArray['is_active']) && is_bool($searchArray['is_active'])) {
            $query->andWhere(['p.is_active' => $searchArray['is_active']]);
            $countQuery->andWhere(['is_active' => $searchArray['is_active']]);
        }

        if (isset($searchArray['is_orig']) && is_bool($searchArray['is_orig'])) {
            $query->andWhere(['p.orig' => $searchArray['is_orig']]);
            $countQuery->andWhere(['orig' => $searchArray['is_orig']]);
        }

        if (isset($searchArray['id']) && $searchArray['id']) {
            $query->andWhere(['p.id' => $searchArray['id']]);
            $countQuery->andWhere(['id' => $searchArray['id']]);
        }

        if (isset($searchArray['query']) && $searchArray['query']) {
            $query->andWhere('p.name ilike :name');
            $query->addParams([':name' => '%' . $searchArray['query'] . '%']);
            $countQuery->andWhere('name ilike :name');
            $countQuery->addParams([':name' => '%' . $searchArray['query'] . '%']);
        }

        if (isset($searchArray['is_in_use']) && is_bool($searchArray['is_in_use'])) {
            if($searchArray['is_in_use'] == true){
                $query->having('sum(case when atl.tariff_id is not null then 1 else 0 end) > 0');
            }else { 
                $query->having('sum(case when atl.tariff_id is not null then 1 else 0 end) = 0');
            }
        }

        $count = $query->count();  
        $query->offset($offset);
        $query->limit($limit);
        $data = $query->all();

        return [
            'totalCount' => $count,
            'data' => $data
        ];
    }

    public function actionGetInCommerce()
    {
        if (!\Yii::$app->user->can('pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $id = $this->request['id'];

        $query = Pricelist::find()
            ->alias('p')
            ->select([
                'pricelist_id' => 'p.id',
                'p_trunk_id' => 'st.trunk_id',
                'p_trunk_name' => 't.name',
                'p_trunk_server_id' => 't.server_id',
                'l_trunk_id' => 'st.id'
            ])
            ->innerJoin('billing_uu.package_pricelist pp', 'pp.nnp_pricelist_id = p.id')
            ->innerJoin(
                'billing_uu.account_tariff_light atl',
                'atl.tariff_id = pp.tariff_id and atl.service_type_id in (23,24) and now() between atl.activate_from and atl.deactivate_from'
            )
            ->innerJoin('billing.service_trunk st', 'st.id = atl.account_tariff_id')
            ->innerJoin('auth.trunk t', 't.id = st.trunk_id')
            ->where(['p.id' => $id])
            ->orderBy('l_trunk_id')
            ->asArray();
            
        return $query->all();
    }

    public function actionGetInCommercePackage()
    {
        if (!\Yii::$app->user->can('pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $id = $this->request['id'];

        $query = Pricelist::find()
            ->alias('p')
            ->select([
                'pricelist_type' => 'p.type_id',
                'pricelist_id' => 'p.id',
                'tariff_id' => 'pckg.tariff_id',
                'package_name' => 'pckg.name',
            ])
            ->innerJoin('billing_uu.package_pricelist pp', 'pp.nnp_pricelist_id = p.id')
            ->innerJoin(
                'billing_uu.package pckg',
                'pckg.tariff_id = pp.tariff_id and pckg.service_type_id in (3)'
            )
            ->where(['p.id' => $id])
            ->orderBy('tariff_id')
            ->asArray();

        return $query->all();
    }

    public function actionGetInCommercePackageSms()
    {
        if (!\Yii::$app->user->can('pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $id = $this->request['id'];

        $query = Pricelist::find()
            ->alias('p')
            ->select([
                'pricelist_type' => 'p.type_id',
                'pricelist_id' => 'p.id',
                'tariff_id' => 'pckg.tariff_id',
                'package_name' => 'pckg.name',
            ])
            ->innerJoin('billing_uu.package_sms sms', 'sms.nnp_pricelist_id = p.id')
            ->innerJoin(
                'billing_uu.package pckg',
                'pckg.tariff_id = sms.tariff_id and pckg.service_type_id in (17,35,36)'
            )
            ->where(['p.id' => $id])
            ->orderBy('tariff_id')
            ->asArray();

        return $query->all();
    }

    public function actionGetInCommercePackageData()
    {
        if (!\Yii::$app->user->can('pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $id = $this->request['id'];

        $query = Pricelist::find()
            ->alias('p')
            ->select([
                'pricelist_type' => 'p.type_id',
                'pricelist_id' => 'p.id',
                'tariff_id' => 'pckg.tariff_id',
                'package_name' => 'pckg.name',
            ])
            ->innerJoin('billing_uu.package_data data', 'data.nnp_pricelist_id = p.id')
            ->innerJoin(
                'billing_uu.package pckg',
                'pckg.tariff_id = data.tariff_id and pckg.service_type_id = 31'
            )
            ->where(['p.id' => $id])
            ->orderBy('tariff_id')
            ->asArray();

        return $query->all();
    }


    public function actionGetWithDependentsNew()
    {
        if (!\Yii::$app->user->can('pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $flatRules = [
            'p' => Pricelist::rulesFlat(),
            'pl' => PricelistLocation::rulesFlat(),
            'pfa' => PricelistFilterA::rulesFlat(),
            'pfb' => PricelistFilterB::rulesFlat(),
            'ppp' => PricelistPrefixPrice::rulesFlat(),
        ];

        $select = [];

        foreach ($flatRules as $tableKey => $rulesArray) {
            foreach ($rulesArray as $rule) {
                $select[$tableKey . '__' . $rule] = $tableKey . '.' . $rule;
            }
        }

        $prefixPriceSelect = <<<SQL
        LATERAL (select * from billing_uu.pricelist_prefix_price ppp
        where pricelist_filter_b_id = pfb.id
        and date_to > now()
        and prefix_b in (
            select distinct prefix_b from billing_uu.pricelist_prefix_price
            where pricelist_filter_b_id = pfb.id
            and date_to > now()
            order by prefix_b
            limit :limit
        ) or prefix_b is null)
SQL;

        $queryResult =
            Pricelist::find()
            ->alias('p')
            ->select($select)
            ->leftJoin(PricelistLocation::tableName() . ' as pl', 'pl.pricelist_id = p.id')
            ->leftJoin(PricelistFilterA::tableName() . ' as pfa', 'pfa.pricelist_location_id = pl.id')
            ->leftJoin(PricelistFilterB::tableName() . ' as pfb', 'pfb.pricelist_filter_a_id = pfa.id')
            ->leftJoin(new Expression($prefixPriceSelect) . ' as ppp', 'ppp.pricelist_filter_b_id = pfb.id')
            ->where(['p.id' => $this->request['id']])
            ->orderBy('pl.id, pfa.id, pfb.id, ppp.prefix_b, ppp.id')
            ->addParams([':limit' => PricelistPrefixPrice::PAGE_LIMIT])
            ->asArray()
            ->all();

        if (isset($this->request['type']) && $this->request['type'] == 'short') {
            $result = PricelistView::getForShortForm($queryResult);
        } else {
            $result = PricelistView::getForFullForm($queryResult);
        }

        return $result;
    }

    public function actionGetWithDependents()
    {
        if (!\Yii::$app->user->can('pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }

        return
            Pricelist::find()
            ->with('location.filterA.filterB.prefixPrice')
            ->with('location.filterA.filterB.prefixPriceCount')
            ->where(['id' => $this->request['id']])
            ->asArray()
            ->one();
    }

    public function actionGet()
    {
        if (!\Yii::$app->user->can('pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }

        return
            Pricelist::find()
            ->where(['id' => $this->request['id']])
            ->asArray()
            ->one();
    }

    public function actionSave()
    {
        if (!\Yii::$app->user->can('pricelist_edit') && !\Yii::$app->user->can('pricelist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $result = [];

        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('pricelist_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }

            $item = $this->getPricelistOr404($this->request['id']);
            $result['log'] = ['data_before' => $this->getDataForLog($item)];
        } else {
            if (!\Yii::$app->user->can('pricelist_create')) {
                throw new ForbiddenHttpException('Access denied');
            }

            $item = Pricelist::create();
            $result['log'] = ['data_before' => []];
        }

        $item->load($this->request, '');

        $transaction = Pricelist::getDb()->beginTransaction();
        try {
            if (!$item->save()) {
                throw new FormValidationException($item);
            }

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }

        if (isset($this->request['old_pricelist_id'])) {
            $item->importFromOldVersion($this->request['old_pricelist_id']);
        }

        $result['log']['data_after'] = $this->getDataForLog($item);

        return $result;
    }

    public function actionSaveAndUpdate()
    {
        if (!\Yii::$app->user->can('pricelist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $item = $this->getPricelistOr404($this->request['id']);
        $item->load($this->request, '');

        $transaction = Pricelist::getDb()->beginTransaction();
        try {
            if (!$item->save()) {
                throw new FormValidationException($item);
            }

            $filterBArray = PricelistFilterB::find()
                ->alias('fb')
                ->select('fb.*')
                ->innerJoin('billing_uu.pricelist_filter_a as fa', 'fa.id = fb.pricelist_filter_a_id')
                ->innerJoin('billing_uu.pricelist_location as pl', 'pl.id = fa.pricelist_location_id')
                ->with('prefixPriceBasic')
                ->where('pl.pricelist_id = :pricelist_id')
                ->addParams([':pricelist_id' => $this->request['id']])
                ->all();

            foreach ($filterBArray as $filterB) {
                $filterB->tarification_free_seconds = $item->default_tarification_free_seconds;
                $filterB->tarification_interval_seconds = $item->default_tarification_interval_seconds;
                $filterB->tarification_min_paid_seconds = $item->default_tarification_min_paid_seconds;
                $filterB->tarification_type = $item->default_tarification_type;

                $prefixesToSave = [];
                $prefixesToSaveFlat = [];

                foreach ($filterB->prefixPriceBasic as $prefixPrice) {
                    if (!isset($prefixesToSave[$prefixPrice->prefix_b])) {
                        $prefixesToSave[$prefixPrice->prefix_b] = ['date_from' => $prefixPrice->date_from, 'id' => $prefixPrice->id];
                    } else {
                        $dateFromCompare = date_create_from_format('Y-m-d', $prefixesToSave[$prefixPrice->prefix_b]['date_from']);
                        $dateFromCurrent = date_create_from_format('Y-m-d', $prefixPrice->date_from);
                        if ($dateFromCurrent > $dateFromCompare) {
                            $prefixesToSave[$prefixPrice->prefix_b] = ['date_from' => $prefixPrice->date_from, 'id' => $prefixPrice->id];
                        }
                    }
                }

                foreach ($prefixesToSave as $prefix) {
                    $prefixesToSaveFlat[] = $prefix['id'];
                }

                foreach ($filterB->prefixPriceBasic as $prefixPrice) {
                    if (in_array($prefixPrice->id, $prefixesToSaveFlat)) {
                        $prefixPrice->date_from = $item->date_start;

                        if (!$prefixPrice->save()) {
                            throw new FormValidationException($item);
                        }
                    } else {
                        $prefixPrice->delete();
                    }
                }

                if (!$filterB->save()) {
                    throw new FormValidationException($item);
                }
            }

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    }

    public function actionToggleActive()
    {
        if (!\Yii::$app->user->can('pricelist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $item = $this->getPricelistOr404($this->request['id']);

        if ($item->isInCommercialUse() && $item->is_active) {
            throw new Exception('In commercial use!');
        } else {
            $item->is_active = !$item->is_active;
            $item->save();
        }
    }

    public function actionInherit()
    {
        if (!\Yii::$app->user->can('pricelist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $name = $this->request['name'];

        if (!empty($name)) {
            $result = (new Query())->select(['id' => new Expression('billing_uu.clone_pricelist(:old_pricelist_id, true, :name)')])
                ->addParams([
                    ':old_pricelist_id' => $this->request['id'],
                    ':name' => $name
                ])->one();
        } else {
            $result = (new Query())->select(['id' => new Expression('billing_uu.clone_pricelist(:old_pricelist_id, true)')])
                ->addParams([
                    ':old_pricelist_id' => $this->request['id']
                ])->one();
        }

        return $result;
    }

    public function actionCopy()
    {
        if (!\Yii::$app->user->can('pricelist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $result = (new Query())->select(['id' => new Expression('billing_uu.clone_pricelist(:old_pricelist_id)')])->addParams([':old_pricelist_id' => $this->request['id']])->one();

        return $result;
    }

    public function actionCopyAndMultiply()
    {
        if (!\Yii::$app->user->can('pricelist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $result = (new Query())
            ->select(['id' => new Expression('billing_uu.clone_pricelist(:old_pricelist_id, :multiplier)')])
            ->addParams(
                [
                    ':old_pricelist_id' => $this->request['id'],
                    ':multiplier' => $this->request['multiplier']
                ]
            )
            ->one();

        return $result;
    }

    /**
     * @throws StaleObjectException
     * @throws HttpException
     * @throws \Exception
     */
    public function actionDelete()
    {
        if (!\Yii::$app->user->can('pricelist_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $item = $this->getPricelistOr404($this->request['id']);

        try {
            $item->delete();
        } catch (IntegrityException $e) {
            return ['errors' => [['code' => $e->getCode(), 'message' => $e->getMessage()]]];
        }
    }


    public function actionSearch()
    {
        if (!\Yii::$app->user->can('pricelist_search')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $fields = [
            'a_country_id', 'b_country_id', 'a_region_id', 'b_region_id', 'a_city_id', 'b_city_id', 'a_operator_id',
            'b_operator_id', 'a_ndc_id', 'b_ndc_id', 'timestamp', 'number_a', 'number_b', 'mcc', 'mnc', 'location_id',
            'service_type_id'
        ];

        $apiUrl = 'http://reg10.mcntelecom.ru:8032/';

        $apiParams = [
            'cmd' => 'findPricelist'
        ];

        foreach ($fields as $fieldName) {
            if (isset($this->request[$fieldName]) && !empty($this->request[$fieldName])) {
                $apiParams[$fieldName] = $this->request[$fieldName];
            }
        }

        if (isset($this->request['is_orig'])) {
            $apiParams['is_orig'] = $this->request['is_orig'];
        }

        $request = $apiUrl . 'test/nnpcalc?' . http_build_query($apiParams);

        $response = file_get_contents($request);

        $response = json_decode($response, true);

        $result = self::processSearchResult($response['paths']);

        return [
            'params' => $response['params'],
            'paths' => $result,
            'size' => $response['size'],
            'url' => $request
        ];
    }

    private function processSearchResult($data)
    {
        $result = [];

        foreach ($data as $item) {
            $pricelistId = $item['pricelist_id'];

            if (!isset($result[$pricelistId])) {
                $result[$pricelistId] = [];
            }

            if (count($result[$pricelistId]) >= self::SEARCH_LIMIT_PER_PRICELIST) {
                if (!isset($result[$pricelistId]['extra_count'])) {
                    $result[$pricelistId]['extra_count'] = 0;
                }

                $result[$pricelistId]['extra_count']++;
                continue;
            }

            if (count($result[$pricelistId]) == 0) {
                $pricelist = Pricelist::findOne(['id' => $pricelistId]);

                if ($pricelist) {
                    $item['pricelist_name'] = $pricelist->name;
                    $item['date_created'] = $pricelist->date_created;
                    $item['date_start'] = $pricelist->date_start;
                }

                $result[$item['pricelist_id']][] = $item;
            } else {
                unset($item['pricelist_id']);
                $result[$pricelistId][] = $item;
            }
        }

        return $result;
    }

    public function actionOldSearch()
    {
        if (!\Yii::$app->user->can('old_pricelist_search')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $fields = [
            'country_code', 'prefix', 'pricelist_ids'
        ];

        $apiUrl = 'http://reg10.mcntelecom.ru:8032/';

        $apiParams = [
            'cmd' => 'findDefs'
        ];

        foreach ($fields as $fieldName) {
            if (isset($this->request[$fieldName]) && !empty($this->request[$fieldName])) {
                $apiParams[$fieldName] = $this->request[$fieldName];
            }
        }

        if (isset($this->request['exact_match'])) {
            $apiParams['exact_match'] = $this->request['exact_match'];
        }

        $request = $apiUrl . 'test/nnpcalc?' . http_build_query($apiParams);

        $response = file_get_contents($request);

        return json_decode($response, true);
    }

    public function actionSynchronize()
    {
        if (!\Yii::$app->user->can('pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }

        \Yii::$app->db->createCommand("select event.notify('nnp_pricelist_prefix_price', 0);")->queryAll();

        return ['success' => 1];
    }
}
