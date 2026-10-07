<!--
目的：「下書き依頼文の組立と返却文の分解」の明文化
-->

# S2J Webinar Survey Service - 下書き仕様

本ドキュメントは、人が採用する前の **下書き候補** について、依頼文の組立と返却文の分解を定義します。

## 設計意図 (ゴール)

プラグインがコネクタに1回送る依頼文を安定して作り、返ってきた文を文書に書かない候補に分けます。

## 責務

* 依頼の種類ごとの依頼文を組み立てること。
* 返却文を、依頼件数を上限とする候補に分けること。

## 非責務

* モデル HTTP、コネクタ、API キー
* 候補をイベントメタに書くこと
* 企画上の目的をモデルに書かせること

## 依頼の種類

| 種類 (kind) | 件数 (`requested_count`) | 中身 |
| --- | --- | --- |
| `survey` | `max(0, max_questions − 既存設問数)`。0なら依頼しない | 各件は下記「候補の形」。`purpose` は空 |
| `prompt` | 常に1 (不正なら例外。0は返さない) | 開いている1件の設問文の書き換え。下記「kind が `prompt`」 |
| `choices` | 常に1 (不正なら例外。0は返さない) | 開いている選択式向け。候補は `choices` だけを埋めた断片 |

どれも、1回の操作で1本の依頼文です。

`build_draft_prompt` は `{ prompt_text, requested_count }` を返す。件数の正本はここであり、プラグインが別途計算しない。`parse_draft_response` には同じ `requested_count` を `$context` で渡す。**`requested_count` が0になりうるのは kind `survey` だけ**である。

focus の検証 (`prompt` / `choices`):

* `focus_index` は必須の `int` (null 不可)。欠落、null、範囲外 → `InvalidArgumentException` (`build` と `parse` の両方)
* `choices` で focus の `answer_kind` が `single` / `multiple` 以外 → 同じく例外
* `prompt` は範囲内ならどの答え方でもよい
* `survey` では `focus_index` は不要 (あっても無視)

## 依頼文に含める規則 (`survey`)

* `single` / `multiple` / `rating` を先にし、自由記述 (`short` または `long`) は最後に多くて1件、と書く。
* 回答者を特定する問いと、必須の自由記述は依頼しない。
* 候補の `required` と `identifies_respondent` は偽、`purpose` は空である。

## 候補の形

候補は、文書の Question と同じ **Zoom 非依存** の形です ([document_spec.md](./document_spec.md))。Zoom の `type` や `rating_min_value` などは載せません。人が採用したあと `evaluate` に渡せるようにします。

### 共通 (kind が `survey` または `prompt`)

| フィールド | 値 |
| --- | --- |
| `prompt` | 設問文。空ならその候補は捨てる |
| `answer_kind` | `single` / `multiple` / `short` / `long` / `rating` |
| `required` | 常に `false` |
| `identifies_respondent` | 常に `false` |
| `purpose` | 常に `""` |

### 答え方ごと

| answer_kind | choices | score_min / score_max | label_low / label_high |
| --- | --- | --- | --- |
| `single` / `multiple` | 空でない短いラベルを2つ以上 (少数) | 載せない (キーなしでよい) | 載せない |
| `short` / `long` | 空配列、またはキーなし | 載せない。文字数欄は持たない | 載せない |
| `rating` | 空配列、またはキーなし | 整数で `score_min` < `score_max`。欠けていれば目安として `0` と `10` を候補に載せる | 空文字可。モデルが返したら入れてよい |

* `short` と `long` の文字数は文書にも候補にも持たない。添付時に Zoom API のデフォルトに任せる (写像は webinar-service)。
* `evaluate` は `score_min` / `score_max` を埋め込まない。候補への目安埋めは `parse_draft_response` だけである。
* kind が `choices` の場合は、候補は `choices` (短いラベルの列) を主とし、他フィールドは省略してよい。プラグインが開いている設問にマージする。
* 形を満たさない候補は捨てる。すべて捨てた場合は候補列は空である。

### kind が `prompt`

開いている設問 (`focus_index`) の書き換えです。返却から得るのは主に新しい `prompt` です。**引き継ぎは `parse_draft_response` が `$context['document']` と `$context['focus_index']` を見て行います** (プラグインはマージしない)。

引き継ぐ欄:

* `answer_kind`
* `choices` (`single` / `multiple` の場合)
* `score_min` / `score_max` / `label_low` / `label_high` (`rating` の場合)

`required` は `false`、`identifies_respondent` は `false`、`purpose` は `""` にリセットします (下書き共通)。引き継ぎ後も「候補の形」を満たさなければ捨てます。

## 渡してよい文脈

* イベントの題名
* 各設問の答え方
* 運営者が書いた企画上の目的
* すでに文書にある設問文

渡してはいけないもの:

* 登壇者のメール
* 申込者の情報

## 分解規則

* 切り詰め上限は `$context['requested_count']` (直前の `build_draft_prompt` の戻り) である。
* 返ってきた件数がそれより多い場合は、その件数だけ残す。
* 少ない場合は、分けられた件数だけ返す。
* 分けられない場合、または `requested_count` が1未満の場合は、候補は空である。プラグインは文書を変えない。
* 候補のままでは `ready` にならない。採用後に [validation_spec.md](./validation_spec.md) / [advice_spec.md](./advice_spec.md) を通す。

## 依頼文の形 (方針)

* 自然言語の指示文である。モデル固有の JSON schema 強制は、初版の必須としない。
* 分解側は、番号付き箇条書きや明確な区切りを期待する実装でかまわない。期待と違う場合は候補空である。

詳細なプロンプト文言のテンプレートは、実装時に `Draft` モジュールに置き、本仕様の規則に従います。文言の変更は、分解のテストを同時に更新します。

## 関連

* 文書の Question: [document_spec.md](./document_spec.md)
* 契約 (DTO 例): [../contracts/data_contract_spec.md](../contracts/data_contract_spec.md)
* 使用方法: [../interfaces/usage_spec.md](../interfaces/usage_spec.md)
