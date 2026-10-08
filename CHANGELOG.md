# S2J Webinar Survey Service - CHANGELOG

## unreleased

## 0.0.1 - 2026-10-08

### Changed

* `docs/` / `docs_mod/` の表記をドキュメント lint に合わせた (「下記」、「際に」、読点、`フォルダー` など)
* 仕様文の「日本語を表示」系を「適切なメッセージ文を表示」に統一 (プラグイン側 i18n 前提。不足・助言の表示文言も同様)

## 0.0.1 - 2026-10-07

### Added

* Composer 実装向けに、Similarity Service の `docs/` 構成に倣った仕様分割を追加 (`specs.md` 起点、overview / concept / architecture / principles、core、contracts、interfaces、engineering、governance、testing、status)
* PHPUnit 生成物は `/coverage/`、実装・改修の証跡は `docs_mod/` → `docs/archive/impl|mod-<slug>/` 三点セット、とガバナンスに記録

### Fixed

* `docs_mod` 間の齟齬: `ready` 文書は S2J Webinar が読む (写像は webinar-service)、初版到達点に下書きを含める、`max_questions` が1未満は `InvalidArgumentException`
* 公開関数名を `build_draft_prompt` / `parse_draft_response` に統一、`choices_unexpected` を「空でない選択肢」、下書き API の `max_questions` 未満も例外、とそろえる
* 下書き候補の形を追記 (`rating` / `long` / `short` を含む Question 形。Zoom キーは載せない)。正本は `docs/core/draft_spec.md`
* あいまいさの締め: `score_*` は evaluate 非埋め / 下書き分解のみ目安、`prompt` は focus 引き継ぎ、`too_many` 文言、規則正本は `core/`、公開面は関数3つ
* 下書き API: `build_draft_prompt` は `{ prompt_text, requested_count }`、`parse_draft_response` は context で引き継ぎ・切り詰め。正規化はデフォルト値埋めと不要キー除去。`choices` の focus 不整合は例外。契約例の分離、architecture ツリーと status の SoT 表現を修正
* 文面の締め: `requested_count` 0は `survey` のみ、`focus_index` は kind 別に必須、bool は coerce (不足コードなし)、用語は「デフォルト値」に統一
* 契約表を「入力 / 正規化後」に分離。bool coerce を明示リスト化し、PHP `(bool)` キャストは使わないと記録

### Changed

* `docs_mod/service_spec.md` の設問総数の上限を、プラグインのサイト設定が渡す値に確定 (未設定の初期値は6。ライブラリには埋め込まない)
* 誘導と二重の問いは初版では判定しない、と記録 (周知はプラグインのパネル指針)
* 未決事項から、設問総数の上限と誘導・二重問いの判定を外す
* 答え方を `closed` / `open` から `single` / `multiple` / `short` / `long` / `rating` の5つに変更 (`rating` 用の境界・ラベル欄を追加)
* Zoom への写像を本ライブラリの外 (S2J Webinar Service) に確定し、初版5種の `type` 対応を記録
* 画像・スキップ・マッチング・ランク・空欄記入は初版以降の検討とし、未決事項の写像項目を外す
* 確定した仕様キットを `docs_mod/` から `docs/` に移行。`docs_mod/` は以降の改訂案の起草用として残す
* README にライブラリ概要と `docs/` / `docs_mod/` / archive への導線を追加
* `lint:docs` の対象に `docs_mod/**/*.md` を追加
* `.gitattributes` で `docs_mod` を Composer 配布物から除外
* `docs_mod/README.md` を追加 (改訂案とイニシアチブ証跡三点の起草手順)

## 0.0.1 - 2026-10-06

### Added

* 確定前のサービス仕様を `docs_mod/service_spec.md` に記載
* ドキュメント lint を追加 (`@s2j/docs-linter` ^1.0.27、`npm run lint:docs`、GitHub Actions)
* npm v12以降向けに `.npmrc` の `allow-git=all` と `package.json` の `allowScripts` を追加

### Changed

* `.gitignore` を Composer、Node、テスト成果物向けに拡張
* `.gitattributes` で開発専用パスを Composer 配布物から除外
* `.vscode/settings.json` で `json.schemaDownload.enable` と textlint の保存時修正を有効化
