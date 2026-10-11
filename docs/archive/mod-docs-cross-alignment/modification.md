# mod-docs-cross-alignment

## 目的

`docs/` 配下の仕様 Markdown について、齟齬・あいまいさ・用語ズレを解消し、確定仕様として維持できる状態にする。

## スコープ

* 対象: `docs/**/*.md` (正本)。`docs_mod/` には仕様ドラフト `.md` は置かれていなかった (README のみ)。
* 反映: validation / concept / service_spec / data_contract / governance / testing への文言修正と改訂履歴の追記。

## スコープ外

* PHP 実装
* プラグイン repo の docs 変更 (参照整合は Survey / Webinar 既存正本に追随)

## 完了定義

* [x] 同一設問の複数不足コードを validation で明文化
* [x] Webinar 添付契機を「通常同期の `attach_survey`」に統一 (「作成直後」表記を廃止)
* [x] `npm run lint:docs` 成功
* [x] archive 三点セットと `docs/README.md` 整備
