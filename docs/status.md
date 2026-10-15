# S2J Webinar Survey Service - 実装状況

本ページは、現状の実装状況を機能単位で一覧します (製品全体の「いま」)。
完了したイニシアチブの凍結状況は [archive/README.md](./archive/README.md) です (直近: [mod-docs-cross-alignment](./archive/mod-docs-cross-alignment/))。進行中の証跡三点は `docs_mod/` に置きます。
索引は [specs.md](./specs.md) です。規則・型の Source of Truth は [core/](./core/document_spec.md) と [contracts/](./contracts/data_contract_spec.md) です ([governance/documentation_governance.md](./governance/documentation_governance.md) と同じ)。

最終更新: 2026-10-15

## 仕様書 (参照元)

* [specs.md](./specs.md) — 索引
* [service_spec.md](./service_spec.md) — 統合見取り図 (要約。規則の正本ではない)
* [core/](./core/document_spec.md) — 規則 (SoT)
* [contracts/data_contract_spec.md](./contracts/data_contract_spec.md) — DTO (SoT)
* [interfaces/php_api_spec.md](./interfaces/php_api_spec.md) — 公開面

## 機能一覧

**完了条件列:** 100% 到達用チェックリスト (1行 = PHPUnit ケース1件、または CI ステップ1件)。`[x]` = 満たし済み、`[ ]` = 未。正本 spec は各§の見出しリンク。

| 機能名 | 実装済み/未実装 | 実装％ | 完了条件 (100%) |
| --- | --- | --- | --- |
| 仕様分割 (`docs/` Why/What/How) | 仕様確定 | — | [§ 仕様分割](#status-spec-docs) |
| Composer スケルトン (`src/` 公開 API) | 実装済み | 100 | [§ Composer](#status-composer) (6/6) |
| 文書正規化 + status | 実装済み | 100 | [§ 正規化・status](#status-document) (5/5) |
| 検査 (不足コード一式) | 実装済み | 100 | [§ 検査](#status-validate) (9/9) |
| 助言コード一式 | 実装済み | 100 | [§ 助言](#status-advice) (5/5) |
| 下書き依頼文 | 実装済み | 100 | [§ 下書き](#status-draft) (10/10) |
| 下書き分解 | 実装済み | 100 | [§ 下書き](#status-draft) (同上) |
| PHPUnit / PHPStan / PHPCS | 実装済み | 100 | [§ 品質ゲート](#status-quality) (7/7) |
| Packagist 公開 | 未実装 | 0 | [§ Packagist](#status-packagist) (1/5) |
| プラグインからの require | 未実装 | 0 | [§ プラグイン](#status-plugin) (0/3) |

## 100% 完了条件チェックリスト

### 仕様分割 {#status-spec-docs}

* [x] CI: `lint-docs` が `docs/**` 等を lint ([ci.md](./engineering/ci.md))
* [x] 正本が `docs/`、起草が `docs_mod/` ([governance/documentation_governance.md](./governance/documentation_governance.md))

### Composer スケルトン {#status-composer}

正本: [php_api_spec.md](./interfaces/php_api_spec.md)

* [x] Test: `EvaluateTest` — 公開 `evaluate` (status / deficiencies / advice)
* [x] Test: `DraftTest::testPromptAndChoicesAlwaysRequestOne` — 公開 `build_draft_prompt`
* [x] Test: `DraftTest::testParsePromptInheritsFocusFields` — 公開 `parse_draft_response`
* [x] Test: `EvaluateTest::testMaxQuestionsBelowOneThrows` — `$max_questions` が1未満
* [x] Test: `DraftTest::testSurveyMaxQuestionsMissingThrows` — kind `survey` の `max_questions` 必須
* [x] Test: `DraftTest::testChoicesFocusOnShortThrows` — kind `choices` の focus 制約 (`build` / `parse` 対称)

### 文書正規化 + status {#status-document}

正本: [document_spec.md](./core/document_spec.md)

* [x] Test: `EvaluateTest::testNormalizationDefaultsAndBoolCoercion` — 欠落 scalar のデフォルト、空 `choices`、`"false"` 文字列 → false
* [x] Test: `EvaluateTest::testBoolCoercionTrueFromStringOne` — `"1"` → true (`(bool)` キャストではない)
* [x] Test: `EvaluateTest::testMissingKeysAreNotAdded` — 入力に無いキーを出力に足さない
* [x] Test: `EvaluateTest::testEmptyChoiceElementsAreRemovedForValidation` — 空要素 choice を除去して検査
* [x] Test: `EvaluateTest::testRatingScoreFieldsStrippedForNonRating` — `rating` 以外の `score_*` 除去

### 検査 (不足コード) {#status-validate}

正本: [validation_spec.md](./core/validation_spec.md)

* [x] Test: `EvaluateTest::testAllDeficiencyCodes` — `internal_name_empty`
* [x] Test: `EvaluateTest::testAllDeficiencyCodes` — `questions_empty`
* [x] Test: `EvaluateTest::testAllDeficiencyCodes` — `prompt_empty`
* [x] Test: `EvaluateTest::testAllDeficiencyCodes` — `answer_kind_invalid`
* [x] Test: `EvaluateTest::testAllDeficiencyCodes` — `choices_missing`
* [x] Test: `EvaluateTest::testAllDeficiencyCodes` — `choices_unexpected`
* [x] Test: `EvaluateTest::testAllDeficiencyCodes` — `rating_bounds_invalid` (`score_min === score_max` 含む)
* [x] Test: `EvaluateTest::testMultipleDeficienciesOnSameQuestionInStableOrder` — 同一設問・安定順
* [x] Test: `EvaluateTest::testRatingStringScoreIsInvalid` — 非 `is_int` の score → `rating_bounds_invalid` (0/10 で埋めない)

### 助言コード {#status-advice}

正本: [advice_spec.md](./core/advice_spec.md)

* [x] Test: `EvaluateTest::testReadyDocumentWithAdviceOnly` — `ready` + `purpose_missing`
* [x] Test: `EvaluateTest::testAdviceCodes` — `too_many` (文書につき1回)
* [x] Test: `EvaluateTest::testAdviceCodes` — `open_before_closed`
* [x] Test: `EvaluateTest::testAdviceCodes` — `identity_not_last`
* [x] Test: `EvaluateTest::testBoolCoercionTrueFromStringOne` — `required_open` (該当設問ごと)

### 下書き依頼・分解 {#status-draft}

正本: [draft_spec.md](./core/draft_spec.md)

* [x] Test: `DraftTest::testSurveyRequestedCountAndZeroPrompt` — `survey` 件数式・満杯時 `requested_count` 0 と空 `prompt_text`
* [x] Test: `DraftTest::testSurveyMissingDocumentCountsAsZeroExisting` — 既存0件としての requested
* [x] Test: `DraftTest::testSurveyMaxQuestionsMissingThrows` — kind `survey` の context 必須
* [x] Test: `DraftTest::testPromptAndChoicesAlwaysRequestOne` — kind `prompt` / `choices` は常に1
* [x] Test: `DraftTest::testChoicesFocusOnShortThrows` — `choices` + focus `short` / `long` / `rating` 不可
* [x] Test: `DraftTest::testParseSurveyTruncatesAndParsesRatingDefaults` — 切り詰め、番号付きリスト / 箇条書き、`rating` 0/10
* [x] Test: `DraftTest::testParsePromptInheritsFocusFields` — kind `prompt` が focus から `answer_kind` 等を引き継ぐ
* [x] Test: `DraftTest::testParseChoicesFragment` — kind `choices` の候補のみ返す
* [x] Test: `DraftTest::testParseReturnsEmptyWhenRequestedCountBelowOne` — `requested_count` 0 で空配列
* [x] Test: `DraftTest::testUnparseableSurveyReturnsEmptyCandidates` — 分解不能で空 (仕様どおり)

### PHPUnit / PHPStan / PHPCS {#status-quality}

正本: [testing.md](./testing.md) / [ci.md](./engineering/ci.md)

* [x] Test: [testing.md](./testing.md) 必須カバレッジ — 上記 § の Unit 群 (21 tests)
* [x] Local: `composer phpstan` がエラーなく通る (level8)
* [x] Local: PHPUnit 全緑 (`composer test`)
* [x] Local: PHPCS 警告0 (`composer phpcs`)
* [x] CI: `lint-docs` ([.github/workflows/docs-lint.yml](../.github/workflows/docs-lint.yml))
* [x] CI: [.github/workflows/php-quality.yml](../.github/workflows/php-quality.yml) — install / validate / test / lint:php
* [x] Local: `composer validate-composer` + `composer verify-dist` ([build_and_release.md](./engineering/build_and_release.md)、`npm run quality`)

### Packagist 公開 {#status-packagist}

正本: [build_and_release.md](./engineering/build_and_release.md)

* [ ] CI: タグ `v*.*.*` 用 release workflow (S2J Webinar Service の [release.yml](https://github.com/stein2nd/s2j-webinar-service/blob/main/.github/workflows/release.yml) 相当)
* [ ] Packagist: `s2j/webinar-survey-service` 登録・auto-update
* [ ] SemVer: CHANGELOG の初回公開節 + タグ push
* [x] `composer validate-composer` / `composer verify-dist` (.gitattributes)
* [ ] README — 公開後の `composer require` 手順が実環境で確認済み

### プラグインからの require {#status-plugin}

正本: S2J Webinar Survey 側 (本 repo 外)。プラグインの進捗はそちらが正本。

* [ ] S2J Webinar Survey: `composer.json` — `s2j/webinar-survey-service` 制約
* [ ] プラグイン: 保存前 `evaluate` + 不足/助言の表示
* [ ] プラグイン: `build_draft_prompt` / `parse_draft_response` による下書き UI

## 補足

* 初版に OpenAPI、REST Adapter、TypeScript SDK は、含めない。
* Zoom 写像は、本 repo の実装対象外である。
* 下書き分解は、番号付きリストと箇条書きを前提とする実装である。返却形式が大きく異なる場合は候補空になる (仕様どおり)。
* **実装％** は、上記チェックリストの `[x]` / 全項の比率 (仕様分割は除く。Packagist / プラグインは表記どおり)。
* **main** の Ruleset で `php-quality` / `lint-docs` を required にした場合、workflow の path filter により片方が Skipped になる PR ではマージできないことがある ([ci.md](./engineering/ci.md) 参照)。
