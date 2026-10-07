<!--
目的：「設問文書の形と状態」の明文化
-->

# S2J Webinar Survey Service - 文書仕様

本ドキュメントは、検査と助言の入力となる **設問文書** と、その **状態** を定義します。

## 設計意図 (ゴール)

呼び出し側が保存し、S2J Webinar が `ready` の場合だけ読める、順序付きの設問一式を一意に表します。

## 責務

* 文書フィールドの意味と必須条件を定義すること。
* `draft` / `ready` の意味を定義すること。

## 非責務

* 不足・助言コードの列挙本体 ([validation_spec.md](./validation_spec.md)、[advice_spec.md](./advice_spec.md))
* Zoom フィールド名への写像
* イベントメタのキー名 (プラグイン仕様)

## 文書の形

```text
internal_name              空は不足。回答者には出さない
questions                  順序あり。0件は不足
  prompt                   回答者に見せる文。空は不足
  answer_kind              single | multiple | short | long | rating
  required                 true | false
  identifies_respondent    true | false。運営者が付ける
  purpose                  次回の企画で何を決めるか。回答者には出さない
  choices                  single / multiple の場合、空でない文が2つ以上。それ以外では空
  score_min                rating の場合。整数。目安は 0。evaluate では埋め込まない
  score_max                rating の場合。整数。目安は 10。score_min より大きい。evaluate では埋め込まない
  label_low                rating の場合。低いスコアのラベル。空可
  label_high               rating の場合。高いスコアのラベル。空可
status                     draft | ready
```

入力に `status` があっても、検査結果で上書きします。助言コードは文書に含めません。

## 答え方

| answer_kind | 意味 | パネルの呼び名 (目安) |
| --- | --- | --- |
| `single` | 単一選択 | 単一選択 |
| `multiple` | 複数選択 | 複数選択 |
| `short` | 短い自由記述 | 短い回答 |
| `long` | 長い自由記述 | 長い回答 |
| `rating` | 数値の段階評価 | レーティングスケール |

* 選択式は `single` と `multiple` である。`choices` を使う。
* `rating` は、選択肢の列にはしない。`score_*` とラベルを使う。
* `short` / `long` の文字数は、文書に持たない。

## 状態

| status | 意味 |
| --- | --- |
| `draft` | 不足がある。S2J Webinar はアンケートとして読まない |
| `ready` | 不足がない。助言が残っていても `ready` |

## 正規化方針

`evaluate` (および下書き API が文書を読む場合) は、検査の前に次を行う。

* `questions` の順は、呼び出し側が渡した順を保つ。
* 空文字の選択肢は、「空でない選択肢」に数えない。
* 未知のキーは、無視してかまわない (前方互換)。
* **欠落のデフォルト値** (キーがない場合だけ埋める):
  * `required` → `false`
  * `identifies_respondent` → `false`
  * `purpose` → `""`
  * `choices` → `[]` (`single` / `multiple` で使う。他の答え方でも空配列にしてよい)
* **答え方に使わない既知キーは除去する** (不足コードは増やさない):
  * `answer_kind` が `rating` 以外の場合、`score_min` / `score_max` / `label_low` / `label_high` を落とす
  * 空の `choices` は空配列またはキーなしにそろえてよい
  * **空でない `choices` が `short` / `long` / `rating` にある場合は除去しない**。検査で `choices_unexpected` にする
* `required` / `identifies_respondent` の coerce (不足コードは増やさない)。**PHP の `(bool)` キャストそのものにはしない** (`(bool) "false"` が真になるため)。次だけを真／偽とし、それ以外は `false` にフォールバックする:
  * 真: `true`、`1`、`"1"`、`"true"`、`"yes"`、`"on"` (文字列は大小無視してよい)
  * 偽: `false`、`0`、`"0"`、`""`、`null`、キー欠落
  * それ以外 (例: `"false"` 文字列、配列、オブジェクト) → `false`
* その他、既知キーの型が不正で直せない場合は不足にする (例: `rating` の境界が整数でない → `rating_bounds_invalid`)。

## 関連

* 不足: [validation_spec.md](./validation_spec.md)
* 契約: [../contracts/data_contract_spec.md](../contracts/data_contract_spec.md)
