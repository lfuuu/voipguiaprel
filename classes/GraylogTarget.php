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
    const SHORT_MESSAGE_LEN = 150;
    /**
     * @var string Graylog2 host
     */
    public $host = '127.0.0.1';

    /**
     * @var integer Graylog2 port
     */
    public $port = 12201;

    public $source;

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
        $publisher = $this->spawnPublisher();

        foreach ($this->messages as $message) {
            $publisher->publish(
                $this->spawnGelfMessage($message)
            );
        }
    }

    private function spawnPublisher()
    {
        return new Gelf\Publisher(
            new Gelf\Transport\UdpTransport(
                $this->host,
                $this->port,
                Gelf\Transport\UdpTransport::CHUNK_SIZE_LAN
            )
        );
    }

    private function spawnGelfMessage(array $yiiMessage)
    {
        list($timeStamp, $level, $shortMessage, $fullMessage, $category, $file) = $this->parseYiiMessage($yiiMessage);

        return
            GelfMessage::create()
                ->setSource($this->source)
                ->setTimestamp($timeStamp)
                ->setLevel($level)
                ->setFacility($this->falicity)
                ->setShortMessage($shortMessage)
                ->setFullMessage($fullMessage)
                ->setCategory($category)
                ->setFile($file)
                ->setLoggerId()
                ->setUserId()
            ;
    }

    private function parseYiiMessage(array $yiiMessage)
    {
        $timeStamp = $yiiMessage[3];
        $level = ArrayHelper::getValue($this->_levels, $yiiMessage[1], LogLevel::INFO);
        $fullMessage = is_string($yiiMessage[0]) ? $yiiMessage[0] : VarDumper::dumpAsString($yiiMessage[0]);
        $category = $yiiMessage[2];

        if (isset($yiiMessage[4][0]['file'])) {
            $file = $yiiMessage[4][0]['file'] . ($yiiMessage[4][0]['line'] ? ' [' . $yiiMessage[4][0]['line'] . ']' : '');
        } else {
            $file = null;
        }

        if (mb_strlen($fullMessage) > self::SHORT_MESSAGE_LEN) {
            $shortMessage = mb_substr($fullMessage, 0, 150);
        } else {
            $shortMessage = $fullMessage;
            $fullMessage = null;
        }

        return [$timeStamp, $level, $shortMessage, $fullMessage, $category, $file];
    }
}