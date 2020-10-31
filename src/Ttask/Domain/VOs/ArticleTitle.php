<?php

namespace Ttask\Domain\VOs;

use Ttask\Application\Exception\Article\InvalidArticleTitle;

// NOTE: статьи уникальны по title. Если такой title есть в базе, то обновляем статью.
// это гарантируется тем что article_id формируется как md5 от article_title
final class ArticleTitle extends StringValueObject  {

    const MAX_LENGTH = 300;
    const MIN_LENGTH = 2;

    /**
     * ArticleText constructor.
     *
     * @param string $value
     *
     * @throws InvalidArticleTitle
     */
    public function __construct(string $value)
    {
        if (mb_strlen($value) > self::MAX_LENGTH || mb_strlen($value) < self::MIN_LENGTH) {
            throw new InvalidArticleTitle();
        }

        parent::__construct($value);
    }
}