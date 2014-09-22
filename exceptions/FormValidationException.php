<?php
namespace app\exceptions;

use yii\base\Model;
use yii\web\BadRequestHttpException;

class FormValidationException extends BadRequestHttpException
{
    private $errors = [];

    public function __construct(Model $form)
    {
        $this->errors = $form->getErrors();
        parent::__construct();
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
