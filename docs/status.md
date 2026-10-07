<!--
目的：「実装状況サマリー」の明文化
-->

# S2J Webinar Survey Service - 実装状況

本ページは、現状の実装状況を機能単位で一覧します (製品全体の「いま」)。
完了したイニシアチブの凍結状況は [archive/README.md](./archive/README.md) です。進行中の証跡三点は `docs_mod/` に置きます。
索引は [specs.md](./specs.md) です。規則・型の Source of Truth は [core/](./core/document_spec.md) と [contracts/](./contracts/data_contract_spec.md) です ([governance/documentation_governance.md](./governance/documentation_governance.md) と同じ)。

最終更新: 2026-10-07

## 仕様書 (参照元)

* [specs.md](./specs.md) — 索引
* [service_spec.md](./service_spec.md) — 統合見取り図 (要約。規則の正本ではない)
* [core/](./core/document_spec.md) — 規則 (SoT)
* [contracts/data_contract_spec.md](./contracts/data_contract_spec.md) — DTO (SoT)
* [interfaces/php_api_spec.md](./interfaces/php_api_spec.md) — 公開面

## 機能一覧

| 機能名 | 実装済み/未実装 | 実装％ | 完了条件 |
| --- | --- | --- | --- |
| 仕様分割 (`docs/` Why/What/How) | 仕様確定 | — | `docs/` に配置済み。改訂案は `docs_mod/` |
| Composer スケルトン (`src/` 公開 API) | 未実装 | 0 | [php_api_spec.md](./interfaces/php_api_spec.md) の3関数と autoload |
| 文書正規化 + status | 未実装 | 0 | [document_spec.md](./core/document_spec.md) |
| 検査 (不足コード一式) | 未実装 | 0 | [validation_spec.md](./core/validation_spec.md) の全コードに unit |
| 助言コード一式 | 未実装 | 0 | [advice_spec.md](./core/advice_spec.md) の全コードに unit |
| 下書き依頼文 | 未実装 | 0 | [draft_spec.md](./core/draft_spec.md) |
| 下書き分解 | 未実装 | 0 | 件数切り詰め・空候補の unit |
| PHPUnit / PHPStan / PHPCS | 設定ファイルのみ想定 | 0 | [testing.md](./testing.md)、[engineering/ci.md](./engineering/ci.md) |
| Packagist 公開 | 未実装 | 0 | [build_and_release.md](./engineering/build_and_release.md) |
| プラグインからの require | 未実装 | 0 | S2J Webinar Survey 側 |

## 補足

* 初版に OpenAPI、REST Adapter、TypeScript SDK は、含めない。
* Zoom 写像は、本 repo の実装対象外である。
