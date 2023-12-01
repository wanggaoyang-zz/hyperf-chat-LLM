<?php

namespace AI\Chat\Library;


use AI\Chat\Bean\ChatBean;
use AI\Chat\Bean\ResponseChatBean;
use AI\Chat\Kernel\EventStream\ChatStream;
use Orhanerday\OpenAi\OpenAi;
use AI\Chat\Bean\BeanInterface;

class ChatGpt implements LLMInterface
{
    public function send(BeanInterface $chatBean): ?ResponseChatBean
    {

        /** @var ChatBean $chatBean */
        $prompt = $chatBean->getPrompt();
        $chatBean->setApiKey(\Hyperf\Config\config('llm.storage.ChatGpt.key'));
        $chatBean->setUrl(\Hyperf\Config\config('llm.storage.ChatGpt.url'));
        $responseJsonDescription = $chatBean->getResponseJsonDescription();
        $responseJson = $chatBean->getResponseFormat();
        $openai = new OpenAi($chatBean->getApiKey());
        $openai->setCustomURL($chatBean->getUrl());
        $message = $chatBean->getMessages();
        if ($prompt) {
            $message[] = [
                'role' => 'user',
                'content' => $prompt,
            ];
        }
        //响应json格式
        if(!empty($responseJsonDescription)){
            $message[] = [
                'role' => 'system',
                'content' => $responseJsonDescription
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
            'messages' => $message,
            'model' => $chatBean->getGPTModel(),
            'temperature' => $chatBean->getTemperature(),
            'top_p' => $chatBean->getTopP(),
            'frequency_penalty' => $chatBean->getFrequencyPenalty(),
            'presence_penalty' => $chatBean->getPresencePenalty(),
            "max_tokens" => $chatBean->getMaxTokens(),
            "stream" => $chatBean->getStream(),
            "stop" => $chatBean->getStop(),
            "functions" => $chatBean->getFunctions(),
            "function_call" => $chatBean->getFunctionCall(),
        ];

        if(empty($post['functions'])){
            unset($post['functions'], $post['function_call']);
        }
        //响应json格式
        if(!empty($responseJson)){
            $post['response_format'] = $responseJson;
        }
        $stream = null;
        if ($chatBean->getStream()) {
            $stream = $chatBean->getCallback() ?? $this->handleStreamV1();
        }
        $response = $openai->chat($post, $stream);
        $result = json_decode($response, true) ?: [];
        $choices = $result['choices'] ?? [];
        $usage = $result['usage'] ?? [];
        $error = $result['error'] ?? [];
        if (empty($choices) && $error) {
            self::handleChatGPTAPIError($error);
        }
        $responseBean->setChoices($choices);
        $responseBean->setUsage($usage);
        if ($chatBean->getStream()) {
            return null;
        }
        return $responseBean;
    }

    public function parseData($data): ?ResponseChatBean
    {
        return null;
    }

    public static function handleChatGPTAPIError($response)
    {
        $error_type = $response['error']['type'] ?? '';
        $error_message = $response['error']['message'] ?? '';
        $error_code = $response['error']['code'] ?? $error_type;
        if (empty($error_type) || empty($error_code)) {
            return;
        }

        switch ($error_code) {
            case 'context_length_exceeded':
                $response_data['message'] = '您已超出上下文长度，请缩短消息长度。';
                $response_data['code'] = '上下文长度超限';
                break;
            case 'insufficient_quota':
                $response_data['message'] = '您已超过当前配额，请检查您的计划和账单详细信息。';
                $response_data['code'] = '配额不足';
                break;
            case 'rate_limit_exceeded':
                $response_data['message'] = '您已超出当前速率限制';
                $response_data['code'] = '超出速率限制';
                break;
            case 'account_deactivated':
                $response_data['message'] = '此密钥与已停用的帐户相关联。';
                $response_data['code'] = '账户被禁用';
                break;
            case 'invalid_api_key':
                $response_data['message'] = '此密钥与已停用的帐户相关联。';
                $response_data['code'] = '无效的key';
                break;
            default:
                $response_data['message'] = $error_message;
                $response_data['code'] = '请求出错';
                break;
        }
        throw new \Exception($response_data['message']);
    }

    public function handleStreamV1()
    {
        $text = '';
        return function ($ch, $response) use ( &$text) {
            $error = self::handleEventStreamError($response);
            if ($error) {
                self::handleChatGPTAPIError($error);
            }
            $data = self::parseEventStreamData($response);

            self::processData($data, $text);
            return strlen($response);
        };
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

    public static function processData($data, &$text): bool|array
    {
        $result = false;
        foreach ($data as $message) {
            $data = json_decode($message, true);

            if ('[DONE]' === $message) {
               ChatStream::end("data: [DONE]" . PHP_EOL);
            } else {
                if (!isset($data['id'])) {
                    continue;
                }
                $text .= $data['choices'][0]['delta']['content'] ?? '';
                ChatStream::send("data: " . json_encode($data, 256) . PHP_EOL);
            }
        }
        return $result;
    }

    public static function handleEventStreamError($response)
    {
        if ($errorMsg = json_decode($response, true)) {
            return $errorMsg;
        }
        return false;
    }
}