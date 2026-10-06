# S2J Webinar Survey Service - CHANGELOG

## unreleased

## 0.0.1 - 2026-10-06

### Added

* 確定前のサービス仕様を `docs_mod/service_spec.md` に記載
* ドキュメント lint を追加 (`@s2j/docs-linter` ^1.0.27、`npm run lint:docs`、GitHub Actions)
* npm v12以降向けに `.npmrc` の `allow-git=all` と `package.json` の `allowScripts` を追加

### Changed

* README の見出しを `S2J Webinar Survey Service` に変更
* `.gitignore` を Composer、Node、テスト成果物向けに拡張
* `.gitattributes` で開発専用パスを Composer 配布物から除外
* `.vscode/settings.json` で `json.schemaDownload.enable` と textlint の保存時修正を有効化
