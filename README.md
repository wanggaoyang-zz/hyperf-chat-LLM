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
$chatBean->setApiKey('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');
$chatBean->setPrompt('给我写一个100字的小说');
$chatBean->setModel('gpt-3.5-turbo');
$this->ChatInterface->send($chatBean);
```