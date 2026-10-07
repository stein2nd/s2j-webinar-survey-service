<!--
目的：「不足コードと draft / ready」の明文化
-->

# S2J Webinar Survey Service - 検査仕様

本ドキュメントは、文書を `ready` にしない **不足** の規則を定義します。

## 設計意図 (ゴール)

保存の正本として使ってよいかを、機械的に判定します。足りない場合は必ず `draft` です。

## 責務

* 不足コードとその条件を定義すること。
* 不足が1件でもあれば `status` を `draft` にすること。

## 非責務

* 助言 ([advice_spec.md](./advice_spec.md))
* 表示用の日本語文 (プラグイン)
* 上限超過の扱い (助言。不足にはしない)

## 不足コード

| コード | 条件 |
| --- | --- |
| `internal_name_empty` | 内部名が空 (trim 後) |
| `questions_empty` | 設問が0件 |
| `prompt_empty` | 設問文が空 (trim 後) |
| `answer_kind_invalid` | `answer_kind` が5種のどれでもない |
| `choices_missing` | `single` または `multiple` で、空でない選択肢が2つ未満 |
| `choices_unexpected` | `short` / `long` / `rating` なのに、空でない選択肢がある |
| `rating_bounds_invalid` | `rating` で、`score_min` と `score_max` がそろわない、整数でない、または `score_min` が `score_max` 以上。`evaluate` は欠けた値を0/10で埋めない |

## 適用順 (推奨)

1. 文書全体: `internal_name_empty`、`questions_empty`
2. 各設問: `prompt_empty`、`answer_kind_invalid`、選択肢・レーティング系
3. 不足が1件以上 → `draft`、0件 → `ready`

同じ設問に複数の不足が付いてかまいません。コードの列は安定した順 (上記表の順、設問は入力順) で返します。

## 初版で判定しないもの

* 誘導文
* 二重の問い (一文に判断が2つ)
* 設問文の意味的な良し悪し

これらは不足にも助言にもしません。周知はプラグインの「大事な指針」です。

## 関連

* 文書: [document_spec.md](./document_spec.md)
* 公開結果形: [../contracts/data_contract_spec.md](../contracts/data_contract_spec.md)
