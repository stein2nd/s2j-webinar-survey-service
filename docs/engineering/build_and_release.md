<!--
目的：「ビルドおよびリリース」の明文化
-->

# S2J Webinar Survey Service - ビルドおよびリリース

本ドキュメントは、Composer パッケージとしての **配布とバージョン** を定義します。

## 設計意図 (ゴール)

プラグインが Packagist の名前だけで require できる状態を維持します。

## 責務

* セマンティックバージョンの付け方
* 配布物に含めるパス / 除外するパス

## 非責務

* プラグインの WordPress.org リリース
* Zoom 連携のリリース列車

## バージョン

* 形式は SemVer とする。
* 公開 API と、不足/助言コードの破壊的変更は、major とする ([../interfaces/php_api_spec.md](../interfaces/php_api_spec.md))。
* `docs/` の仕様変更は CHANGELOG のドキュメント節に記録する。パッケージ機能が未公開のうちはタグ必須ではない。
* `docs_mod/` のみの起草は、`docs/` に反映するまで CHANGELOG に載せなくてよい。

## 配布物

* `.gitattributes` の `export-ignore` で、テストや CI やエディター設定、および `docs` / `docs_mod` の扱いを明確にする。
* 仕様を配布物に含めるかは、README からたどれる範囲に限定してかまわない。

## Packagist

* パッケージ名: `s2j/webinar-survey-service`
* GitHub 連携でタグを公開する。
* `composer.json` の `autoload` は `S2J\WebinarSurveyService\` → `src/` である。

## リリース手順 (概要)

1. CHANGELOG の unreleased を、バージョン節に移す
2. `composer.json` / 必要ならタグメッセージをそろえる
3. タグを push し Packagist が取り込むのを確認する
4. プラグイン側で制約を更新する (別 PR)

## 関連

* CI: [ci.md](./ci.md)
* CHANGELOG: [../../CHANGELOG.md](../../CHANGELOG.md)
