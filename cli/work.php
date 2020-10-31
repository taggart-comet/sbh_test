<?php

require_once __DIR__ . '/../start.php';
ini_set('display_errors', 1);

// в очень простой форме обработчик событий из очереди (нормальная реализация в рамках какого-то фреймворка)
// здесь просто по крону запускается каждую минуту и обрабатывает
// все евенты из очереди (просто чтобы было понимание)


//
$event_bus = new \Ttask\Infrastructure\Bus\RabbitMqEventBus();
$handler = new \Ttask\Application\EventHandler();
$handler->register(
      \Ttask\Domain\Events\ArticleCreatedEvent::EVENT_NAME,
      new \Ttask\Application\Notification\SendEmailEventService()
);

$event_bus->consume(function (\Ttask\Domain\Events\DomainEvent $event) use ($handler) {
    $handler->dispatch($event);
}, ['consumer_tag' => 'consumer_worker0']);
