<?php

namespace app\exceptions;

use yii\db\ActiveRecord;
use yii\web\HttpException;

class ModelValidationException extends HttpException
{
    const STATUS_CODE = 400;

    private $_errors = [];

    private $_model = null;

    /**
     * @param ActiveRecord $model
     * @param int $errorCode код ошибки для API
     * @param int $statusCode http-код для браузера
     */
    public function __construct(ActiveRecord $model, $errorCode = 0, $statusCode = ModelValidationException::STATUS_CODE)
    {
        $this->_model = $model;
        $this->_errors = $model->getErrors();
        parent::__construct($statusCode, 'Error. ' . get_class($model) . ' ' . print_r($model->getPrimaryKey(), true) . ': ' . implode(' ', $model->getFirstErrors()), $errorCode);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Получение модели
     *
     * @return ActiveRecord
     */
    public function getModel()
    {
        return $this->_model;
    }
}