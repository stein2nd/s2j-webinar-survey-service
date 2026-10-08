<!--
目的：「Composer から見える PHP 公開面」の明文化
-->

# S2J Webinar Survey Service - PHP 公開面

本ドキュメントは、パッケージ利用者が呼ぶ **PHP API** を定義します。
Similarity Service の SDK 仕様に相当する、本ライブラリ向けの薄い公開面です。

## 設計意図 (ゴール)

プラグインが、WordPress を知らない純関数として、評価と下書き分解を呼び出せるようにします。

## 責務

* 公開シンボルの名前、引数、戻り値を定義すること。
* 後方互換の方針を定義すること。

## 非責務

* Core 内部の関数分割
* プラグインのフック登録

## パッケージ

| 項目 | 値 |
| --- | --- |
| Composer 名 | `s2j/webinar-survey-service` |
| 名前空間 | `S2J\WebinarSurveyService\` |
| PHP | `>=8.1` |

## 公開 API (初版)

名前は実装時に PSR-4の配置に落とし込みます。意味は変えません。

### evaluate

```php
/**
 * @param array<string, mixed> $document SurveyDocument (status なし可)
 * @return array{
 *   document: array<string, mixed>,
 *   deficiencies: list<string>,
 *   advice: list<string>
 * }
 */
function evaluate(array $document, int $max_questions): array
```

* `$max_questions` が1未満の場合は `InvalidArgumentException` とする。サイト設定の誤りではなく、呼び出し側のプログラマー誤りである。助言コードにも不足コードにもしない。
* 入力は正規化する ([../core/document_spec.md](../core/document_spec.md))。欠落フィールドのデフォルト値埋め、bool の明示 coerce、答え方に使わない既知キーの除去を含む。

### build_draft_prompt

```php
/**
 * @param 'survey'|'prompt'|'choices' $kind
 * @param array<string, mixed> $context event_title, document, focus_index, max_questions
 * @return array{
 *   prompt_text: string,
 *   requested_count: int
 * }
 */
function build_draft_prompt(string $kind, array $context): array
```

* 戻りは、コネクタに送る依頼文と、その依頼で期待する件数の組である。`parse_draft_response` には、同じ `requested_count` を渡す。
* `$context['max_questions']` が必要な場合 (`survey`)、値が1未満なら `InvalidArgumentException` とする。`evaluate` と同じ。
* `survey` の既存設問数は、`$context['document']['questions']` から数える。`document` 欠落、または `questions` が配列でない場合は、**既存0件扱い** とする (例外にしない)。
* `survey` の `requested_count` は `max(0, max_questions − 既存設問数)` である。0の場合は `prompt_text` を空文字、`requested_count` を0とし、プラグインは送らない。**`requested_count` が0になりうるのは `survey` だけ**である。
* `prompt` / `choices` は focus が正しければ常に `requested_count` は1、`prompt_text` は非空である。不正なら例外とし、0や空文字は返さない。
* `prompt` / `choices` では `$context['focus_index']` が必須の `int` である (null 不可)。欠けるか範囲外なら `InvalidArgumentException` とする。`survey` では `focus_index` は不要 (あっても無視してよい)。
* `choices` では、focus の設問の `answer_kind` が `single` または `multiple` でなければ `InvalidArgumentException` とする。`prompt` は範囲内ならどの答え方でもよい。

### parse_draft_response

```php
/**
 * @param 'survey'|'prompt'|'choices' $kind
 * @param array<string, mixed> $context document, focus_index, requested_count
 * @return list<array<string, mixed>> candidates
 */
function parse_draft_response(string $kind, string $response, array $context): array
```

* `$context['requested_count']` は、直前の `build_draft_prompt` が返した値を渡す。切り詰めの上限にする。1未満なら候補は空でよい (`survey` で送っていない想定)。
* kind が `prompt` / `choices` の場合は `$context['document']` と `$context['focus_index']` (必須の `int`、null 不可) が必須である。欠けるか範囲外、または `choices` で focus が `single` / `multiple` 以外なら `InvalidArgumentException` とする (`build_draft_prompt` と同じ)。`survey` では `document` / `focus_index` は不要 (あっても無視してよい)。
* kind が `prompt` の場合、候補の `answer_kind` と答え方に応じた欄は、focus の設問からライブラリ内で引き継ぐ ([../core/draft_spec.md](../core/draft_spec.md))。プラグイン側でマージしない。

## 非公開

* Core の内部ヘルパー
* プロンプト文言テンプレートの定数配置 (変更しても SemVer の major にしない。分解の振る舞いが変わる場合だけ minor/major)

## バージョニング

* 不足・助言コードの **削除・意味変更** は major である。
* コードの **追加** は minor である。
* 文書フィールドの **必須追加** は major である。任意フィールドの追加は minor である。

## 関連

* 契約: [../contracts/data_contract_spec.md](../contracts/data_contract_spec.md)
* 使用方法: [usage_spec.md](./usage_spec.md)
