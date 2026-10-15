<?php

declare(strict_types=1);

namespace S2J\WebinarSurveyService\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

use function S2J\WebinarSurveyService\build_draft_prompt;
use function S2J\WebinarSurveyService\parse_draft_response;

final class DraftTest extends TestCase
{
    public function testSurveyRequestedCountAndZeroPrompt(): void
    {
        $built = build_draft_prompt('survey', [
            'max_questions' => 6,
            'document' => [
                'questions' => [
                    ['prompt' => 'q1', 'answer_kind' => 'short'],
                    ['prompt' => 'q2', 'answer_kind' => 'short'],
                    ['prompt' => 'q3', 'answer_kind' => 'short'],
                    ['prompt' => 'q4', 'answer_kind' => 'short'],
                    ['prompt' => 'q5', 'answer_kind' => 'short'],
                    ['prompt' => 'q6', 'answer_kind' => 'short'],
                ],
            ],
        ]);

        self::assertSame(0, $built['requested_count']);
        self::assertSame('', $built['prompt_text']);

        $builtPartial = build_draft_prompt('survey', [
            'max_questions' => 6,
            'document' => ['questions' => [['prompt' => 'q1', 'answer_kind' => 'short']]],
        ]);
        self::assertSame(5, $builtPartial['requested_count']);
        self::assertNotSame('', $builtPartial['prompt_text']);
    }

    public function testSurveyMissingDocumentCountsAsZeroExisting(): void
    {
        $built = build_draft_prompt('survey', ['max_questions' => 3]);
        self::assertSame(3, $built['requested_count']);
    }

    public function testSurveyMaxQuestionsMissingThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        build_draft_prompt('survey', ['document' => ['questions' => []]]);
    }

    public function testPromptAndChoicesAlwaysRequestOne(): void
    {
        $document = [
            'questions' => [
                [
                    'prompt' => '満足度',
                    'answer_kind' => 'single',
                    'choices' => ['良い', '悪い'],
                ],
            ],
        ];

        $prompt = build_draft_prompt('prompt', [
            'document' => $document,
            'focus_index' => 0,
        ]);
        self::assertSame(1, $prompt['requested_count']);
        self::assertNotSame('', $prompt['prompt_text']);

        $choices = build_draft_prompt('choices', [
            'document' => $document,
            'focus_index' => 0,
        ]);
        self::assertSame(1, $choices['requested_count']);
    }

    public function testChoicesFocusOnShortThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        build_draft_prompt('choices', [
            'document' => [
                'questions' => [
                    ['prompt' => 'q', 'answer_kind' => 'short'],
                ],
            ],
            'focus_index' => 0,
        ]);
    }

    public function testParseSurveyTruncatesAndParsesRatingDefaults(): void
    {
        $response = <<<'TEXT'
1. 満足度を教えてください (レーティング)
2. 良かった点は？
- 内容
- 進行
3. 改善点
TEXT;

        $candidates = parse_draft_response('survey', $response, ['requested_count' => 2]);

        self::assertCount(2, $candidates);
        self::assertSame('rating', $candidates[0]['answer_kind']);
        self::assertSame(0, $candidates[0]['score_min']);
        self::assertSame(10, $candidates[0]['score_max']);
        self::assertSame('single', $candidates[1]['answer_kind']);
        self::assertSame(['内容', '進行'], $candidates[1]['choices']);
    }

    public function testParsePromptInheritsFocusFields(): void
    {
        $document = [
            'questions' => [
                [
                    'prompt' => '旧設問',
                    'answer_kind' => 'rating',
                    'score_min' => 1,
                    'score_max' => 5,
                    'label_low' => '低',
                    'label_high' => '高',
                ],
            ],
        ];

        $candidates = parse_draft_response('prompt', "新しい設問文\n", [
            'requested_count' => 1,
            'document' => $document,
            'focus_index' => 0,
        ]);

        self::assertSame('新しい設問文', $candidates[0]['prompt']);
        self::assertSame('rating', $candidates[0]['answer_kind']);
        self::assertSame(1, $candidates[0]['score_min']);
        self::assertSame(5, $candidates[0]['score_max']);
    }

    public function testParseChoicesFragment(): void
    {
        $candidates = parse_draft_response('choices', "- はい\n- いいえ\n", [
            'requested_count' => 1,
            'document' => [
                'questions' => [
                    ['prompt' => 'q', 'answer_kind' => 'single', 'choices' => ['a', 'b']],
                ],
            ],
            'focus_index' => 0,
        ]);

        self::assertSame([['choices' => ['はい', 'いいえ']]], $candidates);
    }

    public function testParseReturnsEmptyWhenRequestedCountBelowOne(): void
    {
        self::assertSame([], parse_draft_response('survey', '1. q', ['requested_count' => 0]));
    }

    public function testUnparseableSurveyReturnsEmptyCandidates(): void
    {
        self::assertSame([], parse_draft_response('survey', '   ', ['requested_count' => 3]));
    }
}
