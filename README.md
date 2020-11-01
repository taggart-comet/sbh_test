# sbh test task

### Что-куда смотреть:
- Основная логика в `./src/Ttask`
- Тесты в папке `./tests`

### Запустить с `docker-compose`
- После `up` поднимет стандартные порты 80, 3306, 5672
- Через постман можно пробовать так:
    - `GET`: `localhost/api/v1/article/list`
    - `PUT`: `localhost/api/v1/article/save` (тут в json-body передать `title`,`author_name`,`text`)