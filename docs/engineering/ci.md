<!--
目的：「CI 品質ゲート」の明文化
-->

# S2J Webinar Survey Service - CI

本ドキュメントは、merge 前に満たす **品質ゲート** を定義します。

## 設計意図 (ゴール)

契約と規則の回帰を自動で検知し、仕様と実装の乖離を防ぎます。

## 責務

* ジョブの責務分割と必須チェックを定義すること。

## 非責務

* Packagist への publish 操作そのもの ([build_and_release.md](./build_and_release.md))
* 外部モデルや Zoom へのライブ呼び出し

## 品質ゲート方針

* 品質ゲートは CI で自動実行し、失敗時は merge 不可とする。
* **1本の workflow** ([`.github/workflows/ci.yml`](../../.github/workflows/ci.yml)) が PR / `main` push / merge queue で常に起動する (workflow レベルの `path filter` は使わない)。
* 変更検知 ([`dorny/paths-filter`](https://github.com/dorny/paths-filter)) の結果に応じ、**job レベル**で PHP 品質または docs lint を実行する。該当パスがない job は **Skipped** (Required には成功扱いで通る)。
* **Ruleset で Required にするのは `merge-gate` のみ**。`merge-gate` は、実行された job が failure / cancelled でないことを確認する。

## 変更検知 (初版)

| 出力 | パス例 | 起動 job |
| --- | --- | --- |
| `php` | `src/**`, `tests/**`, `composer.*`, PHPUnit / PHPStan / PHPCS 設定 | `php-quality` |
| `docs` | `docs/**`, `**/*.md`, textlint 設定, `package.json` / lock | `docs-lint` |
| `workflows` | `.github/workflows/**` | **両方** (CI 定義変更時は回帰を避ける) |

Markdown のみ / PHP のみの PR でも、該当 job だけが走り **`merge-gate` が green ならマージ可能** です。

## CI ジョブ

| ジョブ | 内容 |
| --- | --- |
| `changes` | `path filter` 出力 |
| `php-quality` | PHP **8.3** — `composer install` → validate → PHPUnit → PHPStan → PHPCS |
| `docs-lint` | `npm ci` → `npm run lint:docs` |
| `merge-gate` | 上記品質 job の結果集約 (**Required**) |

WordPress 統合ジョブは、本ライブラリ単体では持ちません。プラグイン側の CI に委ねます。

## ローカルコマンド (想定)

```bash
npm run quality
npm run lint:docs
```

個別に回す場合:

```bash
npm test
npm run phpstan
npm run phpcs
npm run validate:composer
npm run verify:dist
composer run lint:php
```

カバレッジなど PHPUnit の生成物は `/coverage/` に出す ([../testing.md](../testing.md))。CI では必要なら Actions artifact として上げる。

## 完了条件

* `main` の required checks に **`merge-gate`** が含まれること (`php-quality` / `docs-lint` を個別 Required にしない)。
* 外部 API なしで、unit が緑であること。
