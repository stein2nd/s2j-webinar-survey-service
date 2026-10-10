<!--
目的：「入出力仕様 (DTO)」の明文化
-->

# S2J Webinar Survey Service - 入出力仕様

本ドキュメントは、本ライブラリの **入出力データ契約 (DTO)** を定義します。
PHP の公開面と Core の間で共有する Source of Truth とします。

## 概要

* 永続化形式は、プラグインが決める。本契約は、計算の入出力である。
* バリデーションの「規則」は、Core である。本仕様は、形である。
* OpenAPI は、初版では使わない。

## 入力: 設問文書

意味と制約の詳細は [../core/document_spec.md](../core/document_spec.md) と [data_dictionary.md](./data_dictionary.md) です。

「入力」は呼び出し側が渡す前の欠落可否、「正規化後」は検査前の文書にその欄があるかを表します。欠落のデフォルト埋めと「キーを足さない」欄の扱いは [../core/document_spec.md](../core/document_spec.md) が正です。

```json
{
  "internal_name": "string",
  "questions": [
    {
      "prompt": "string",
      "answer_kind": "single",
      "required": false,
      "identifies_respondent": false,
      "purpose": "string",
      "choices": ["string"],
      "score_min": 0,
      "score_max": 10,
      "label_low": "string",
      "label_high": "string"
    }
  ]
}
```

| フィールド | 型 | 入力 | 正規化後 | 説明 |
| --- | --- | --- | --- | --- |
| internal_name | string | 任意 (欠落・空は不足) | 欠落可 (キーは足さない。判定は空と同じ) | 一覧用。回答者には出さない |
| questions | Question[] | 任意 (欠落・0件は不足) | 欠落可 (キーは足さない。判定は0件と同じ) | 順序あり |
| questions[].prompt | string | 任意 (欠落・空は不足) | 欠落可 (キーは足さない。判定は空と同じ) | 回答者に見せる文 |
| questions[].answer_kind | enum | 任意 (欠落・不正は不足) | 欠落可 (デフォルトなし。キーは足さない) | `single` / `multiple` / `short` / `long` / `rating` |
| questions[].required | bool | 任意 | あり (欠落は `false`) | 必須か。coerce は document_spec |
| questions[].identifies_respondent | bool | 任意 | あり (欠落は `false`) | 回答者を特定する問いが。coerce は document_spec |
| questions[].purpose | string | 任意 | あり (欠落は `""`) | 企画上の目的。空可 |
| questions[].choices | string[] | 任意 | あり得る (欠落は `[]` 可。空文字要素は除去) | `single` / `multiple` で使用 |
| questions[].score_min | int | 任意 | `rating` の場合、残る | `rating` で使用。欠けは不足 |
| questions[].score_max | int | 任意 | `rating` の場合、残る | `rating` で使用。欠けは不足 |
| questions[].label_low | string | 任意 | `rating` の場合、残ってよい | `rating`。空可 |
| questions[].label_high | string | 任意 | `rating` の場合、残ってよい | `rating`。空可 |

## 入力: 上限

| 引数 | 型 | 説明 |
| --- | --- | --- |
| max_questions | int | 設問総数の上限。サイト設定の1〜15は埋め込まない。1未満は公開面で `InvalidArgumentException` ([../interfaces/php_api_spec.md](../interfaces/php_api_spec.md)) |

## 出力: 評価結果

```json
{
  "document": {
    "internal_name": "string",
    "questions": [],
    "status": "draft"
  },
  "deficiencies": ["internal_name_empty"],
  "advice": ["purpose_missing"]
}
```

| フィールド | 型 | 説明 |
| --- | --- | --- |
| document | SurveyDocument + status | 正規化後。`status` は検査結果 |
| deficiencies | string[] | 不足コード。空なら `ready` 可 |
| advice | string[] | 助言コード。空でもよい |

不足・助言コードの enumeration は、Core 仕様が正です。

## 入力: 下書き依頼 (`build_draft_prompt` の `$context`)

| フィールド | 型 | 説明 |
| --- | --- | --- |
| kind | enum | 引数。`survey` / `prompt` / `choices` |
| max_questions | int | `survey` で必須。欠落、非 int、または1未満は `InvalidArgumentException` |
| document | SurveyDocument | 既存設問の文脈。`survey` で欠落・`questions` 非配列は既存0件扱い |
| event_title | string | 題名。空可だが渡す想定 |
| focus_index | int | **`prompt` / `choices` で必須** (非 null)。`survey` では不要 (あっても無視) |

## 出力: 依頼文 (`build_draft_prompt` の戻り)

依頼文と期待件数は、セットで返します。件数は、プラグインが再計算しません。

```json
{
  "prompt_text": "string",
  "requested_count": 3
}
```

| フィールド | 型 | 説明 |
| --- | --- | --- |
| prompt_text | string | コネクタに送る文。`survey` で依頼しない場合、空 |
| requested_count | int | `survey` は `max(0, max_questions − 既存)` (**0になりうるのは `survey` のみ**)。`prompt` / `choices` は常に1 (不正時は例外で返さない) |

## 入力: 下書き分解 (`parse_draft_response` の `$context`)

| フィールド | 型 | 説明 |
| --- | --- | --- |
| requested_count | int | 直前の `build_draft_prompt` が返した値 |
| document | SurveyDocument | **`prompt` / `choices` で必須**。引き継ぎと focus 検証に使う。`survey` では不要 |
| focus_index | int | **`prompt` / `choices` で必須** (非 null)。`survey` では不要 |

## 出力: 下書き候補 (`parse_draft_response` の戻り)

意味と答え方ごとの欄は [../core/draft_spec.md](../core/draft_spec.md) の「候補の形」が正です。ここは DTO の例です。Zoom のフィールド名は使いません。戻りは候補の配列のみです (`prompt_text` は含まない)。

### kind が `survey` または `prompt` (Question 形)

```json
[
  {
    "prompt": "今回の内容は分かりやすかったですか？",
    "answer_kind": "single",
    "choices": ["はい", "いいえ"],
    "required": false,
    "identifies_respondent": false,
    "purpose": ""
  },
  {
    "prompt": "満足度を教えてください",
    "answer_kind": "rating",
    "choices": [],
    "score_min": 0,
    "score_max": 10,
    "label_low": "",
    "label_high": "",
    "required": false,
    "identifies_respondent": false,
    "purpose": ""
  },
  {
    "prompt": "改善してほしい点があれば書いてください",
    "answer_kind": "long",
    "choices": [],
    "required": false,
    "identifies_respondent": false,
    "purpose": ""
  }
]
```

| フィールド | 型 | 説明 |
| --- | --- | --- |
| prompt | string | 必須。空の候補は捨てる |
| answer_kind | enum | `single` / `multiple` / `short` / `long` / `rating` |
| required | bool | 常に `false` |
| identifies_respondent | bool | 常に `false` |
| purpose | string | 常に `""` |
| choices | string[] | `single` / `multiple` で2つ以上。`short` / `long` / `rating` では空 |
| score_min / score_max | int | `rating` のみ。欠けていれば分解時に目安 `0` / `10` |
| label_low / label_high | string | `rating` のみ。空可 |

kind が `prompt` の場合、返却の主は新しい `prompt` です。`answer_kind` と答え方に応じた欄は、`$context` の focus 設問から **ライブラリが** 引き継ぎます。

### kind が `choices`

```json
[
  {
    "choices": ["とても良い", "良い", "ふつう", "改善が必要"]
  }
]
```

プラグインが、開いている `single` / `multiple` の選択肢にマージします。

## エラー

* 初版は、例外の細分化はしない。不正な kind、不正な `focus_index`、`choices` で focus が選択式以外、などプログラマー誤りは `InvalidArgumentException` でかまわない。
* 文書の不足は例外にせず、`deficiencies` で返す。

## 関連

* 辞書: [data_dictionary.md](./data_dictionary.md)
* PHP API: [../interfaces/php_api_spec.md](../interfaces/php_api_spec.md)
