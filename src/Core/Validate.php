<?php

declare(strict_types=1);

namespace S2J\WebinarSurveyService\Core;

final class Validate
{
    /** @var list<string> */
    private const QUESTION_CODE_ORDER = [
        'prompt_empty',
        'answer_kind_invalid',
        'choices_missing',
        'choices_unexpected',
        'rating_bounds_invalid',
    ];

    /**
     * @param array<string, mixed> $document Normalized document (no status).
     *
     * @return list<string>
     */
    public static function deficiencies(array $document): array
    {
        $codes = [];

        if (Document::trimmedString($document['internal_name'] ?? null) === '') {
            $codes[] = 'internal_name_empty';
        }

        $questions = Document::questionsList($document);
        if ($questions === []) {
            $codes[] = 'questions_empty';
        }

        foreach ($questions as $question) {
            $codes = array_merge($codes, self::questionDeficiencies($question));
        }

        return $codes;
    }

    /**
     * @param array<string, mixed> $question
     *
     * @return list<string>
     */
    private static function questionDeficiencies(array $question): array
    {
        $found = [];

        if (Document::trimmedString($question['prompt'] ?? null) === '') {
            $found['prompt_empty'] = true;
        }

        $kind = $question['answer_kind'] ?? null;
        if (!AnswerKind::isValid(is_string($kind) ? $kind : null)) {
            $found['answer_kind_invalid'] = true;
            $kind = null;
        }

        $nonEmptyChoices = Document::normalizeChoices($question['choices'] ?? null);

        if ($kind !== null && AnswerKind::isChoiceKind($kind) && count($nonEmptyChoices) < 2) {
            $found['choices_missing'] = true;
        }

        if ($kind !== null && !AnswerKind::isChoiceKind($kind) && $nonEmptyChoices !== []) {
            $found['choices_unexpected'] = true;
        }

        if ($kind === AnswerKind::RATING && !self::ratingBoundsValid($question)) {
            $found['rating_bounds_invalid'] = true;
        }

        $ordered = [];
        foreach (self::QUESTION_CODE_ORDER as $code) {
            if (isset($found[$code])) {
                $ordered[] = $code;
            }
        }

        return $ordered;
    }

    /**
     * @param array<string, mixed> $question
     */
    private static function ratingBoundsValid(array $question): bool
    {
        $min = $question['score_min'] ?? null;
        $max = $question['score_max'] ?? null;

        if (!is_int($min) || !is_int($max)) {
            return false;
        }

        return $min < $max;
    }

    /**
     * @param list<string> $deficiencies
     */
    public static function statusFromDeficiencies(array $deficiencies): string
    {
        return $deficiencies === [] ? 'ready' : 'draft';
    }
}
