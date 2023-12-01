<?php

namespace  AI\Chat\Kernel\EventStream;
use Hyperf\Engine\Contract\Http\Writable;
use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Response;
class Stream
{
    public function __construct(protected Writable $connection, ?ResponseInterface $response = null)
    {
        /** @var Response $socket */
        $socket = $this->connection->getSocket();
        $socket->header('Content-Type', 'text/event-stream; charset=utf-8');
        $socket->header('Transfer-Encoding', 'chunked');
        $socket->header('Connection', 'keep-alive');
        $socket->header('Access-Control-Allow-Origin', '*');
        $socket->header('Cache-Control', 'no-cache');
        $socket->header('X-Accel-Buffering', 'no');
        foreach ($response?->getHeaders() ?? [] as $name => $values) {
            $socket->header($name, implode(", ", $values));
        }
    }

    public function write(string $data): self
    {
        $this->connection->write($data);
        return $this;
    }

    public function end(): void
    {
        $this->connection->end();
    }
}

