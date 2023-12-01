<?php

namespace AI\Chat\Bean;


class ResponseChatBean extends SplBean
{
    protected ?array $data;
    protected ?string $conversation_id = '';
    protected ?string $parent_message_id = '';
    protected ?int $user_id = 0;

    protected string $id = '';
    protected string $object = '';
    protected int $created = 0;
    protected array $choices = [];
    protected array $usage = [];
    private ?string $model = null;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getObject(): string
    {
        return $this->object;
    }

    /**
     * @param string $object
     */
    public function setObject(string $object): void
    {
        $this->object = $object;
    }

    /**
     * @return int
     */
    public function getCreated(): int
    {
        return $this->created;
    }

    /**
     * @param int $created
     */
    public function setCreated(int $created): void
    {
        $this->created = $created;
    }

    /**
     * @return array
     */
    public function getChoices(): array
    {
        return $this->choices;
    }

    /**
     * @param array $choices
     */
    public function setChoices(array $choices): void
    {
        $this->choices = $choices;
    }

    /**
     * @return array
     */
    public function getUsage(): array
    {
        return $this->usage;
    }

    /**
     * @param array $usage
     */
    public function setUsage(array $usage): void
    {
        $this->usage = $usage;
    }

    /**
     * @return string|null
     */
    public function getConversationId(): ?string
    {
        return $this->conversation_id;
    }

    /**
     * @param string|null $conversation_id
     */
    public function setConversationId(?string $conversation_id): void
    {
        $this->conversation_id = $conversation_id;
    }

    /**
     * @return string|null
     */
    public function getParentMessageId(): ?string
    {
        return $this->parent_message_id;
    }

    /**
     * @param string|null $parent_message_id
     */
    public function setParentMessageId(?string $parent_message_id): void
    {
        $this->parent_message_id = $parent_message_id;
    }

    /**
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    /**
     * @param int|null $user_id
     */
    public function setUserId(?int $user_id): void
    {
        $this->user_id = $user_id;
    }

    /**
     * @return array|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * @param array|null $data
     */
    public function setData(?array $data): void
    {
        $this->data = $data;
    }


    /**
     * @return string|null
     */
    public function getModel(): ?string
    {
        return $this->model;
    }

    /**
     * @param string|null $model
     */
    public function setModel(?string $model): void
    {
        $this->model = $model;
    }
}
