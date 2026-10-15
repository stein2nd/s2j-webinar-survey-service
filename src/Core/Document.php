<?php

declare(strict_types=1);

namespace S2J\WebinarSurveyService\Core;

final class Document
{
    /**
     * @param array<string, mixed> $document
     *
     * @return array<string, mixed>
     */
    public static function normalize(array $document): array
    {
        $normalized = [];

        if (array_key_exists('internal_name', $document) && is_string($document['internal_name'])) {
            $normalized['internal_name'] = $document['internal_name'];
        }

        if (array_key_exists('questions', $document) && is_array($document['questions'])) {
            $questions = [];
            foreach ($document['questions'] as $question) {
                if (!is_array($question)) {
                    continue;
                }
                $questions[] = self::normalizeQuestion($question);
            }
            $normalized['questions'] = $questions;
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed> $question
     *
     * @return array<string, mixed>
     */
    public static function normalizeQuestion(array $question): array
    {
        $normalized = [];

        if (array_key_exists('prompt', $question) && is_string($question['prompt'])) {
            $normalized['prompt'] = $question['prompt'];
        }

        if (array_key_exists('answer_kind', $question) && is_string($question['answer_kind'])) {
            $normalized['answer_kind'] = $question['answer_kind'];
        }

        $normalized['required'] = BoolCoercion::coerce(
            $question['required'] ?? null,
            array_key_exists('required', $question)
        );
        $normalized['identifies_respondent'] = BoolCoercion::coerce(
            $question['identifies_respondent'] ?? null,
            array_key_exists('identifies_respondent', $question)
        );
        $normalized['purpose'] = array_key_exists('purpose', $question) && is_string($question['purpose'])
            ? $question['purpose']
            : '';

        $normalized['choices'] = self::normalizeChoices($question['choices'] ?? null);

        $kind = $normalized['answer_kind'] ?? null;
        if ($kind === AnswerKind::RATING) {
            foreach (['score_min', 'score_max', 'label_low', 'label_high'] as $key) {
                if (!array_key_exists($key, $question)) {
                    continue;
                }
                if ($key === 'score_min' || $key === 'score_max') {
                    if (is_int($question[$key])) {
                        $normalized[$key] = $question[$key];
                    }
                    continue;
                }
                if (is_string($question[$key])) {
                    $normalized[$key] = $question[$key];
                }
            }
        }

        return $normalized;
    }

    /**
     * @return list<string>
     */
    public static function normalizeChoices(mixed $choices): array
    {
        if (!is_array($choices)) {
            return [];
        }

        $normalized = [];
        foreach ($choices as $choice) {
            if (!is_string($choice) || $choice === '') {
                continue;
            }
            $normalized[] = $choice;
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed> $document
     *
     * @return list<array<string, mixed>>
     */
    public static function questionsList(array $document): array
    {
        if (!isset($document['questions']) || !is_array($document['questions'])) {
            return [];
        }

        $questions = [];
        foreach ($document['questions'] as $question) {
            if (is_array($question)) {
                $questions[] = $question;
            }
        }

        return $questions;
    }

    public static function trimmedString(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }
}
