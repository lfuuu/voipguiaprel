<?php
namespace app\classes;

use app\exceptions\FormValidationException;

class ErrorHandler extends \yii\web\ErrorHandler
{

    protected function convertExceptionToArray($exception)
    {
        $array = parent::convertExceptionToArray($exception);

        if ($exception instanceof FormValidationException) {
            $array['errors'] = $exception->getErrors();
        }

        return $array;
    }

}