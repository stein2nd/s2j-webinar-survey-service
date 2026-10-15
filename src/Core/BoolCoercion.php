<?php

declare(strict_types=1);

namespace S2J\WebinarSurveyService\Core;

final class BoolCoercion
{
    public static function coerce(mixed $value, bool $keyPresent): bool
    {
        if (!$keyPresent) {
            return false;
        }

        if ($value === true || $value === 1) {
            return true;
        }

        if ($value === false || $value === 0 || $value === null) {
            return false;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            if ($normalized === '1' || $normalized === 'true' || $normalized === 'yes' || $normalized === 'on') {
                return true;
            }

            if ($normalized === '0' || $normalized === 'false' || $normalized === '') {
                return false;
            }
        }

        return false;
    }
}
