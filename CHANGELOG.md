# S2J Webinar Survey Service - CHANGELOG

## unreleased

## 0.0.1 - 2026-10-07

### Changed

* `docs_mod/service_spec.md` の設問総数の上限を、プラグインのサイト設定が渡す値に確定 (未設定の初期値は6。ライブラリには埋め込まない)
* 誘導と二重の問いは初版では判定しない、と記録 (周知はプラグインのパネル指針)
* 未決事項から、設問総数の上限と誘導・二重問いの判定を外す
* 答え方を `closed` / `open` から `single` / `multiple` / `short` / `long` / `rating` の5つに変更 (`rating` 用の境界・ラベル欄を追加)
* Zoom への写像を本ライブラリの外 (S2J Webinar Service) に確定し、初版5種の `type` 対応を記録
* 画像・スキップ・マッチング・ランク・空欄記入は初版以降の検討とし、未決事項の写像項目を外す

## 0.0.1 - 2026-10-06

### Added

* 確定前のサービス仕様を `docs_mod/service_spec.md` に記載
* ドキュメント lint を追加 (`@s2j/docs-linter` ^1.0.27、`npm run lint:docs`、GitHub Actions)
* npm v12以降向けに `.npmrc` の `allow-git=all` と `package.json` の `allowScripts` を追加

### Changed

* `.gitignore` を Composer、Node、テスト成果物向けに拡張
* `.gitattributes` で開発専用パスを Composer 配布物から除外
* `.vscode/settings.json` で `json.schemaDownload.enable` と textlint の保存時修正を有効化
