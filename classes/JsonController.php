<?php
namespace app\classes;

use app\exceptions\FormValidationException;
use app\models\ActionLog;
use Yii;
use yii\filters\ContentNegotiator;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\Response;

class JsonController extends BaseController
{
    public $serializer = 'yii\rest\Serializer';

    public $enableCsrfValidation = false;

    protected $doNotLog = false;

    private $saveMethods = ['save', 'saveAndUpdate', 'toggleActive', 'inherit', 'copy', 'delete'];

    protected $modelName = '';
    protected $idParamName = '';
    protected $nameParamName = '';
    protected $withDependencies = [];
    protected $throwExceptionOnEmptyItemInSave = true;
    protected $readWhere = [];
    protected $readSelect = ['*'];
    protected $getSelect = ['*'];
    protected $createPermission = '';
    protected $listPermission = '';
    protected $editPermission = '';
    protected $deletePermission = '';

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

    public function actionList()
    {
        if (!\Yii::$app->user->can($this->listPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;

        $where = [];

        foreach ($this->readWhere as $param) {
            $where[$param] = $this->request[$param];
        }

        return
            $modelName::find()
                ->select(['id' => $this->idParamName, 'name' => $this->nameParamName])
                ->orderBy($this->nameParamName)
                ->andWhere($where)
                ->asArray()
                ->all();
    }

    public function actionRead()
    {
        if (!\Yii::$app->user->can($this->listPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;

        $where = [];

        foreach ($this->readWhere as $param) {
            $where[$param] = $this->request[$param];
        }

        $items =
            $modelName::find()
                ->select($this->readSelect)
                ->orderBy($this->nameParamName)
                ->andWhere($where)
                ->asArray()
                ->all();

        $items = $this->performAfterReadActions($items);

        return $items;
    }

    protected function performAfterReadActions($items)
    {
        return $items;
    }

    public function actionGet()
    {
        if (!\Yii::$app->user->can($this->editPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;
        $item = $modelName::find()
            ->select($this->getSelect)
            ->with($this->withDependencies)
            ->where([$this->idParamName => $this->request[$this->idParamName]])
            ->asArray()
            ->one();

        if ($item === null) {
            throw new HttpException(404, $modelName . ' не найден');
        }

        $item = $this->performAfterGetActions($item);

        return $item;
    }

    protected function performAfterGetActions($item)
    {
        return $item;
    }

    public function actionSave()
    {
        if (!\Yii::$app->user->can($this->editPermission) && !\Yii::$app->user->can($this->createPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;

        $result = [];

        if (isset($this->request[$this->idParamName])) {
            if (!\Yii::$app->user->can($this->editPermission)) {
                throw new ForbiddenHttpException('Access denied');
            }

            $item = $modelName::findOne($this->request[$this->idParamName]);

            if ($item === null) {
                if ($this->throwExceptionOnEmptyItemInSave) {
                    throw new HttpException(404, $this->modelName . ' не найден');
                } else {
                    if (!\Yii::$app->user->can($this->createPermission)) {
                        throw new ForbiddenHttpException('Access denied');
                    }

                    $item = $modelName::create();
                    $result['log'] = ['data_before' => []];
                }
            } else {
                $result['log'] = ['data_before' => self::getDataForLog($item)];
            }

        } else {
            if (!\Yii::$app->user->can($this->createPermission)) {
                throw new ForbiddenHttpException('Access denied');
            }

            $item = $modelName::create();
            $result['log'] = ['data_before' => []];
        }

        $item->load($this->request, '');

        $this->performBeforeSaveActions($item, $this->request);

        $transaction = $modelName::getDb()->beginTransaction();
        try {
            if (!$item->save()) {
                throw new FormValidationException($item);
            }

            $this->performAfterSaveActions($item, $this->request);

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }

        $result['log']['data_after'] = self::getDataForLog($item);

        return $result;
    }

    protected function performBeforeSaveActions($item, $request)
    {
        // do_nothing
    }

    protected function performAfterSaveActions($item, $request)
    {
        // do_nothing
    }

    public function actionDelete()
    {
        if (!\Yii::$app->user->can($this->deletePermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;

        $item = $modelName::findOne($this->request[$this->idParamName]);
        $item->delete();
    }

    protected function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
}