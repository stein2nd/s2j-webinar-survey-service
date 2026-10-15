<?php

declare(strict_types=1);

namespace S2J\WebinarSurveyService\Core;

use InvalidArgumentException;

final class DraftContext
{
    public const KIND_SURVEY = 'survey';

    public const KIND_PROMPT = 'prompt';

    public const KIND_CHOICES = 'choices';

    /** @var list<string> */
    public const KINDS = [
        self::KIND_SURVEY,
        self::KIND_PROMPT,
        self::KIND_CHOICES,
    ];

    public static function assertKind(string $kind): void
    {
        if (!in_array($kind, self::KINDS, true)) {
            throw new InvalidArgumentException('Invalid draft kind.');
        }
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function requireMaxQuestions(array $context): int
    {
        if (!array_key_exists('max_questions', $context) || !is_int($context['max_questions'])) {
            throw new InvalidArgumentException('max_questions is required for survey draft.');
        }

        if ($context['max_questions'] < 1) {
            throw new InvalidArgumentException('max_questions must be at least 1.');
        }

        return $context['max_questions'];
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function existingQuestionCount(array $context): int
    {
        if (!isset($context['document']) || !is_array($context['document'])) {
            return 0;
        }

        return count(Document::questionsList($context['document']));
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function requireFocusIndex(string $kind, array $context): int
    {
        if ($kind === self::KIND_SURVEY) {
            return 0;
        }

        if (!isset($context['focus_index']) || !is_int($context['focus_index'])) {
            throw new InvalidArgumentException('focus_index is required.');
        }

        if (!isset($context['document']) || !is_array($context['document'])) {
            throw new InvalidArgumentException('document is required.');
        }

        $questions = Document::questionsList($context['document']);
        $index = $context['focus_index'];
        if ($index < 0 || $index >= count($questions)) {
            throw new InvalidArgumentException('focus_index is out of range.');
        }

        if ($kind === self::KIND_CHOICES) {
            $answerKind = $questions[$index]['answer_kind'] ?? null;
            if (!is_string($answerKind) || !AnswerKind::isChoiceKind($answerKind)) {
                throw new InvalidArgumentException('focus question must be single or multiple for choices draft.');
            }
        }

        return $index;
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public static function focusQuestion(string $kind, array $context): array
    {
        $index = self::requireFocusIndex($kind, $context);
        $questions = Document::questionsList($context['document']);

        return $questions[$index];
    }
}
