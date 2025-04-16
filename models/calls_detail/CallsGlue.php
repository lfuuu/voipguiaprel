<?php

namespace app\models\calls_detail;

use yii\db\ActiveRecord;

/**
 * Модель для таблицы calls_detail.calls_glue
 *
 * @property int    $id
 * @property string $mcn_callid
 * @property int    $id_cnt
 * @property string $dt_create
 * @property string $dt_update
 * @property string $dt_connect
 * @property int    $update_cnt
 * @property array  $val
 * @property bool   $fRawErrCutoff
 * @property bool   $fCdrErrCutoff
 * @property bool   $fAstErrCutoff
 */
class CallsGlue extends ActiveRecord
{
    public static function tableName()
    {
        return 'calls_detail.calls_glue';
    }

    public function rules()
    {
        return [
            [['id', 'id_cnt', 'update_cnt'], 'integer'],
            [['mcn_callid'], 'string'],
            [['dt_create', 'dt_update', 'dt_connect'], 'safe'],
            [['val'], 'safe'],
            [['fRawErrCutoff', 'fCdrErrCutoff', 'fAstErrCutoff'], 'boolean'],
        ];
    }
}
