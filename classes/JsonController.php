<?php
namespace app\classes;

use app\models\ActionLog;
use Yii;
use yii\filters\ContentNegotiator;
use yii\web\Response;

class JsonController extends BaseController
{
    public $serializer = 'yii\rest\Serializer';

    public $enableCsrfValidation = false;

    protected $request;
    
    protected $doNotLog = false;
    
    private $saveMethods = ['save', 'saveAndUpdate', 'toggleActive', 'inherit', 'copy', 'delete'];

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
    
        // Логируем только если включено логирование в конфиге
        // и если у контроллера _doNotLog установлена в ложь
        if (\Yii::$app->params['loggingEnabled'] && !$action->controller->doNotLog) {
            $actionName = $action->id;
            
            if (\Yii::$app->params['logReadMethods'] || in_array($actionName, $this->saveMethods)) {
                if (isset($result['log'])) {
                    $dataBefore = $result['log']['data_before'];
                    $dataAfter = $result['log']['data_after'];
                    
                    if (isset($result['result'])) {
                        $result = $result['result'];
                    } else {
                        $result = null;
                    }
                } else {
                    $dataBefore = [];
                    $dataAfter = [];
                }
                
                $data = [
                    'user_id' => \Yii::$app->user->id,
                    'controller' => $action->controller->id,
                    'action' => $action->id,
                    'object_id' => isset($this->request['id']) ? $this->request['id'] : (isset($dataAfter['id']) ? $dataAfter['id'] : ''),
                    'request_date' => 'now()',
                    'data_before' => json_encode($dataBefore, JSON_FORCE_OBJECT),
                    'data_after' => json_encode($dataAfter, JSON_FORCE_OBJECT)
                ];
                
                $logItem = ActionLog::create($data);
                $logItem->save();
            }
        }
        
        return $this->serializeData($result);
    }
    
    protected function getDataForLog($item)
    {
        $data = $item->getAttributes();
        
        $subitems = isset($item->_subitems) ? $item->_subitems : [];
        
        foreach ($subitems as $subitemName => $subitemMethod) {
            $subitemData = $item->$subitemMethod()->asArray()->all();
            $data[$subitemName] = $subitemData;
        }
        
        return $data;
    }

    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
}