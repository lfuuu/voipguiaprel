<?php
namespace app\classes;

use Yii;
use Gelf\Message;

class GelfMessage extends Message
{
    const LOGGER_ID_FIELD = 'LoggerId';
    const USER_ID_FIELD = 'UserId';
    const CATEGORY_FIELD = 'category';

    public static function create()
    {
        return new self();
    }

    public function __construct()
    {
        parent::__construct();
        $this->version = '1.1';
    }

    public function setSource($source)
    {
        if ($source) {
            $this->host = $source;
        }
        return $this;
    }

    public function setCategory($category)
    {
        $this->setAdditional(self::CATEGORY_FIELD, $category);
        return $this;
    }

    public function setLoggerId($loggerId = null)
    {
        $this->setAdditional(
            self::LOGGER_ID_FIELD,
            $loggerId !== null ? $loggerId : $this->getLoggerId()
        );
        return $this;
    }

    public function setUserId($userId = null)
    {
        if ($userId === null && Yii::$app instanceof \yii\web\Application && Yii::$app->session->isActive) {
            $userId = Yii::$app->session->get(Yii::$app->user->idParam);
        }
        if ($userId) {
            $this->setAdditional(self::USER_ID_FIELD, $userId);
        }
        return $this;
    }

    private function getLoggerId()
    {
        static $loggerId;
        if (!$loggerId) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $loggerId = '';
            for ($i = 0; $i < 8; $i++) {
                $loggerId .= $characters[rand(0, strlen($characters) - 1)];
            }
        }
        return $loggerId;
    }

}