<!--
目的：「用語、型、制約」の明文化
-->

# S2J Webinar Survey Service - データ辞書

本ドキュメントは、本プロジェクトで扱う用語の **意味、制約、利用文脈** を定義します。
Contracts 層における意味定義の Source of Truth とします。

## 非対象

* WordPress のメタキー・オプション名 (プラグイン仕様)
* Zoom API のフィールド名 (webinar-service)
* UI のラベル文言 (プラグイン)

## 用語

### SurveyDocument

その回のアンケート設問一式です。`internal_name` と順序付き `questions` を持ちます。計算後に `status` が付きます。

### Question

1件の設問です。`prompt` と `answer_kind` を必ず持ちます。

### answer_kind

答え方です。値は `single` / `multiple` / `short` / `long` / `rating` のみです。Zoom の `type` ではありません。

### status

| 値 | 意味 |
| --- | --- |
| `draft` | 不足あり。添付に使わない |
| `ready` | 不足なし。助言があっても可 |

### deficiency code

不足コードです。1件でもあれば `draft` です。一覧は [../core/validation_spec.md](../core/validation_spec.md) です。

### advice code

助言コードです。保存を拒みません。一覧は [../core/advice_spec.md](../core/advice_spec.md) です。

### purpose

次回の企画で何を決めるかです。回答者には出しません。空でも `ready` 可です。

### internal_name

Zoom の一覧向けの名前です。回答者には出しません。本ライブラリは送信しません。

### identifies_respondent

回答者を特定しうる問いである、という運営者の印です。最後に置くよう助言します。

### choices

`single` / `multiple` の選択肢ラベルです。空文字は「空でない選択肢」に数えません。

### score_min / score_max

`rating` の境界です。整数で、`score_min` < `score_max` です。目安は0と10です。

* `evaluate` では埋め込まない。欠けていれば不足 `rating_bounds_invalid` です ([../core/validation_spec.md](../core/validation_spec.md))。
* `parse_draft_response` では、候補の分解時に欠けていれば目安0と10を候補に載せます ([../core/draft_spec.md](../core/draft_spec.md))。

### DraftKind

下書き依頼の種類です。`survey` / `prompt` / `choices` です。

### candidate

人が採用する前の設問または選択肢の断片です。文書の正本には入りません。形は文書の Question と同じ Zoom 非依存の欄です。答え方ごとの詳細は [../core/draft_spec.md](../core/draft_spec.md) です。Zoom の `rating_min_value` などは使いません。

### requested_count

`build_draft_prompt` が返す、その依頼で期待する候補件数です。`parse_draft_response` の切り詰め上限に使います。プラグインが別途計算しません。0になりうるのは kind `survey` のみです。`prompt` / `choices` は常に1です (不正時は例外)。

## 一貫性ルール

* コード名は snake_case の英数字である。
* プラグインの表示文はコードから導く。コード文字列を画面に出さない。
* Zoom の `short_answer` などの文字列は、本辞書に登録しない。
