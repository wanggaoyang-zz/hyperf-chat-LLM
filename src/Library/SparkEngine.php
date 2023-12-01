<?php

namespace AI\Chat\Library;

use App\Bean\ResponseChatBean;
use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Kernel\AIEngine\Engine\EngineAlarm;
use App\Kernel\ChatEngine\Engine\BaseEngine;
use App\Kernel\EventStream\ChatStream;
use App\Model\ApiAccount;
use DateTime;
use Hyperf\Di\Annotation\Inject;
use Hyperf\WebSocketClient\ClientFactory;

class SparkEngine extends BaseEngine
{

    #[Inject]
    protected ClientFactory $clientFactory;

    protected string $addr = 'wss://spark-api.xf-yun.com/v3.1/chat';

    public function send(): ?ResponseChatBean
    {
        $prompt    = $this->chatBean->getPrompt();
        $messages    = $this->chatBean->getMessages();
        $messageId = $this->chatBean->getMessageId();
        $model = $this->chatBean->getModel();
        $stream = $this->chatBean->getStream();
        $functions = $this->chatBean->getFunctions();

        $responseBean = new ResponseChatBean([
            'conversation_id'   => $this->chatBean->getConversationId(),
            'parent_message_id' => $messageId,
            'user_id'           => $this->chatBean->getUserId(),
            'id' => uuid(16),
            'created' => time(),
            'object' => 'chat.completion',
            'choices'           => [],
        ]);

//        $appid = "c7ddfd0a";
//        $api_key = "f8846795bac912ca335f85b835991eac";
//        $api_secret = "YmY2YjYxMjFiMjU1NjEyZGFmMjY5MzZj";
        $api_key = $this->chatBean->getApiKey($model);
        $api_secret = $this->chatBean->getApiOption('api_secret');
        $appid = $this->chatBean->getApiOption('appid');

        $chat_url = $this->assembleAuthUrl($api_secret, $api_key, $this->addr);
        $client = $this->clientFactory->create($chat_url);
        // 发送数据到 WebSocket 服务器
        $data = $this->getBody($appid,$messages,$functions);
        p($data, '发送给星火的body');
        $client->push($data);
        // $response = $client->recv()->data;
        // $resp = json_decode($response,true);
        // p($resp);exit();

        // 从 WebSocket 服务器接收数据
        $answer = "";
        while(true){
            $response = $client->recv()->data;
            if (empty($response)){
                break;
            }
            $resp = json_decode($response,true);
            $code = $resp["header"]["code"];
            p("从星火服务器接收到的数据： " . $response);
            //break;
            if(0 == $code){//0表示正常，非0表示出错
                $status = $resp["header"]["status"];// 0代表首个文本结果；1代表中间文本结果；2代表最后一个文本结果
                $content = $resp['payload']['choices']['text'][0]['content'];
                $function_call = $resp['payload']['choices']['text'][0]['function_call'] ?? null;
                $data = [
                    'model' => $this->chatBean->getModel(),
                    'id' => $resp['header']['sid'] ?? microtime(),
                    'object' => 'chat.completion.chunk',
                    'choices' => [
                        [
                            'index' => 0,
                            'delta' => [
                                'role' => 'assistant',
                                'content' => $content,
                                'function_call' => $function_call,
                            ],
                            'finish_reason' => null,
                        ]
                    ],
                    'created' => time()
                ];
                if($status == 2){
                    $answer .= $content;
                    //$total_tokens = $resp['payload']['usage']['text']['total_tokens'];
//                $message = Message::query()->where('message_id', $this->chatBean->getMessageId())->first();
//                if($message){
//                    $message->response_content = $text;
//                    $message->save();
//                }
                    if($stream === true){
                        ChatStream::send("data: " . json_encode($data, 256) . PHP_EOL);
                        ChatStream::end("data: [DONE]" . PHP_EOL);
                        p("data: [DONE]" . PHP_EOL);
                    }
                    //p("本次消耗token用量：".$total_tokens);
                    break;
                }else{
                    if($stream === true){
                        //p("data: " . json_encode($data, 256) . PHP_EOL);
                        ChatStream::send("data: " . json_encode($data, 256) . PHP_EOL);
                    }
                    $answer .= $content;
                }
            }else{
                p("服务返回报错".$response);
                $error_code = $resp["header"]["code"] ?? 0;
                $error_message = $resp["header"]["message"] ?? '';
                $apiAccount = ApiAccount::query()->where('key_id', $this->chatBean->getKeyId())->first();
                EngineAlarm::make()
                    ->setEngineName('讯飞星火')
                    ->setKeyId($apiAccount->key_id)
                    ->setApiKey($apiAccount->api_key)
                    ->setErrorMsg($error_message)
                    ->setErrorCode($error_code)
                    ->send();
                $exception_code = match ($error_code) {
                    10907,11200,11201,11202,11203 => ErrorCode::ERROR_SPARK_API_LIMIT,
                    10013,10014,10015,10019 => ErrorCode::ERROR_SPARK_INVALID,
                    default => ErrorCode::ERROR_SPARK_UNKNOWN_ERROR,
                };
                throw new BusinessException($exception_code);
            }
        }
        //p('返回结果为:'.$answer);
        $client->close();
        if($stream === false){
            return $this->returnNoStream($responseBean, $answer);
        }
        return $responseBean;
    }

    public function returnNoStream(ResponseChatBean $responseBean, $content)
    {
        $choices[0]['message']['content'] = $content;
        $responseBean->setChoices($choices);
        return $responseBean;
    }

    //构造参数体
    public function getBody($appid,$messages,$functions)
    {
        $header = array(
            "app_id" => (string)$appid,
            "uid" => (string)($this->chatBean->getUserId() ?? 1)
        );

        $temperature = $this->chatBean->getTemperature() ?? 0.5;
        if($temperature <= 0 ){
            $temperature = 0.5;
        }
//        $max_tokens = $this->chatBean->getMaxTokens() ?? 8192;
//        if($max_tokens <= 0 ){
//            $max_tokens = 8192;
//        }
        $parameter = array(
            "chat" => array(
                "domain" => "generalv3",
                "temperature" => $temperature,
                "max_tokens" => 8192
            )
        );

        $system = '';
        foreach ($messages as $key => $item) {
            if ($item['role'] == 'system') {
                $system .= ($item['content'] ?? '') . PHP_EOL;
                unset($messages[$key]);
            }
        }
        $messages = array_values($messages);
        if(!empty($system)){
            $lastIndex = count($messages) - 1; // 获取最后一个元素
            $messages[$lastIndex]['content'] = $system . "\r\n我的第一个问题：" . $messages[$lastIndex]['content'];
        }


        $payload = array(
            "message" => array(
                "text" => $messages
            ),
        );
        if(!empty($functions)){
            $payload['functions'] = ['text' => $functions];
        }

        $json_string = json_encode(array(
            "header" => $header,
            "parameter" => $parameter,
            "payload" => $payload,
        ));

        return $json_string;

    }

    public function assembleAuthUrl($api_secret, $api_key, $addr)
    {
        $url_components = parse_url($addr);
        // 生成RFC1123格式的时间戳
        $date = DateTime::createFromFormat('U', time())->format('D, d M Y H:i:s T');

        // 拼接字符串
        $signature_origin = "host: " . $url_components['host'] . "\n";
        $signature_origin .= "date: " . $date . "\n";
        $signature_origin .= "GET " . $url_components['path'] . " HTTP/1.1";

        // 进行hmac-sha256进行加密
        $signature_sha = hash_hmac('sha256', utf8_encode($signature_origin), utf8_encode($api_secret), true);
        $signature_sha_base64 = base64_encode($signature_sha);
        $authorization_origin = 'api_key="' . $api_key . '", algorithm="hmac-sha256", headers="host date request-line", signature="' . $signature_sha_base64 . '"';
        $authorization = base64_encode(utf8_encode($authorization_origin));

        // 将请求的鉴权参数组合为字典
        $v = [
            "authorization" => $authorization,
            "date"          => $date,
            "host"          => $url_components['host']
        ];
        // 拼接鉴权参数，生成url
        return $addr . '?' . http_build_query($v);
    }

    public function check($app_id, $api_key, $api_secret)
    {
        $chat_url = $this->assembleAuthUrl($api_secret, $api_key, $this->addr);
        $client = $this->clientFactory->create($chat_url);
        // 发送数据到 WebSocket 服务器
        $data = $this->getBody($app_id,'1+1=?', []);
        $client->push($data);
        $response = $client->recv()->data;
        $resp = json_decode($response,true);
        $code = $resp["header"]["code"] ?? 0;
        $message = $resp["header"]["message"] ?? 0;
        return [
            'error_code' => $code,
            'error_message' => $message,
        ];
    }

    public function parseData($data): ?ResponseChatBean
    {
        return new ResponseChatBean($data);
    }
}