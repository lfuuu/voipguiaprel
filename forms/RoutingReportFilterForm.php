<?php

namespace app\forms;

use Yii;
use yii\base\Model;

class RoutingReportFilterForm extends Model
{
    public $configVersionId;
    public $destinationId;
    public $countryId;
    public $regionId;
    public $mobFix;
    public $prefix;
    public $limit;
    public $offset;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->limit = 100;
        $this->offset = 0;
    }

    public function rules()
    {
        return [
            [['configVersionId'], 'required'],
            [['configVersionId', 'destinationId', 'countryId', 'regionId'], 'integer'],
            [['prefix'], 'string'],
            ['mobFix', 'in', 'range' => ['t', 'f']],
            [['limit', 'offset'], 'integer'],
        ];
    }
}
