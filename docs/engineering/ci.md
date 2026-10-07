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
* ジョブは、責務単位で分離する。
* 外部プロバイダ依存のテストは、CI に含めない。

## CI マトリックス (初版想定)

| ジョブ | 内容 |
| --- | --- |
| php-quality | `composer install` → PHPUnit → PHPStan → PHPCS |
| docs-lint | `README.md` / `docs/**` (および起草中の `docs_mod/**`) 変更時に `npm run lint:docs` |

WordPress 統合ジョブは、本ライブラリ単体では持ちません。プラグイン側の CI に委ねます。

## ローカルコマンド (想定)

```bash
composer test
composer run lint:php
npm run lint:docs
```

カバレッジなど PHPUnit の生成物は `/coverage/` に出す ([../testing.md](../testing.md))。CI では必要なら Actions artifact として上げる。

## 完了条件

* `main` の required checks に、php-quality と docs-lint が含まれること。
* 外部 API なしで、unit が緑であること。
