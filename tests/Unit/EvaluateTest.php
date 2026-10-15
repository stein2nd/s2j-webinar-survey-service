<?php

declare(strict_types=1);

namespace S2J\WebinarSurveyService\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

use function S2J\WebinarSurveyService\evaluate;

final class EvaluateTest extends TestCase
{
    public function testReadyDocumentWithAdviceOnly(): void
    {
        $result = evaluate(
            [
                'internal_name' => 'post-webinar',
                'questions' => [
                    [
                        'prompt' => '満足度は？',
                        'answer_kind' => 'single',
                        'choices' => ['良い', '普通'],
                    ],
                ],
            ],
            6
        );

        self::assertSame('ready', $result['document']['status']);
        self::assertSame([], $result['deficiencies']);
        self::assertContains('purpose_missing', $result['advice']);
    }

    public function testMaxQuestionsBelowOneThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        evaluate(['internal_name' => 'x', 'questions' => []], 0);
    }

    public function testNormalizationDefaultsAndBoolCoercion(): void
    {
        $result = evaluate(
            [
                'internal_name' => 'survey',
                'questions' => [
                    [
                        'prompt' => '  コメント  ',
                        'answer_kind' => 'short',
                        'required' => 'false',
                    ],
                ],
            ],
            6
        );

        $question = $result['document']['questions'][0];
        self::assertFalse($question['required']);
        self::assertSame('', $question['purpose']);
        self::assertSame([], $question['choices']);
        self::assertSame('  コメント  ', $question['prompt']);
    }

    public function testBoolCoercionTrueFromStringOne(): void
    {
        $result = evaluate(
            [
                'internal_name' => 'survey',
                'questions' => [
                    [
                        'prompt' => '必須コメント',
                        'answer_kind' => 'long',
                        'required' => '1',
                    ],
                ],
            ],
            6
        );

        self::assertTrue($result['document']['questions'][0]['required']);
        self::assertContains('required_open', $result['advice']);
    }

    public function testMissingKeysAreNotAdded(): void
    {
        $result = evaluate(
            [
                'questions' => [
                    [
                        'required' => false,
                        'identifies_respondent' => false,
                        'purpose' => '',
                        'choices' => [],
                    ],
                ],
            ],
            6
        );

        self::assertArrayNotHasKey('internal_name', $result['document']);
        self::assertArrayNotHasKey('prompt', $result['document']['questions'][0]);
        self::assertArrayNotHasKey('answer_kind', $result['document']['questions'][0]);
    }

    public function testMultipleDeficienciesOnSameQuestionInStableOrder(): void
    {
        $result = evaluate(
            [
                'internal_name' => 'survey',
                'questions' => [
                    [
                        'prompt' => '',
                        'answer_kind' => 'invalid',
                        'choices' => ['a'],
                        'required' => false,
                        'identifies_respondent' => false,
                        'purpose' => '',
                    ],
                ],
            ],
            6
        );

        self::assertSame(['prompt_empty', 'answer_kind_invalid'], $result['deficiencies']);
    }

    public function testAllDeficiencyCodes(): void
    {
        $cases = [
            'internal_name_empty' => ['questions' => [['prompt' => 'q', 'answer_kind' => 'short']]],
            'questions_empty' => ['internal_name' => 'x'],
            'prompt_empty' => [
                'internal_name' => 'x',
                'questions' => [['answer_kind' => 'short']],
            ],
            'answer_kind_invalid' => [
                'internal_name' => 'x',
                'questions' => [['prompt' => 'q', 'answer_kind' => 'invalid']],
            ],
            'choices_missing' => [
                'internal_name' => 'x',
                'questions' => [['prompt' => 'q', 'answer_kind' => 'single', 'choices' => ['a']]],
            ],
            'choices_unexpected' => [
                'internal_name' => 'x',
                'questions' => [['prompt' => 'q', 'answer_kind' => 'short', 'choices' => ['a']]],
            ],
            'rating_bounds_invalid' => [
                'internal_name' => 'x',
                'questions' => [
                    [
                        'prompt' => 'q',
                        'answer_kind' => 'rating',
                        'score_min' => 5,
                        'score_max' => 5,
                    ],
                ],
            ],
        ];

        foreach ($cases as $expectedCode => $document) {
            $result = evaluate($document, 6);
            self::assertContains($expectedCode, $result['deficiencies'], "Failed asserting deficiency {$expectedCode}");
        }
    }

    public function testRatingStringScoreIsInvalid(): void
    {
        $result = evaluate(
            [
                'internal_name' => 'x',
                'questions' => [
                    [
                        'prompt' => 'q',
                        'answer_kind' => 'rating',
                        'score_min' => '0',
                        'score_max' => '10',
                    ],
                ],
            ],
            6
        );

        self::assertContains('rating_bounds_invalid', $result['deficiencies']);
    }

    public function testEmptyChoiceElementsAreRemovedForValidation(): void
    {
        $result = evaluate(
            [
                'internal_name' => 'x',
                'questions' => [
                    [
                        'prompt' => 'q',
                        'answer_kind' => 'single',
                        'choices' => ['a', '', 'b'],
                    ],
                ],
            ],
            6
        );

        self::assertSame(['a', 'b'], $result['document']['questions'][0]['choices']);
        self::assertSame('ready', $result['document']['status']);
    }

    public function testAdviceCodes(): void
    {
        $tooMany = evaluate(
            [
                'internal_name' => 'x',
                'questions' => [
                    ['prompt' => '1', 'answer_kind' => 'short'],
                    ['prompt' => '2', 'answer_kind' => 'short'],
                ],
            ],
            1
        );
        self::assertContains('too_many', $tooMany['advice']);

        $order = evaluate(
            [
                'internal_name' => 'x',
                'questions' => [
                    ['prompt' => 'open', 'answer_kind' => 'long'],
                    ['prompt' => 'closed', 'answer_kind' => 'single', 'choices' => ['a', 'b']],
                ],
            ],
            6
        );
        self::assertContains('open_before_closed', $order['advice']);

        $identity = evaluate(
            [
                'internal_name' => 'x',
                'questions' => [
                    ['prompt' => 'id', 'answer_kind' => 'short', 'identifies_respondent' => true],
                    ['prompt' => 'last', 'answer_kind' => 'short'],
                ],
            ],
            6
        );
        self::assertContains('identity_not_last', $identity['advice']);
    }

    public function testRatingScoreFieldsStrippedForNonRating(): void
    {
        $result = evaluate(
            [
                'internal_name' => 'x',
                'questions' => [
                    [
                        'prompt' => 'q',
                        'answer_kind' => 'short',
                        'score_min' => 0,
                        'score_max' => 10,
                    ],
                ],
            ],
            6
        );

        self::assertArrayNotHasKey('score_min', $result['document']['questions'][0]);
        self::assertArrayNotHasKey('score_max', $result['document']['questions'][0]);
    }
}
