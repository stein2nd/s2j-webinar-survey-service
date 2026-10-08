<!--
目的：「助言コード (保存を拒まない)」の明文化
-->

# S2J Webinar Survey Service - 助言仕様

本ドキュメントは、保存を拒まない **助言** の規則を定義します。

## 設計意図 (ゴール)

回答の負担と、企画への使い道について、運営者に気付きを返します。状態は変えません。

## 責務

* 助言コードとその条件を定義すること。
* `ready` の文書にも助言を付けられること。

## 非責務

* 不足判定 ([validation_spec.md](./validation_spec.md))
* 表示用のメッセージ文 (プラグイン。国際化関数を経由)
* 誘導・二重問いの自動判定 (初版の対象外)

## 助言コード

| コード | 条件 |
| --- | --- |
| `too_many` | 設問数が、引数の上限を超える |
| `open_before_closed` | `short` または `long` より後ろに、`single` / `multiple` / `rating` がある |
| `required_open` | `short` または `long` が必須である |
| `identity_not_last` | `identifies_respondent` が真の設問が、最後ではない |
| `purpose_missing` | その設問の `purpose` が空 (trim 後) |

## 順番の規則 (助言の根拠)

* 答えやすい選択と段階評価 (`single` / `multiple` / `rating`) を先にする。
* 自由記述 (`short` / `long`) を後ろにまとめる。
* 回答者を特定する問いは、最後に置く。
* 必須の自由記述と、特定の問いは、回答を躊躇させやすいものとして助言する。

## 上限

* 上限は引数で指定する。ライブラリに初期値も下限・上限も埋めない。
* プラグインが渡す値の目安は、サイト設定で1以上15以下、未設定時は6である。
* `evaluate` / 下書き API に渡す `max_questions` が1未満の場合は、公開面で `InvalidArgumentException` とする ([../interfaces/php_api_spec.md](../interfaces/php_api_spec.md))。助言にも不足にもしない。
* 設問数が `max_questions` を超える場合は `too_many` のみである。`draft` にはしない。

## `purpose_missing`

* `purpose` が空でも `ready` にできる。
* 設問ごとにコードを付ける。

## 関連

* 文書: [document_spec.md](./document_spec.md)
* 契約: [../contracts/data_contract_spec.md](../contracts/data_contract_spec.md)
