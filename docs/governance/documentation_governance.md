<!--
目的：「README と docs の整合、用語」の明文化
-->

# S2J Webinar Survey Service - ドキュメンテーションガバナンス

本ドキュメントは、ユーザー向け説明と仕様の **整合ルール** を定義します。

## 設計意図 (ゴール)

公開 API の説明・命名・例が、実装と contracts からずれないようにします。

## Source of Truth

| 対象 | 正本 |
| --- | --- |
| 規則 | `docs/core/*` |
| 型 | `docs/contracts/*` |
| 公開 PHP API | `docs/interfaces/php_api_spec.md` |
| 最短手順・インストール | ルート `README.md` |
| 統合の見取り図 | `docs/service_spec.md` (要約。規則の正本ではない) |

矛盾時は **core / contracts を正** とし、`service_spec.md`、README、usage を追随させます。`service_spec.md` のコード表を直すだけでは不十分です。

## 用語

* 正式なコード名は [../contracts/data_dictionary.md](../contracts/data_dictionary.md) に従う。
* 画面の日本語 (単一選択、短い回答など) はプラグイン仕様の用語である。ライブラリ docs では `answer_kind` を優先する。
* Zoom の `type` (`short_answer` 等) を、本ライブラリの `answer_kind` と混同して書かない。

## Lint

* `@s2j/docs-linter` を SoT とする。
* ローカルと CI で同じ `npm run lint:docs` を使う。
* 対象は `README.md` と仕様 Markdown である。

## 分割ルール

* 新規仕様は [../specs.md](../specs.md) のレイヤーに分類する。
* Similarity にある OpenAPI / SRE 層を、必要になるまで追加しない。

## 改訂フロー

* 確定仕様の正本は `docs/` である。
* 大きな改訂案は `docs_mod/` で起草し、レビュー・合意のあと `docs/` に反映する。
* プラグインや webinar-service からのリンクは、`docs/` を指すように保つ。

## イニシアチブ証跡 (archive)

実装・改修の区切りごとに、人が読める合格証跡を残します。PHPUnit の HTML / XML など機械成果物とは別です (置き場は [../testing.md](../testing.md) の `/coverage/`)。

### 命名

| 種類 | フォルダー | いつ使うか |
| --- | --- | --- |
| 実装イニシアチブ | `docs/archive/impl-<slug>/` | まだない能力を初めて入れる |
| 改修イニシアチブ | `docs/archive/mod-<slug>/` | すでに `docs/` にある仕様・振る舞いを変える |

`<slug>` は短い kebab-case とする。SemVer はフォルダー名に入れず、`status.md` や CHANGELOG に書く。

### 三点セット

作業中は `docs_mod/` に置き、完了時に archive にフリーズする。

| ファイル | 書くこと | 書かないこと |
| --- | --- | --- |
| `modification.md` | 目的、スコープ内外、タスク表、完了定義 | 長い仕様本文 (正本は `docs/`) |
| `status.md` | 進捗サマリー、完了条件のチェック、残ギャップ | 生のカバレッジ HTML |
| `test-results.md` | 仕様条件 ID ごとの PASS / WARN / FAIL | PHPUnit 生ログの丸貼り |

`test-results.md` の ID は、[../testing.md](../testing.md) の必須カバレッジや core のコード名に寄せる (例: `VAL-choices_unexpected`、`API-build-returns-count`)。

### ライフサイクル

1. **開始** … `docs_mod/` に三点セットを置く (必要なら仕様ドラフトも)
2. **作業** … 合意した仕様は都度 `docs/` に反映する。証跡三点は `docs_mod/` で更新する
3. **フリーズ** … 下記をすべて満たしたら `docs/archive/impl-<slug>/` または `docs/archive/mod-<slug>/` にコピーして固定する
   * 該当する `docs/` 仕様が最新である
   * `test-results.md` に FAIL がない (WARN は理由付きのみ可)
   * CHANGELOG の unreleased に一行ある
4. **フリーズ後** … archive 配下は原則変更しない。続きは新しい `mod-*` (または `impl-*`) を切る
5. **片付け** … `docs_mod/` の三点は削除してよい (ディレクトリと [../../docs_mod/README.md](../../docs_mod/README.md) は残す)

### いつ切るか

* コードまたは契約が動くイニシアチブでは三点セットを切る。
* docs だけの整備は archive 任意とする。
* 初回実装は大きくまとめず、縦に切ってよい (例: `impl-skeleton` → `impl-validate-advise` → `impl-draft`)。

### 全体 status との関係

* [../status.md](../status.md) は製品全体の「いま」である。
* `docs/archive/.../status.md` は、そのイニシアチブ完了時点の凍結である。
* 全体 status に、進行中の `docs_mod/` と直近 archive へのリンクを短く置いてよい。進捗表の二重管理はしない。

索引は [../archive/README.md](../archive/README.md) である。
