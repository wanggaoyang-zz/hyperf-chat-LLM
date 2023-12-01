<?php

namespace AI\Chat\Library;


use Exception;

class LLMFactory
{
    public static function create():LLMInterface
    {
        $llmDrive = \Hyperf\Config\config('llm.default');
        $class = 'AI\\Chat\\Library\\' . $llmDrive;
        if (!class_exists($class)) {
            throw new Exception('引擎不存在');
        }
        return \Hyperf\Support\make($class);
    }
}