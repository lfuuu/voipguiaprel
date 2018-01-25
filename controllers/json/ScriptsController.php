<?php

namespace app\controllers\json;

use app\classes\JsonController;
use yii\web\ForbiddenHttpException;

class ScriptsController extends JsonController
{
    public function actionGenerateTests()
    {
        if (!\Yii::$app->user->can('auto_test_management')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $command = 'python ./../scripts/generate_autotest/generate_autotest.py 2>&1 -c ../../generate_autotest.conf > ../scripts/generate_autotest/log.txt';
        
        shell_exec($command);
        
        return ['success' => 1];
    }

    public function actionDeleteTests()
    {
        if (!\Yii::$app->user->can('auto_test_management')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $command = 'python ./../scripts/generate_autotest/generate_autotest.py 2>&1 -c ../../generate_autotest.conf -delete > ../scripts/generate_autotest/log.txt';
    
        shell_exec($command);
    
        return ['success' => 1];
    }

    public function actionViewTestsLog()
    {
        if (!\Yii::$app->user->can('auto_test_management')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $file = file_get_contents('../scripts/generate_autotest/log.txt');
    
        return [
            'success' => 1,
            'file' => explode("\n", $file)
        ];
    }
}
