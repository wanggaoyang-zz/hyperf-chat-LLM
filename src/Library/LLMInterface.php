<?php

namespace AI\Chat\Library;



use AI\Chat\Bean\BeanInterface;
use AI\Chat\Bean\ChatBean;
use AI\Chat\Bean\ResponseChatBean;
use AI\Chat\Bean\SplBean;

interface LLMInterface
{
    public function send(BeanInterface $chatBean): ?ResponseChatBean;

    public function parseData($data): ?ResponseChatBean;
}