# S2J Webinar Survey Service - 仕様書の起点

本プロジェクトの仕様は、下記のドキュメントに分散して定義します。
各ドキュメントへの導線のみを提供し、詳細な仕様は個別ファイルに委譲します。

統合の見取り図は [service_spec.md](./service_spec.md) です (要約。規則の正本は `core/`)。構成は [S2J Similarity Service の docs/](https://github.com/stein2nd/s2j-similarity-service/tree/main/docs) に倣い、本ライブラリに必要な層だけを置いています。次回以降の仕様改訂案は `docs_mod/` で起草し、合意後に本ディレクトリに反映します。

## 読み方ガイド

本ドキュメント群は、「Why → What → How → Usage → Operations」の順で理解することを前提とします。

### 推奨読み順 (初読)

1. **[concept.md](./concept.md)** — なぜ存在するか
2. **[contracts/](./contracts/data_contract_spec.md)** — 入出力契約
3. **[architecture.md](./architecture.md)** — レイヤと依存
4. **[core/](./core/document_spec.md)** — 文書・検査・助言・下書き
5. **[interfaces/](./interfaces/usage_spec.md)** — 呼び出し方
6. **[engineering/](./engineering/ci.md)** — CI と配布

### 役割別読み順

#### 呼び出し側 (S2J Webinar Survey プラグイン)

1. [interfaces/usage_spec.md](./interfaces/usage_spec.md)
2. [interfaces/php_api_spec.md](./interfaces/php_api_spec.md)
3. [contracts/data_contract_spec.md](./contracts/data_contract_spec.md)

#### 実装者 (本ライブラリ)

1. [architecture.md](./architecture.md)
2. [contracts/](./contracts/data_contract_spec.md)
3. [core/](./core/document_spec.md)
4. [testing.md](./testing.md)
5. [governance/documentation_governance.md](./governance/documentation_governance.md)

## ドキュメント一覧

### Why (基本情報)

| ドキュメント | 内容 |
| --- | --- |
| [概要](./overview.md) | プロジェクト概要、責務、非対応 |
| [コンセプト](./concept.md) | 背景、課題、ユースケース、処理フロー |
| [アーキテクチャー](./architecture.md) | レイヤ、フォルダー、依存方向 |
| [設計原則](./principles.md) | Source of Truth、純関数、境界 |
| [統合見取り図](./service_spec.md) | サービス全体の要約。規則の正本は `core/` |

### Domain (コア)

| ドキュメント | 内容 |
| --- | --- |
| [文書](./core/document_spec.md) | 設問文書の形と状態 |
| [検査](./core/validation_spec.md) | 不足コードと `draft` / `ready` |
| [助言](./core/advice_spec.md) | 助言コード (保存を拒まない) |
| [下書き](./core/draft_spec.md) | 依頼文の組立と返却文の分解 |

### What (契約)

| ドキュメント | 内容 |
| --- | --- |
| [入出力仕様](./contracts/data_contract_spec.md) | DTO、結果レコード |
| [データ辞書](./contracts/data_dictionary.md) | 用語、型、制約 |

### How (インターフェイス)

| ドキュメント | 内容 |
| --- | --- |
| [PHP 公開面](./interfaces/php_api_spec.md) | Composer から見える関数・型 |
| [使用方法](./interfaces/usage_spec.md) | プラグインからの呼び出し例 |

### エンジニアリング

| ドキュメント | 内容 |
| --- | --- |
| [CI](./engineering/ci.md) | 品質ゲート |
| [ビルドおよびリリース](./engineering/build_and_release.md) | Packagist、セマンティックバージョン |

### ガバナンス

| ドキュメント | 内容 |
| --- | --- |
| [ドキュメンテーション](./governance/documentation_governance.md) | README と docs の整合、用語、イニシアチブ証跡 |
| [archive 索引](./archive/README.md) | 完了した実装・改修イニシアチブの凍結一覧 |

### その他

| ドキュメント | 内容 |
| --- | --- |
| [テスト戦略](./testing.md) | テストレベル、`/coverage/`、必須分岐 |
| [実装状況](./status.md) | 機能単位の進捗 (製品全体の「いま」) |

## 本ライブラリに置かない層

Similarity Service にあるが、初版の本ライブラリでは持たないものです。

| Similarity 側 | 本ライブラリ |
| --- | --- |
| OpenAPI / codegen / TS SDK | なし。契約は PHP の DTO と本 docs |
| REST / WordPress HTTP Adapter | なし。HTTP はプラグイン |
| Embedding / 外部モデル呼び出し | なし。依頼文の組立と分解だけ |
| SRE (観測・スケール) | なし。インプロセスの純関数 |
| 認証・課金ガバナンス | なし。秘密情報を扱わない |

Zoom へのアンケート添付と設問型の写像は、[S2J Webinar Service](https://github.com/stein2nd/s2j-webinar-service) の仕事です。本ドキュメント群には写像の実装仕様を置きません。境界の要約だけ [service_spec.md](./service_spec.md) にあります。

## 依存関係ルール

```mermaid
flowchart TD
  A["interfaces (公開関数)"] --> B["core (検査・助言・下書き)"]
  A --> C["contracts (DTO)"]
  B --> C
```

### 禁止事項

* Core が WordPress / HTTP / Zoom / モデル API を知らない。
* Contracts にビジネス規則 (不足判定の本体) を置かない。
* Interfaces にドメイン規則を埋め込まない (薄い入口にする)。

## Source of Truth (SoT)

| 対象 | 正本 |
| --- | --- |
| 設問文書の形と不足・助言の規則 | [core/](./core/document_spec.md) |
| 入出力の型とフィールド | [contracts/data_contract_spec.md](./contracts/data_contract_spec.md) |
| 用語の意味 | [contracts/data_dictionary.md](./contracts/data_dictionary.md) |
| 公開 PHP API | [interfaces/php_api_spec.md](./interfaces/php_api_spec.md) |
| ユーザー向け最短手順 | ルート [README.md](../README.md) と [usage_spec.md](./interfaces/usage_spec.md) |

契約と README が食い違う場合は、core / contracts を正とし、README を追随させます。

## 補足

* 各仕様は「責務・非責務」で境界を定義する。
* 新規仕様は、必ず既存レイヤーに分類する。分類できない場合は、新規レイヤーを足さず、再設計する。
