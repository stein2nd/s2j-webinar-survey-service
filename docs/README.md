# S2J Webinar Survey Service - 仕様ディレクトリ

確定仕様の正本はこの `docs/` です。索引は [specs.md](./specs.md) です。

ルート [README.md](../README.md) はプロジェクト概要と最短リンクです。

## 起草用ディレクトリ (`docs_mod/`)

改訂案と、進行中イニシアチブの証跡三点は [../docs_mod/README.md](../docs_mod/README.md) のとおり `docs_mod/` で起草します。

1. 改訂案を `docs_mod/` に置く (必要なら `docs/` と同じレイヤー構成で)
2. レビューし、合意する
3. 合意内容を `docs/` に反映する (同名ファイルのマージ規則は [governance/documentation_governance.md](./governance/documentation_governance.md) の「`docs_mod/` から `docs/` への反映 (マージ)」)
4. 起草ファイルは反映後に片付けてよい (`docs_mod/` ディレクトリ自体は残す)

完了したイニシアチブの凍結は [archive/README.md](./archive/README.md) です。

## 改訂履歴

* 2026-10-11追記: `docs_mod/` に残っていた起草用 README の運用説明を本ファイルに集約。横断ドキュメント監査の確定内容は各仕様の改訂履歴および [archive/mod-docs-cross-alignment/](./archive/mod-docs-cross-alignment/) を参照。
