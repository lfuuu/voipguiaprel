<?php
namespace app\controllers\json;

use Yii;
use yii\web\Controller;

class PstnController extends Controller
{
    public function actionIndex()
    {
        $mcn_callid = Yii::$app->request->get('mcn_callid');
        $is_573 = Yii::$app->request->get('is_573');
        $is_86 = Yii::$app->request->get('is_86');
        $is_debug = Yii::$app->request->get('is_debug');

        $url = 'https://eridanus3-aggr-legs.veles.mcn.ru:8207/pstn.php?' .
            http_build_query([
                'mcn_callid' => $mcn_callid,
                'is_573' => $is_573,
                'is_86' => $is_86,
                'is_debug' => $is_debug
            ]);

        $response = $this->performCurlRequest($url);

        if ($is_debug == 1) {
            return $response;
        }

        $data = $this->parseCsvResponse($response);

        $lines = [];
        foreach ($data as $row) {
            $lines[] = implode(';', $row);
        }

        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        return implode("\n", $lines);
    }

    /**
     * Новый метод для запроса по графу
     */
    public function actionGraph()
    {
        $mcn_callid = Yii::$app->request->get('mcn_callid');

        $url = 'https://calligrapher.mcn.ru/v1/api/graph?' . http_build_query([
            'mcnCallId' => $mcn_callid,
        ]);

        $response = $this->performCurlRequest($url);

        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        return $response;
    }

    /**
     * Выполнение CURL запроса
     * @param string $url
     * @return string
     */
    protected function performCurlRequest($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    /**
     * Парсинг CSV ответа
     * @param string $csv
     * @return array
     */
    protected function parseCsvResponse($csv)
    {
        $data = [];
        $rows = explode("\n", $csv);

        foreach ($rows as $row) {
            if (empty($row)) continue;
            $data[] = str_getcsv($row, ';');
        }

        return $data;
    }
}
