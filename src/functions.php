<?php

declare(strict_types=1);

namespace S2J\WebinarSurveyService;

use InvalidArgumentException;
use S2J\WebinarSurveyService\Core\Advise;
use S2J\WebinarSurveyService\Core\Document;
use S2J\WebinarSurveyService\Core\Draft;
use S2J\WebinarSurveyService\Core\Validate;

/**
 * @param array<string, mixed> $document
 *
 * @return array{
 *   document: array<string, mixed>,
 *   deficiencies: list<string>,
 *   advice: list<string>
 * }
 */
function evaluate(array $document, int $max_questions): array
{
    if ($max_questions < 1) {
        throw new InvalidArgumentException('max_questions must be at least 1.');
    }

    $normalized = Document::normalize($document);
    $deficiencies = Validate::deficiencies($normalized);
    $advice = Advise::advice($normalized, $max_questions);

    $normalized['status'] = Validate::statusFromDeficiencies($deficiencies);

    return [
        'document' => $normalized,
        'deficiencies' => $deficiencies,
        'advice' => $advice,
    ];
}

/**
 * @param 'survey'|'prompt'|'choices' $kind
 * @param array<string, mixed> $context
 *
 * @return array{prompt_text: string, requested_count: int}
 */
function build_draft_prompt(string $kind, array $context): array
{
    return Draft::buildPrompt($kind, $context);
}

/**
 * @param 'survey'|'prompt'|'choices' $kind
 * @param array<string, mixed> $context
 *
 * @return list<array<string, mixed>>
 */
function parse_draft_response(string $kind, string $response, array $context): array
{
    if ($kind === 'prompt' || $kind === 'choices') {
        \S2J\WebinarSurveyService\Core\DraftContext::requireFocusIndex($kind, $context);
    }

    return Draft::parseResponse($kind, $response, $context);
}
