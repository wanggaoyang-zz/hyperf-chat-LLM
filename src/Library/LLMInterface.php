<?php

namespace AI\Chat\Library;



use AI\Chat\Bean\ChatBean;
use AI\Chat\Bean\ResponseChatBean;

interface LLMInterface
{
    public function send(ChatBean $chatBean): ?ResponseChatBean;

    public function parseData($data): ?ResponseChatBean;
}