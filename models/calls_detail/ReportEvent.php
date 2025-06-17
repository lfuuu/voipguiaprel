<?php
namespace app\models\calls_detail;

use yii\db\ActiveRecord;

/**
 * Модель для таблицы calls_detail.report_event
 *
 * @property string $csv_formated
 * @property string $mcn_callid
 */
class ReportEvent extends ActiveRecord
{
    public static function tableName()
    {
        return 'calls_detail.report_event';
    }
}
