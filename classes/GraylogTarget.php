<?php
namespace app\classes;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\log\Target;
use yii\log\Logger;
use Gelf;
use Psr\Log\LogLevel;

class GraylogTarget extends Target
{
    /**
     * @var string Graylog2 host
     */
    public $host = '127.0.0.1';

    /**
     * @var integer Graylog2 port
     */
    public $port = 12201;

    /**
     * @var string default facility name
     */
    public $falicity;

    public $exportInterval = 1;

    public $logVars = [];

    /**
     * @var array graylog levels
     */
    private $_levels = [
        Logger::LEVEL_TRACE => LogLevel::DEBUG,
        Logger::LEVEL_PROFILE_BEGIN => LogLevel::DEBUG,
        Logger::LEVEL_PROFILE_END => LogLevel::DEBUG,
        Logger::LEVEL_INFO => LogLevel::INFO,
        Logger::LEVEL_WARNING => LogLevel::WARNING,
        Logger::LEVEL_ERROR => LogLevel::ERROR,
    ];

    /**
     * Sends log messages to Graylog2 input
     */
    public function export()
    {
        $transport = new Gelf\Transport\UdpTransport($this->host, $this->port, Gelf\Transport\UdpTransport::CHUNK_SIZE_LAN);
        $publisher = new Gelf\Publisher($transport);
        foreach ($this->messages as $message) {
            $gelfMsg = new Gelf\Message();
            $gelfMsg->setVersion('1.1');
            $msg = is_string($message[0]) ? $message[0] : VarDumper::export($message[0]);
            $gelfMsg
                ->setTimestamp($message[3])
                ->setLevel(ArrayHelper::getValue($this->_levels, $message[1], LogLevel::INFO))
                ->setFacility($this->falicity)
                ->setAdditional('category', $message[2])
                ->setShortMessage(mb_substr($msg, 0, 150))
                ->setFullMessage($msg)
            ;

            if (isset($message[4][0]['file'])) {
                $gelfMsg->setFile($message[4][0]['file'] . ($message[4][0]['line'] ? ' [' . $message[4][0]['line'] . ']' : ''));
            }

            $gelfMsg->setAdditional('LoggerId', self::getLoggerId());

            if (Yii::$app instanceof \yii\web\Application
                    &&
                Yii::$app->getSession()->getIsActive()
            ) {
                $gelfMsg->setAdditional('UserId', Yii::$app->getSession()->get(Yii::$app->getUser()->idParam));
            }

            $publisher->publish($gelfMsg);
        }
    }

    private static function getLoggerId()
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