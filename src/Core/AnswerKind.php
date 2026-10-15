<?php

declare(strict_types=1);

namespace S2J\WebinarSurveyService\Core;

final class AnswerKind
{
    public const SINGLE = 'single';

    public const MULTIPLE = 'multiple';

    public const SHORT = 'short';

    public const LONG = 'long';

    public const RATING = 'rating';

    /** @var list<string> */
    public const ALL = [
        self::SINGLE,
        self::MULTIPLE,
        self::SHORT,
        self::LONG,
        self::RATING,
    ];

    public static function isValid(?string $kind): bool
    {
        return $kind !== null && in_array($kind, self::ALL, true);
    }

    public static function isChoiceKind(string $kind): bool
    {
        return $kind === self::SINGLE || $kind === self::MULTIPLE;
    }

    public static function isOpenKind(string $kind): bool
    {
        return $kind === self::SHORT || $kind === self::LONG;
    }

    public static function isClosedKind(string $kind): bool
    {
        return self::isChoiceKind($kind) || $kind === self::RATING;
    }
}
