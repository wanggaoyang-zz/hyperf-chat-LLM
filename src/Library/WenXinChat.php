<?php

namespace AI\Chat\Library;



use AI\Chat\Bean\BeanInterface;
use AI\Chat\Bean\ResponseChatBean;

use AI\Chat\Bean\WenXinBean;
use AI\Chat\Constants\ErrorCode;
use AI\Chat\Kernel\EventStream\ChatStream;
use Exception;
use GuzzleHttp\Client;
use Hyperf\Di\Annotation\Inject;

class WenXinChat implements LLMInterface
{

    #[Inject]
    protected Client $client;

    protected string $base_api = 'https://aip.baidubce.com';
    protected string $ernie_bot_api = '/rpc/2.0/ai_custom/v1/wenxinworkshop/chat/completions';
    protected string $ernie_bot_4_api = '/rpc/2.0/ai_custom/v1/wenxinworkshop/chat/completions_pro';

    public function getAccessToken($client_id, $client_secret): string
    {
        try {
            return cache_has_set('baidu_wenxin_access_token_'.$client_id, function () use ($client_id, $client_secret) {
                $response = $this->client->get($this->base_api . sprintf('/oauth/2.0/token?grant_type=client_credentials&client_id=%s&client_secret=%s', $client_id, $client_secret));
                $body = $response->getBody()->getContents();
                $data = json_decode($body, true);
                return $data['access_token'];
            }, 2590000);
        }catch (\Throwable $e){
            p($e->getMessage());
            throw new Exception('获取token失败');
        }

    }

    public function send(BeanInterface $chatBean): ResponseChatBean
    {
        /** @var WenXinBean $chatBean */
        $chatBean->setClientId(\Hyperf\Config\config('llm.storage.WenXinChat.client_id'));
        $chatBean->setClientSecret(\Hyperf\Config\config('llm.storage.WenXinChat.client_secret'));
        $model = $chatBean->getModel();
        $chat_api = $model == 'ernie_bot_4' ? $this->ernie_bot_4_api : $this->ernie_bot_api;
        $access_token = $this->getAccessToken( $chatBean->getClientId(),  $chatBean->getClientSecret());
        $message = $chatBean->getMessages();
        // 模型人设，主要用于人设设定，例如，你是xxx公司制作的AI助手，说明：
        //（1）长度限制1024个字符
        //（2）如果使用functions参数，不支持设定人设system
        $system = '';
        foreach ($message as $key => $item) {
            if ($item['role'] == 'system') {
                $system .= ($item['content'] ?? '') . PHP_EOL;
                unset($message[$key]);
            }
        }
        $message = array_values($message);
        if(!empty($chatBean->getFunctions())){
            $system = '';
        }
        if(!empty($system)){
            //$system = mb_substr($system, 0, 1024);
            if(mb_strlen($system) > 1024){
                $lastIndex = count($message) - 1; // 获取最后一个元素
                $message[$lastIndex]['content'] = $system . "\r\n我的第一个问题：" . $message[$lastIndex]['content'];
                $system = '';
            }
        }
        // 判断$message元素个数是否为奇数 是则删除第一个元素
        if (count($message) % 2 == 0) {
            array_shift($message);
        }
        $message = array_values($message);
        // function_call
        if(is_array($chatBean->getFunctionCall())){
            $message[] = [
                'role' => 'assistant',
                'content' => null,
                'function_call' => $chatBean->getFunctionCall()
            ];
        }
        $responseBean = new ResponseChatBean([
            'conversation_id' => $chatBean->getConversationId(),
            'id' => uuid(16),
            'created' => time(),
            'object' => 'chat.completion',
            'choices' => [],
        ]);

        $post = [
            'system' => $system,
            'messages' => $message,
            'temperature' => $chatBean->getTemperature(),
            'top_p' => $chatBean->getTopP(),
            "stream" => $chatBean->getStream(),
            "functions" => $chatBean->getFunctions(),
        ];
        if(empty($post['functions'])){
            unset($post['functions']);
        }
        if($post['temperature'] == 0.0){
            unset($post['temperature']);
        }
        if($post['top_p'] == 0.0){
            unset($post['top_p']);
        }

        p($post);
        if($chatBean->getStream() === false){
            try {
                $res = $this->client->post($this->base_api . $chat_api.'?access_token='.$access_token, [
                    'json' => $post,
                ]);
                $res = json_decode($res->getBody()->getContents(), true);
            }catch (\Throwable $throwable){
                self::handleChatAPIError($res);
            }

            // 文心一言 咒语返回json 数据处理
            $res_str = $res['result'] ?? '';
            $pattern = '/```json(.*?)```/s';
            preg_match($pattern, $res_str, $matches);
            $json_str = $matches[1] ?? '';
            if ($json_str) {
                $content = $json_str;
            } else {
                $content = $res_str;
            }
            $choices[0]['message']['content'] = $content;
            $responseBean->setChoices($choices);
            return $responseBean;
        }

        try {
            $this->request($this->base_api . $chat_api.'?access_token='.$access_token, $post, function ($ch, $data) use (&$text, &$result, $chatBean) {
                $bytes = $data_chat = $data;
                $response = json_decode($data_chat, true);
                p($response, '文心流式返回');
                if(isset($response['error_code'])){
                    self::handleChatAPIError( $response);
                }
                // how big is the data transmission
                $bytes = strlen($bytes);
                static $buf = '';
                $buf .= $data;
                while (1) {
                    $pos = strpos($buf, "\n");
                    if ($pos === false) {
                        break;
                    }
                    // trim things down
                    $data = substr($buf, 0, $pos + 1);
                    $buf = substr($buf, $pos + 1);
                    $data = self::parseEventStreamData($data);
                    p('流式开始');
                    foreach ($data as $message) {
                        $data_chat = json_decode($message, true);
                        if ($data_chat['is_end']) {
                            p('流式结束');
                            ChatStream::end("data: [DONE]" . PHP_EOL);
                            break;
                        } else {
                            if (!isset($data_chat['id'])) {
                                continue;
                            }
                            //有函数调用时返回的格式
                            if(isset($data_chat['function_call'])){
                                ChatStream::send("data: " . json_encode([
                                        'name' => $data_chat['function_call']['name'] ?? '',
                                        'arguments' => $data_chat['function_call']['arguments'] ?? '',
                                    ], 256) . PHP_EOL);
                            }else{
                                //没有函数调用时返回的格式
                                $text .= $data_chat['result'] ?? '';
                                $data_chat['choices'][0]['delta']['content'] = $data_chat['result'];
                                unset($data_chat['result']);
                                p(json_encode($data_chat, 256) . PHP_EOL, '流式输出');
                                //兼容下小程序 之前小程序的流式格式略有不同
                                if($chatBean->getIsRoutine() == 1){
                                    ChatStream::send('data: ' . json_encode(['content' => $text, 'id' => time(), 'code' => 200], 256) . PHP_EOL);
                                }else{
                                    ChatStream::send("data: " . json_encode($data_chat, 256) . PHP_EOL);
                                }
                            }
                        }
                    }
                }
                return $bytes;
            });
        } catch (\Throwable $exception) {
            p($exception->getMessage(), '文心http异常');
            throw new Exception($exception->getMessage());
        }
        return $responseBean;
    }


    public function parseData($data): ?ResponseChatBean
    {
        return null;
    }

    public static function parseEventStreamData($response): array
    {
        // file_put_contents(public_path().'/test.jsonl',$response.PHP_EOL.PHP_EOL,FILE_APPEND);
        $data = [];
        $lines = explode("\n", $response);
        foreach ($lines as $line) {
            if (!str_contains($line, ':')) {
                continue;
            }
            [$name, $value] = explode(':', $line, 2);
            if ($name == 'data') {
                $data[] = trim($value);
            }
        }
        return $data;
    }

    public static function handleChatAPIError($response)
    {
        $error_code = $response['error_code'] ?? 400;
        $error_message = $response['error_msg'] ?? '当前服务不可用';
        $exception_code = match ($error_code) {
            1,2,3,4,33600,336100 => ErrorCode::ERROR_WXYY_UNKNOWN_ERROR,
            6,336004,336005 => ErrorCode::ERROR_WXYY_API_NO_PERMISSION,
            13,14,15 => ErrorCode::ERROR_WXYY_TOKEN_FAILED,
            17,18,19 => ErrorCode::ERROR_WXYY_API_LIMIT,
            100,110,111 => ErrorCode::ERROR_WXYY_AUTH_FAILED,
            336001,336002,336003,336101,336104 => ErrorCode::ERROR_WXYY_INVALID,
            default => ErrorCode::ERROR_WXYY_UNKNOWN_ERROR,
        };
        throw new Exception($exception_code);
    }

    public function request($url, $data = [], $fun = null)
    {
        // setup the api request
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, $fun);
        curl_setopt($ch, CURLOPT_TCP_KEEPALIVE, 1);   // 开启
        curl_setopt($ch, CURLOPT_TCP_KEEPIDLE, 10);   // 空闲10秒问一次
        curl_setopt($ch, CURLOPT_TCP_KEEPINTVL, 10);  // 每10秒问一次
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json; charset=utf-8',
            ]
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

}