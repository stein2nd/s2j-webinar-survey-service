# S2J Webinar Survey Service - CHANGELOG

## unreleased

## 0.0.1 - 2026-10-07

### Changed

* `docs_mod/service_spec.md` の設問総数の上限を、プラグインのサイト設定が渡す値に確定 (未設定の初期値は6。ライブラリには埋め込まない)
* 誘導と二重の問いは初版では判定しない、と記録 (周知はプラグインのパネル指針)
* 未決事項から、設問総数の上限と誘導・二重問いの判定を外す

## 0.0.1 - 2026-10-06

### Added

* 確定前のサービス仕様を `docs_mod/service_spec.md` に記載
* ドキュメント lint を追加 (`@s2j/docs-linter` ^1.0.27、`npm run lint:docs`、GitHub Actions)
* npm v12以降向けに `.npmrc` の `allow-git=all` と `package.json` の `allowScripts` を追加

### Changed

* `.gitignore` を Composer、Node、テスト成果物向けに拡張
* `.gitattributes` で開発専用パスを Composer 配布物から除外
* `.vscode/settings.json` で `json.schemaDownload.enable` と textlint の保存時修正を有効化
