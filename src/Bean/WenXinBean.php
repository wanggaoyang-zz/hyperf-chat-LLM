<?php

namespace AI\Chat\Bean;

class WenXinBean extends SplBean implements BeanInterface
{
    protected string $client_id = '';
    protected string $client_secret = '';
    protected string $model = '';//模型
    protected array $messages = [];//消息内容
    protected string $prompt = '';
    protected bool $stream = true;
    protected array $functions = [];//函数
    protected string|array|null $function_call = 'auto';
    protected ?string $conversation_id = '';
    protected ?float $temperature = 0;
    protected ?float $top_p = 0;
    protected int $is_routine = 0;

    public function getIsRoutine(): int
    {
        return $this->is_routine;
    }

    public function setIsRoutine(int $is_routine): void
    {
        $this->is_routine = $is_routine;
    }

    public function getTopP(): ?float
    {
        return $this->top_p;
    }

    public function setTopP(?float $top_p): void
    {
        $this->top_p = $top_p;
    }

    public function getFunctionCall(): array|string|null
    {
        return $this->function_call;
    }

    public function setFunctionCall(array|string|null $function_call): void
    {
        $this->function_call = $function_call;
    }

    public function getClientId(): string
    {
        return $this->client_id;
    }

    public function setClientId(string $client_id): void
    {
        $this->client_id = $client_id;
    }

    public function getClientSecret(): string
    {
        return $this->client_secret;
    }

    public function setClientSecret(string $client_secret): void
    {
        $this->client_secret = $client_secret;
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
    }

    public function getStream(): bool
    {
        return $this->stream;
    }

    public function setStream(bool $stream): void
    {
        $this->stream = $stream;
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

    public function getTemperature(): ?float
    {
        return $this->temperature;
    }

    public function setTemperature(?float $temperature): void
    {
        $this->temperature = $temperature;
    }

}
