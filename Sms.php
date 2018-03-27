<?php
/**
 * @author Самсонов Владимир <samsonov.sem@gmail.com>
 * @copyright Copyright &copy; S.E.M. 2017-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
namespace sem\smsru;

use Yii;
use yii\base\Component;
use yii\helpers\Json;
use Zelenin\SmsRu\Api;
use Zelenin\SmsRu\Auth\ApiIdAuth;
use Zelenin\SmsRu\Client\Client;
use Zelenin\SmsRu\Entity\Sms as BasicSms;
use Zelenin\SmsRu\Entity\SmsPool as BasicPoolSms;
use Zelenin\SmsRu\Response\SmsResponse;

/**
 * Компонент отправки SMS-сообщений через сервис sms.ru
 * 
 * @see http://sms.ru/api
 * @see https://github.com/zelenin/sms_ru
 */
class Sms extends Component
{

    /**
     * Код успешно завершенного запроса
     */
    const RESPONSE_SUCCESS_CODE = '100';

    /**
     * @var string уникальный ключ-идентификатор подписки на сервис SMS.ru 
     */
    public $api_id;

    /**
     * @var float стоимость одного SMS-сообщения стандартной длины
     */
    public $oneSmsCost = 1.00;

    /**
     * @var array дополнительная конфигурация для Guzzle-клиента
     */
    public $config = [];

    /**
     * @var \Zelenin\SmsRu\Api объект, предназначенный для работы с сервисом SMS.RU
     */
    protected $_client;

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->_client = new Api(new ApiIdAuth($this->api_id));
        $this->_client->setClient(new Client($this->config));
    }

    /**
     * Проверяет наличие положительного баланса для отправки хотябы трех SMS стандартной длины
     * 
     * @return boolean
     */
    public function haveMoney()
    {
        $have = false;

        $smsBalance = $this->_client->myBalance();
        if ($smsBalance->code == self::RESPONSE_SUCCESS_CODE && $smsBalance->balance >= ($this->oneSmsCost * 3)) {
            $have = true;
        }

        return $have;
    }

    /**
     * Производит отправку SMS-сообщения
     * 
     * @param string $phone
     * @param string $message
     * @return \Zelenin\SmsRu\Response\SmsResponse
     */
    public function send($phone, $message)
    {
        if (YII_DEBUG) {
            $response = new SmsResponse(self::RESPONSE_SUCCESS_CODE);
            $response->ids = [
                rand(1, 9999)
            ];
            Yii::info(Json::encode([
                    'phone' => $phone,
                    'message' => $message
            ]));
        } else {
            $response = $this->_client->smsSend(new BasicSms($phone, $message));
        }

        return $response;
    }

    /**
     * Производит отправку нескольких SMS-сообщений
     * 
     * @param array $batch массив с информацией об отправляемых SMS-сообщениях
     * @return \Zelenin\SmsRu\Response\SmsResponse
     */
    public function sendPool($batch)
    {
        $pool = [];
        foreach ($batch as $info) {
            $pool[] = new BasicSms($info['phone'], $info['message']);
        }

        if (YII_DEBUG) {
            $response = new SmsResponse(self::RESPONSE_SUCCESS_CODE);
            $idx = rand(1, 9999);
            $smses = [];
            foreach ($batch as $sms) {
                $response->ids[] = $idx ++;
                $smses[] = $info;
            }
            Yii::info(Json::encode($smses));
        } else {
            $response = $this->_client->smsSend(new BasicPoolSms($pool));
        }

        return $response;
    }
}
