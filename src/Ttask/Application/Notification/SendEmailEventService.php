<?php

namespace Ttask\Application\Notification;

use PHPUnit\Framework\MockObject\MockMethod;
use Ttask\Application\EventServiceInterface;
use Ttask\Application\Exception\InvalidEventServiceUsage;
use Ttask\Application\Exception\SendEmailError;
use Ttask\Domain\Events\ArticleCreatedEvent;
use Ttask\Domain\Events\DomainEvent;

/**
 * Class SendEmailEventService
 * @package Ttask\Application\Notification
 *
 *  Отправляем письмо на email автора когда приходит событие о том что статья была создана
 */
class SendEmailEventService implements EventServiceInterface
{

    const EVENT_LIST = [
          ArticleCreatedEvent::EVENT_NAME,
    ];
    protected DomainEvent $event;
    protected int       $send_to_user_id;

    /**
     * @param DomainEvent $event
     *
     *                          Думаю сама реализация здесь не принципиальна
     *                          Поэтому пишу так базово, чтобы можно было
     *                          запустить понять что отработает
     *
     * @throws InvalidEventServiceUsage
     */
    public function work(DomainEvent $event):void
    {
        $this->_init($event);

        // здесь как то достаем email для данного пользователя

        // шлем письмо

        // в случае проблемы
        if (false) {
            throw new SendEmailError();
        }

        if (php_sapi_name() == "cli") {
            print_r("Send-Email service worked for [{$this->send_to_user_id}]." . PHP_EOL);
        }
    }

    // -------------------------------------------------------
    // Protected
    // -------------------------------------------------------

    protected function _init(DomainEvent $event) {

        if (!in_array($event->name(), self::EVENT_LIST)) {
            throw new InvalidEventServiceUsage();
        }

        if (!isset($event->body()['author_id'])) {
            throw new InvalidEventServiceUsage();
        }

        $this->event           = $event;
        $this->send_to_user_id = $event->body()['author_id']; // тут меняется язык автор становится юзером
    }
}