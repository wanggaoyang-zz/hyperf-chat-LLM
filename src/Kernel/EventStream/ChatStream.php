<?php

declare(strict_types=1);
namespace  AI\Chat\Kernel\EventStream;

use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Contract\ResponseInterface;

final class ChatStream
{
    /**
     * 向客户端发送流消息，目前只支持同步，不支持队列或异步，后续用 uid扩展支持队列或异步.
     */
    public static function send($data, $uid = null)
    {
        $eventStream = self::getClient();
        if (is_array($data)) {
            $data = json_encode($data, 256);
        }
        //        $eventStream->write('data: ' .$data. PHP_EOL . PHP_EOL);
        return $eventStream->write($data . PHP_EOL. PHP_EOL );
    }

    public static function end(string $data = '[DONE]')
    {
        $eventStream = self::getClient();
        //        $eventStream->write('data: ' . $data . PHP_EOL . PHP_EOL);
        $eventStream->write($data . PHP_EOL. PHP_EOL );
        $eventStream->end();
        return true;
    }

    public static function getClient()
    {
        if ($eventStream = Context::get('client:event')) {
            return $eventStream;
        }
        $response = ApplicationContext::getContainer()->get(ResponseInterface::class);
        $eventStream = new Stream($response->getConnection());


        Context::set('client:event', $eventStream);
        return $eventStream;
    }

}