# 使用教程
### 安装

```
composer require hyperf-llm/chat
```
### 发布
```
php bin/hyperf.php vendor:publish hyperf-llm/chat
```
### ChatGPT使用
```
llm配置项'default'设置为'ChatGpt',

$chatBean = new ChatBean();
$chatBean->setPrompt('介绍一下hyperf');
$chatBean->setModel('gpt-3.5-turbo');
$llm =  LLMFactory::create();
$llm ->send($chatBean);
```

### 星火使用
```
llm配置项'default'设置为'Spark',

$chatBean = new SparkBean();
$chatBean->setPrompt('介绍一下hyperf');
$llm =  LLMFactory::create();
$llm ->send($chatBean);
```

### 文心一言
```
llm配置项'default'设置为'WenXin',

$chatBean = new WenXinBean();
$chatBean->setPrompt('介绍一下hyperf');
$llm =  LLMFactory::create();
return $llm ->send($chatBean);
```