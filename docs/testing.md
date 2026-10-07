<!--
目的：「テスト戦略」の明文化
-->

# S2J Webinar Survey Service - テスト戦略

本ドキュメントは、品質を継続的に保証するための **テスト戦略** を定義します。

## 設計意図 (ゴール)

WordPress も Zoom もモデル API も使わずに、検査・助言・下書き分解の全分岐を固定します。

## 責務

* 何を、どのレベルでテストするかを正本とすること。

## 非責務

* CI YAML の細部 ([engineering/ci.md](./engineering/ci.md))
* プラグインの e2e
* 生のカバレッジ HTML を仕様や archive に貼ること (生成物の置き場は下記)
* イニシアチブ証跡の三点セット ([governance/documentation_governance.md](./governance/documentation_governance.md) の archive 規則)

## テストレベル

| レベル | 内容 | 環境 |
| --- | --- | --- |
| Unit | evaluate / draft の純関数 | PHPUnit、WP なし |
| 契約スナップショット (任意) | 代表的な入出力 JSON | PHPUnit |
| プラグイン結合 | メタ保存と表示 | プラグイン repo |

## 必須カバレッジ (規則)

* 不足コードは、各コードについて真になる入力を1件以上
* 助言コードも同様
* `max_questions` が1未満の場合 `InvalidArgumentException`
* `ready` で助言だけが残るケース
* 正規化: 欠落の `required` 等をデフォルト値で埋める。`rating` 以外の `score_*` を除去する。bool coerce (`"1"` → true、`"false"` 文字列 → false。`(bool)` キャストではない)
* `build_draft_prompt` が `{ prompt_text, requested_count }` を返す (`survey` の件数式。0は `survey` のみ。`prompt` / `choices` は1か例外)
* `survey` 下書き: `requested_count` での切り詰め、分解不能で空
* 下書き候補: `rating` に `score_min` / `score_max` (欠けたら 0 / 10)、`long` / `short` に文字数欄を載せない、Zoom キー名を載せない
* `evaluate` は欠けた `score_*` を埋め込まず `rating_bounds_invalid`
* kind `prompt`: `parse_draft_response` が `$context` の focus から `answer_kind` 等を引き継ぐ
* `choices` で focus が `short` などの場合 `InvalidArgumentException` (`build` / `parse`)
* `choices` を `short` に付けた場合の `choices_unexpected`
* `rating_bounds_invalid` の境界 (`score_min === score_max` を含む)

## 外部依存

* モデル応答は、文字列フィクスチャで与える。ライブ呼び出しは、しない。
* 時刻・乱数に依存するテストは、書かない。

## 実行

```bash
composer test
```

## 生成物の置き場

PHPUnit などの **機械成果物** はリポジトリにコミットしない。ルートの `/coverage/` に出す (`.gitignore` 済み)。

| 種類 | 置き場 | Git |
| --- | --- | --- |
| カバレッジ (HTML / Clover 等) | `/coverage/` | 無視 |
| JUnit XML (使う場合) | `/coverage/` 配下 | 無視 |
| PHPUnit キャッシュ | `.phpunit.cache/` / `.phpunit.result.cache` | 無視 |
| 配布 tarball | `/artifacts/` (既存) | 無視 (`.gitkeep` のみ) |

`phpunit.xml` の coverage / logfile は `/coverage/` 向けに設定する。CI では Actions artifact で渡せばよく、専用の恒久フォルダは増やさない。

人が読む合格証跡 (`test-results.md` 等) は `/coverage/` には置かず、イニシアチブ証跡として [governance/documentation_governance.md](./governance/documentation_governance.md) に従う。

## 関連

* Core: [core/validation_spec.md](./core/validation_spec.md)、[core/advice_spec.md](./core/advice_spec.md)、[core/draft_spec.md](./core/draft_spec.md)
* CI: [engineering/ci.md](./engineering/ci.md)
* イニシアチブ証跡: [governance/documentation_governance.md](./governance/documentation_governance.md)、[archive/README.md](./archive/README.md)
