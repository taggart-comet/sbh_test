<?php

namespace Api\Controllers;

/**
 * Интерфейс для контроллеров, определяем константы
 */
abstract class ControllerInterface {

    /**
     * Типы реквестов к ресурсу
     * Например: получить вссе ресурсы, получить по айди (int, string)
     */
    const ALLOWED_REQUESTS = [];

    /**
     * Определяем разрешенные действия БЕЗ указания айди ресурса и http методы для них
     * Example: 'get' => Handler::METHOD_GET, будет для: GET api/v1/controller/get
     */
    const ALLOWED_ACTIONS = [];

    /**
     * Определяем разрешенные действия по id and HTTP methods для них
     * Example: 'editName' => Handler::METHOD_POST, будет для: POST api/v1/controller/123/edit-name
     */
    const ALLOWED_ID_ACTIONS = [];

    // -------------------------------------------------------
    // Direct Actions section
    // -------------------------------------------------------

    // здесь ALLOWED_ACTIONS

    // -------------------------------------------------------
    // ID Actions section
    // -------------------------------------------------------

    // здесь ALLOWED_ID_ACTIONS

}