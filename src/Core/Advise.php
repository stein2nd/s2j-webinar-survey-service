<?php

declare(strict_types=1);

namespace S2J\WebinarSurveyService\Core;

final class Advise
{
    /**
     * @param array<string, mixed> $document Normalized document.
     *
     * @return list<string>
     */
    public static function advice(array $document, int $maxQuestions): array
    {
        $questions = Document::questionsList($document);
        $codes = [];

        if (count($questions) > $maxQuestions) {
            $codes[] = 'too_many';
        }

        if (self::hasOpenBeforeClosed($questions)) {
            $codes[] = 'open_before_closed';
        }

        if (self::hasIdentityNotLast($questions)) {
            $codes[] = 'identity_not_last';
        }

        foreach ($questions as $question) {
            if (($question['required'] ?? false) === true) {
                $kind = $question['answer_kind'] ?? '';
                if (is_string($kind) && AnswerKind::isOpenKind($kind)) {
                    $codes[] = 'required_open';
                }
            }

            if (Document::trimmedString($question['purpose'] ?? null) === '') {
                $codes[] = 'purpose_missing';
            }
        }

        return $codes;
    }

    /**
     * @param list<array<string, mixed>> $questions
     */
    private static function hasOpenBeforeClosed(array $questions): bool
    {
        $lastOpenIndex = null;
        foreach ($questions as $index => $question) {
            $kind = $question['answer_kind'] ?? null;
            if (!is_string($kind)) {
                continue;
            }
            if (AnswerKind::isOpenKind($kind)) {
                $lastOpenIndex = $index;
            }
        }

        if ($lastOpenIndex === null) {
            return false;
        }

        foreach ($questions as $index => $question) {
            if ($index <= $lastOpenIndex) {
                continue;
            }
            $kind = $question['answer_kind'] ?? null;
            if (is_string($kind) && AnswerKind::isClosedKind($kind)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<array<string, mixed>> $questions
     */
    private static function hasIdentityNotLast(array $questions): bool
    {
        if ($questions === []) {
            return false;
        }

        $lastIndex = count($questions) - 1;
        foreach ($questions as $index => $question) {
            if (($question['identifies_respondent'] ?? false) === true && $index !== $lastIndex) {
                return true;
            }
        }

        return false;
    }
}
