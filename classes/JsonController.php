<?php
namespace app\classes;

use Yii;
use yii\filters\ContentNegotiator;
use yii\web\Response;

class JsonController extends BaseController
{
    public $serializer = 'yii\rest\Serializer';

    public $enableCsrfValidation = false;

    protected $request;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        return $behaviors;
    }
/*
    public function runAction($id, $params = [])
    {
        try {
            $this->data = json_decode(file_get_contents("php://input"), true);
            $result = parent::runAction($id, $params);
        } catch (FormValidationException $e) {
            $result = [ 'errors' => $e->getErrors() ];
        } catch (\Exception $e) {
            throw $e;
            $result = [
                'errors' => [
                    'exception' => [$e->getMessage()]
                ]
            ];
        }
        header('Content-Type: application/json');
        echo json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
*/
    public function beforeAction($action)
    {
        $result = parent::beforeAction($action);
        $this->request = Yii::$app->request->getBodyParams();
        return $result;
    }


    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);
        return $this->serializeData($result);
    }

    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
}