<?php

namespace AI\Chat\Bean;

class SparkBean extends SplBean implements BeanInterface
{
    protected string $appid = '';
    protected string $api_key = '';
    protected string $api_secret = '';
    protected string $addr = '';

    protected string $model = '';//模型
    protected array $messages = [];//消息内容
    protected string $prompt = '';
    protected bool $stream = true;
    protected array $functions = [];//函数

    protected ?string $conversation_id = '';
    protected ?float $temperature = 0;

    public function getTemperature(): ?float
    {
        return $this->temperature;
    }

    public function setTemperature(?float $temperature): void
    {
        $this->temperature = $temperature;
    }

    public function getApiKey(): string
    {
        return $this->api_key;
    }

    public function setApiKey(string $api_key): void
    {
        $this->api_key = $api_key;
    }

    public function getFunctions(): array
    {
        return $this->functions;
    }

    public function setFunctions(array $functions): void
    {
        $this->functions = $functions;
    }

    public function getConversationId(): ?string
    {
        return $this->conversation_id;
    }

    public function setConversationId(?string $conversation_id): void
    {
        $this->conversation_id = $conversation_id;
    }

    public function getStream(): bool
    {
        return $this->stream;
    }

    public function setStream(bool $stream): void
    {
        $this->stream = $stream;
    }

    public function getAppid(): string
    {
        return $this->appid;
    }

    public function setAppid(string $appid): void
    {
        $this->appid = $appid;
    }

    public function getApiSecret(): string
    {
        return $this->api_secret;
    }

    public function setApiSecret(string $api_secret): void
    {
        $this->api_secret = $api_secret;
    }

    public function getAddr(): string
    {
        return $this->addr;
    }

    public function setAddr(string $addr): void
    {
        $this->addr = $addr;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): void
    {
        $this->model = $model;
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
    }//消息内容

}
