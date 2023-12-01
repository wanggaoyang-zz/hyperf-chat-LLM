<?php

namespace AI\Chat\Bean;

class ChatBean extends SplBean implements BeanInterface
{
    protected string $api_key = '';//key chatGPT的秘钥
    protected string $url = '';//请求地址
    protected string $model = '';//模型
    protected array $messages = [];//消息内容
    protected string $prompt = '';//消息内容
    protected ?array $response_format = [];//响应json格式
    protected ?string $response_json_description = '';//响应json格式描述

    protected bool $stream = true;

    protected ?\Closure $callback = null;//回调
    protected array $functions = [];//函数
    protected string|array|null $function_call = 'auto';
    protected ?string $conversation_id = '';
    protected ?float $temperature = 0;
    protected ?float $top_p = 0;
    protected ?float $frequency_penalty = 0;
    protected ?float $presence_penalty = 0;
    protected int $max_tokens = 2000;
    protected array $stop = [
        "\n",
    ];

    public function getApiKey(): string
    {
        return $this->api_key;
    }

    public function setApiKey(string $api_key): void
    {
        $this->api_key = $api_key;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function setMessages(array $messages): void
    {
        $this->messages = $messages;
    }

    public function getPrompt(): string
    {
        return $this->prompt;
    }

    public function setPrompt(string $prompt): void
    {
        $this->prompt = $prompt;
    }

    public function getResponseJsonDescription(): ?string
    {
        return $this->response_json_description;
    }

    public function setResponseJsonDescription(?string $response_json_description): void
    {
        $this->response_json_description = $response_json_description;
    }

    public function getStream(): bool
    {
        return $this->stream;
    }

    public function setStream(bool $stream): void
    {
        $this->stream = $stream;
    }

    public function getCallback(): ?\Closure
    {
        return $this->callback;
    }

    public function setCallback(?\Closure $callback): void
    {
        $this->callback = $callback;
    }

    public function getFunctions(): array
    {
        return $this->functions;
    }

    public function setFunctions(array $functions): void
    {
        $this->functions = $functions;
    }

    public function getFunctionCall(): array|string|null
    {
        return $this->function_call;
    }

    public function setFunctionCall(array|string|null $function_call): void
    {
        $this->function_call = $function_call;
    }

    public function getConversationId(): ?string
    {
        return $this->conversation_id;
    }

    public function setConversationId(?string $conversation_id): void
    {
        $this->conversation_id = $conversation_id;
    }

    public function getTemperature(): ?float
    {
        return $this->temperature;
    }

    public function setTemperature(?float $temperature): void
    {
        $this->temperature = $temperature;
    }

    public function getTopP(): ?float
    {
        return $this->top_p;
    }

    public function setTopP(?float $top_p): void
    {
        $this->top_p = $top_p;
    }

    public function getFrequencyPenalty(): ?float
    {
        return $this->frequency_penalty;
    }

    public function setFrequencyPenalty(?float $frequency_penalty): void
    {
        $this->frequency_penalty = $frequency_penalty;
    }

    public function getPresencePenalty(): ?float
    {
        return $this->presence_penalty;
    }

    public function setPresencePenalty(?float $presence_penalty): void
    {
        $this->presence_penalty = $presence_penalty;
    }

    public function getMaxTokens(): int
    {
        return $this->max_tokens;
    }

    public function setMaxTokens(int $max_tokens): void
    {
        $this->max_tokens = $max_tokens;
    }

    public function getStop(): array
    {
        return $this->stop;
    }

    public function setStop(array $stop): void
    {
        $this->stop = $stop;
    }



    public function getResponseFormat(): ?array
    {
        return $this->response_format;
    }

    public function setResponseFormat(array|string|null $response_format): void
    {
        if(empty($response_format)){
            $this->response_format =  [];
        }elseif(is_string($response_format)){
            $this->response_format = ["type"=> "$response_format"];
        }else{
            $this->response_format = $response_format;
        }

    }

    public function context($message, $role = 'system')
    {
        if (empty($message)) {
            return;
        }
        $this->messages[] = [
            'content' => $message,
            'role' => $role,
        ];
    }

    /**
     * @return string
     */
    public function setModel($model)
    {
        $this->model = $model;
    }
    /**
     * @return string
     */
    public function getModel(): string
    {
        if ($this->model == 'ChatGPT') {
            return 'gpt-3.5-turbo';
        }
        return $this->model;
    }

    public function getGPTModel(): string
    {

        if (str_contains($this->model, 'gpt-3.5-turbo-16k')) {
            if(!empty($this->getResponseFormat())){
                return 'gpt-3.5-turbo-1106';
            }
            return 'gpt-3.5-turbo-16k';
        }
        if (str_contains($this->model, 'gpt-3.5')) {
            if(!empty($this->getResponseFormat())){
                return 'gpt-3.5-turbo-1106';
            }
            return 'gpt-3.5-turbo';
        }
        if (str_contains($this->model, 'gpt-4')) {
            if(!empty($this->getResponseFormat())){
                return 'gpt-4-1106-preview';
            }
            return 'gpt-4';
        }
        return $this->model;
    }





}
