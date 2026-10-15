<?php

declare(strict_types=1);

namespace S2J\WebinarSurveyService\Core;

final class Draft
{
    /**
     * @param array<string, mixed> $context
     *
     * @return array{prompt_text: string, requested_count: int}
     */
    public static function buildPrompt(string $kind, array $context): array
    {
        DraftContext::assertKind($kind);

        return match ($kind) {
            DraftContext::KIND_SURVEY => self::buildSurveyPrompt($context),
            DraftContext::KIND_PROMPT => self::buildPromptRewrite($context),
            DraftContext::KIND_CHOICES => self::buildChoicesPrompt($context),
            default => throw new \InvalidArgumentException('Invalid draft kind.'),
        };
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return list<array<string, mixed>>
     */
    public static function parseResponse(string $kind, string $response, array $context): array
    {
        DraftContext::assertKind($kind);

        $requestedCount = $context['requested_count'] ?? null;
        if (!is_int($requestedCount) || $requestedCount < 1) {
            return [];
        }

        $candidates = match ($kind) {
            DraftContext::KIND_SURVEY => self::parseSurveyResponse($response),
            DraftContext::KIND_PROMPT => self::parsePromptResponse($response, $context),
            DraftContext::KIND_CHOICES => self::parseChoicesResponse($response),
            default => throw new \InvalidArgumentException('Invalid draft kind.'),
        };

        $valid = [];
        foreach ($candidates as $candidate) {
            if ($kind === DraftContext::KIND_CHOICES) {
                if (self::isValidChoicesFragment($candidate)) {
                    $valid[] = $candidate;
                }
                continue;
            }

            if (self::isValidQuestionCandidate($candidate)) {
                $valid[] = $candidate;
            }
        }

        return array_slice($valid, 0, $requestedCount);
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array{prompt_text: string, requested_count: int}
     */
    private static function buildSurveyPrompt(array $context): array
    {
        $maxQuestions = DraftContext::requireMaxQuestions($context);
        $existing = DraftContext::existingQuestionCount($context);
        $requestedCount = max(0, $maxQuestions - $existing);

        if ($requestedCount === 0) {
            return [
                'prompt_text' => '',
                'requested_count' => 0,
            ];
        }

        $title = '';
        if (isset($context['event_title']) && is_string($context['event_title'])) {
            $title = trim($context['event_title']);
        }

        $titleLine = $title !== '' ? "イベント名: {$title}\n" : '';

        $promptText = $titleLine
            . "ウェビナー終了後アンケートの設問を {$requestedCount} 件提案してください。\n"
            . "単一選択・複数選択・レーティング・スケールを先にし、自由記述 (短い回答または長い回答) は最後に多くて1件にしてください。\n"
            . "回答者を特定する問いと、必須の自由記述は含めないでください。\n"
            . "各設問は1行目に設問文、選択式なら `- 選択肢` 形式で短いラベルを2つ以上書いてください。\n"
            . "番号付きリスト (1. 2. ...) で {$requestedCount} 件に分けてください。";

        return [
            'prompt_text' => $promptText,
            'requested_count' => $requestedCount,
        ];
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array{prompt_text: string, requested_count: int}
     */
    private static function buildPromptRewrite(array $context): array
    {
        $focus = DraftContext::focusQuestion(DraftContext::KIND_PROMPT, $context);
        $current = Document::trimmedString($focus['prompt'] ?? null);

        $promptText = "次の設問文を、回答者が答えやすい一文に書き換えてください。\n"
            . "設問文のみを1行で返してください。\n"
            . "現在の設問: {$current}";

        return [
            'prompt_text' => $promptText,
            'requested_count' => 1,
        ];
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array{prompt_text: string, requested_count: int}
     */
    private static function buildChoicesPrompt(array $context): array
    {
        $focus = DraftContext::focusQuestion(DraftContext::KIND_CHOICES, $context);
        $prompt = Document::trimmedString($focus['prompt'] ?? null);

        $promptText = "次の選択式設問向けに、短い選択肢ラベルを2〜4個提案してください。\n"
            . "1行に1つ、`- ラベル` 形式で返してください。\n"
            . "設問: {$prompt}";

        return [
            'prompt_text' => $promptText,
            'requested_count' => 1,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function parseSurveyResponse(string $response): array
    {
        $blocks = self::splitNumberedBlocks($response);
        $candidates = [];

        foreach ($blocks as $block) {
            $candidate = self::parseQuestionBlock($block);
            if ($candidate !== null) {
                $candidates[] = $candidate;
            }
        }

        return $candidates;
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return list<array<string, mixed>>
     */
    private static function parsePromptResponse(string $response, array $context): array
    {
        DraftContext::requireFocusIndex(DraftContext::KIND_PROMPT, $context);
        $focus = DraftContext::focusQuestion(DraftContext::KIND_PROMPT, $context);

        $prompt = '';
        $lines = preg_split('/\r\n|\r|\n/', trim($response)) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line !== '') {
                $prompt = $line;
                break;
            }
        }

        if ($prompt === '') {
            return [];
        }

        $candidate = self::baseDraftQuestion($prompt, $focus);

        return [$candidate];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function parseChoicesResponse(string $response): array
    {
        $choices = self::extractBulletLabels($response);
        if (count($choices) < 2) {
            return [];
        }

        return [
            ['choices' => $choices],
        ];
    }

    /**
     * @return list<string>
     */
    private static function splitNumberedBlocks(string $response): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($response)) ?: [];
        if ($lines === []) {
            return [];
        }

        $blocks = [];
        $current = [];

        foreach ($lines as $line) {
            if (preg_match('/^\s*\d+[\.\)]\s*(.*)$/', $line, $matches) === 1) {
                if ($current !== []) {
                    $blocks[] = implode("\n", $current);
                    $current = [];
                }
                if ($matches[1] !== '') {
                    $current[] = $matches[1];
                }
                continue;
            }

            $current[] = $line;
        }

        if ($current !== []) {
            $blocks[] = implode("\n", $current);
        }

        if ($blocks === [] && trim($response) !== '') {
            return [trim($response)];
        }

        return $blocks;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function parseQuestionBlock(string $block): ?array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($block)) ?: [];
        if ($lines === []) {
            return null;
        }

        $prompt = '';
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line !== '') {
                $prompt = $line;
                break;
            }
        }

        if ($prompt === '') {
            return null;
        }

        $choices = self::extractBulletLabels($block);
        $kind = self::detectAnswerKind($block, $choices);

        $candidate = [
            'prompt' => $prompt,
            'answer_kind' => $kind,
            'required' => false,
            'identifies_respondent' => false,
            'purpose' => '',
        ];

        if (AnswerKind::isChoiceKind($kind)) {
            $candidate['choices'] = $choices;
            return $candidate;
        }

        $candidate['choices'] = [];

        if ($kind === AnswerKind::RATING) {
            $candidate['score_min'] = 0;
            $candidate['score_max'] = 10;
            $candidate['label_low'] = '';
            $candidate['label_high'] = '';
        }

        return $candidate;
    }

    /**
     * @param list<string> $choices
     */
    private static function detectAnswerKind(string $block, array $choices): string
    {
        $lower = mb_strtolower($block);

        if (str_contains($lower, '複数選択') || str_contains($lower, 'multiple')) {
            return AnswerKind::MULTIPLE;
        }

        if (str_contains($lower, '短い回答') || str_contains($lower, 'short')) {
            return AnswerKind::SHORT;
        }

        if (str_contains($lower, '長い回答') || str_contains($lower, 'long')) {
            return AnswerKind::LONG;
        }

        if (str_contains($lower, 'レーティング') || str_contains($lower, 'rating') || str_contains($lower, '満足度')) {
            return AnswerKind::RATING;
        }

        if (count($choices) >= 2) {
            return AnswerKind::SINGLE;
        }

        return AnswerKind::LONG;
    }

    /**
     * @return list<string>
     */
    private static function extractBulletLabels(string $text): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $labels = [];

        foreach ($lines as $line) {
            if (preg_match('/^\s*[-・•*]\s*(.+)$/', $line, $matches) === 1) {
                $label = trim($matches[1]);
                if ($label !== '') {
                    $labels[] = $label;
                }
            }
        }

        return $labels;
    }

    /**
     * @param array<string, mixed> $focus
     *
     * @return array<string, mixed>
     */
    private static function baseDraftQuestion(string $prompt, array $focus): array
    {
        $kind = $focus['answer_kind'] ?? AnswerKind::SHORT;
        if (!is_string($kind) || !AnswerKind::isValid($kind)) {
            $kind = AnswerKind::SHORT;
        }

        $candidate = [
            'prompt' => $prompt,
            'answer_kind' => $kind,
            'required' => false,
            'identifies_respondent' => false,
            'purpose' => '',
            'choices' => [],
        ];

        if (AnswerKind::isChoiceKind($kind)) {
            $candidate['choices'] = Document::normalizeChoices($focus['choices'] ?? null);
        }

        if ($kind === AnswerKind::RATING) {
            foreach (['score_min', 'score_max', 'label_low', 'label_high'] as $key) {
                if (array_key_exists($key, $focus)) {
                    $candidate[$key] = $focus[$key];
                }
            }
            if (!isset($candidate['score_min'], $candidate['score_max'])) {
                $candidate['score_min'] = 0;
                $candidate['score_max'] = 10;
            }
            if (!isset($candidate['label_low'])) {
                $candidate['label_low'] = '';
            }
            if (!isset($candidate['label_high'])) {
                $candidate['label_high'] = '';
            }
        }

        return $candidate;
    }

    /**
     * @param array<string, mixed> $candidate
     */
    private static function isValidQuestionCandidate(array $candidate): bool
    {
        if (Document::trimmedString($candidate['prompt'] ?? null) === '') {
            return false;
        }

        $kind = $candidate['answer_kind'] ?? null;
        if (!is_string($kind) || !AnswerKind::isValid($kind)) {
            return false;
        }

        $choices = Document::normalizeChoices($candidate['choices'] ?? null);

        if (AnswerKind::isChoiceKind($kind)) {
            return count($choices) >= 2;
        }

        if ($choices !== []) {
            return false;
        }

        if ($kind === AnswerKind::RATING) {
            $min = $candidate['score_min'] ?? 0;
            $max = $candidate['score_max'] ?? 10;
            if (!is_int($min) || !is_int($max)) {
                return false;
            }

            return $min < $max;
        }

        return true;
    }

    /**
     * @param array<string, mixed> $candidate
     */
    private static function isValidChoicesFragment(array $candidate): bool
    {
        $choices = Document::normalizeChoices($candidate['choices'] ?? null);

        return count($choices) >= 2;
    }
}
