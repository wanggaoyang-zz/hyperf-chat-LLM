# 使用教程
### 安装

```
composer require hyperf-llm/chat
```
### 发布
```
php bin/hyperf.php vendor:publish hyperf-llm/chat
```
### 使用
```
$chatBean = new ChatBean();
$chatBean->setPrompt('介绍一下hyperf');
$chatBean->setModel('gpt-3.5-turbo');
$llm =  LLMFactory::create();
$llm ->send($chatBean);
```