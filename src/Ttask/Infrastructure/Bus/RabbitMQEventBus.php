<?php

namespace Ttask\Infrastructure\Bus;

use AMQPQueueException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Ttask\Application\Exception\InvalidEventInTheQueue;
use Ttask\Domain\Events\DomainEvent;
use Ttask\Domain\Events\EventBus;

final class RabbitMqEventBus implements EventBus
{

    protected string               $queue_name;
    protected AMQPChannel          $channel;
    protected AMQPStreamConnection $connection;

    public function __construct()
    {

        $this->queue_name = RABBIT_BUS_QUEUE_NAME;

        // устанавилваем соединение и получаем канал
        $this->connection = new AMQPStreamConnection(
              RABBIT_BUS_HOST,
              RABBIT_BUS_PORT,
              RABBIT_BUS_USER,
              RABBIT_BUS_PASS
        );
        $this->channel    = $this->connection->channel();

        // создаем очередь
        $this->channel->queue_declare($this->queue_name, false, true, false, false);
    }

    public function __destruct()
    {

        $this->closeAll();
    }

    public function publish(DomainEvent $event):void
    {

        //
        $message_text = $this->_serialize($event);

        //
        $msg = new AMQPMessage($message_text, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
        $this->channel->basic_publish($msg, '', $this->queue_name);
    }

    // получить сообщение
    public function consume(callable $work, array $extra = []):void
    {

        if ($this->getQueueSize() < 1) {
            return;
        }

        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume($this->queue_name,
              $extra['consumer_tag'] ?? 'default_consumer',
              false,
              false,
              false,
              false,
              function ($msg) use ($work) {

                  $work(self::_deserialize($msg->body));

                  // сообщить что очередь можно удалть
                  $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);

                  // завершаем работу консамера
                  $this->channel->basic_cancel($extra['consumer_tag'] ?? 'default_consumer');
              });

        $this->channel->wait();
    }

    // закрываем все соединения
    public function closeAll()
    {

        $this->channel->close();
        $this->connection->close();
    }

    public function getQueueSize() {

        $info = $this->channel->queue_declare($this->queue_name, false, true, false, false);
        return isset($info[1]) ? $info[1] : 0;
    }

    // -------------------------------------------------------
    // Protected
    // -------------------------------------------------------

    protected function _serialize(DomainEvent $event):string
    {

        return json_encode([
              'data' => [
                    'event_id'   => $event->id(),
                    'event_name' => $event->name(),
                    'body'       => $event->body(),
              ],
        ]);
    }

    protected function _deserialize(string $message):DomainEvent
    {

        $data = json_decode($message, true);

        if (!isset($data['data'])) {
            throw new InvalidEventInTheQueue();
        }

        $payload = $data['data'];

        if (!isset($payload['event_id']) || !isset($payload['event_name']) || !isset($payload['body'])) {
            throw new InvalidEventInTheQueue();
        }

        return new DomainEvent($payload['body'], $payload['event_name'], $payload['event_id']);
    }
}
