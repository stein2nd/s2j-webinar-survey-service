# S2J Webinar Survey Service - サービス仕様

確定した統合の見取り図です。記録日は2026-10-06、確定移行は2026-10-07です。

実装向けに分割した仕様の起点は [specs.md](./specs.md) です。構成は [S2J Similarity Service の docs/](https://github.com/stein2nd/s2j-similarity-service/tree/main/docs) に倣い、本ライブラリに必要な層だけを置いています。本書は統合の見取り図です。検査・助言・下書き・文書の **規則の正本は常に `core/`** です。本書の表は要約であり、食い違う場合は `core/` を直し、本書を追随させます。契約は `contracts/`、公開面は `interfaces/` です。次回の大きな改訂案は `docs_mod/` で起草します。

## 概要

本ドキュメントは、S2J Webinar Survey Service の初期設計における、サービス全体の統合の見取り図を定義します。

呼び出す WordPress プラグインは [S2J Webinar Survey](https://github.com/stein2nd/s2j-webinar-survey) です。プラグイン仕様は [docs/specs.md](https://github.com/stein2nd/s2j-webinar-survey/blob/main/docs/specs.md) です。

本ライブラリは **WordPress 非依存** です。WP フック、設定画面、HTTP の実行、Zoom への送信、モデルの呼び出しは扱いません。運営者が書いた設問を検査し、回答の負担と、次回の企画に使う目的について、助言のコードを返します。下書きを頼む場合は、依頼文の組み立てと、返ってきた文の分解までを持ちます。採用前の文は文書に書きません。

Zoom にアンケートとして付けるリクエストは、[S2J Webinar Service](https://github.com/stein2nd/s2j-webinar-service) が後で持ちます。本ライブラリはその材料を組み立てません。

## 背景

勤務先では、Zoom Webinar を何度か実施しています。案内のあとにアンケートを置いていますが、回答率がとても低い状態です。

両立したいのは、下記の2つです。

* 参加者が、応えたくなるアンケートである。順番、総数、文章が、回答を躊躇させるものにしない。
* 運営者が、回答を次回以降のウェビナー企画に使える。

設問の良し悪しは、ウェビナー作成リクエストの組立とは別の仕事です。S2J Webinar の中には置きません。

## 目的

運営者が書いたアンケート設問について、下記を返すことです。

* 文書として足りているか。足りなければ `draft` と、不足のコード。
* 回答の負担と、企画への使い道について、保存を拒まない助言のコード。
* 呼び出し側が保存し、あとから S2J Webinar が読める、設問一式の文書。
* 下書きを頼む依頼文と、返ってきた文を設問または選択肢に分けた候補。候補は、人が採用する前の文書には入らない。

初版の到達点は、検査と助言、および下書きの依頼文の組立と返却文の分解です。Zoom の画面に出ているアンケートの複製や、回答の取り込みはしません。

## 非目標 (初版)

* モデルを呼ぶこと。コネクタ、API キー、HTTP はプラグインが持つ。
* 下書きを、人が採用する前に文書に書くこと。
* 企画上の目的を下書きに書くこと。目的は運営者が書く。
* Zoom への作成、更新、削除。アンケートの添付リクエスト。
* 回答、回答率、滞在、投票の取り込み。
* 回答から次回のテーマを自動で提案すること。企画に活かすのは、各設問に付けた目的を人が読むところまで。
* 過去のアンケート設問の流用、設問の台帳。
* セッション中の投票、登録フォームの質問、セッション中の Q&A。
* WordPress、GatherPress、Zoom の型を知ること。
* WordPress.org への掲載。

## Composer ライブラリの理由

見た目は WordPress のイベント編集画面ですが、層は下記に分かれます。

| 層 | 中身 | 置き場 |
| --- | --- | --- |
| 計算 | 文書の検査、助言コード、下書きの依頼文、返却文の分解 | **本ライブラリ** |
| 副作用 | イベントへの保存、助言文の表示、コネクタ経由のモデル呼び出し、のちの Zoom 添付の HTTP | **S2J Webinar Survey** と、添付は **S2J Webinar** |

プラグイン一本に判断を置くと、設問の組み合わせをユニットテストするたびに WordPress が必要になります。

## 本ライブラリの責務

入力は、設問の文書と、設問総数の上限です。出力は、同じ文書に状態を足したものと、助言コードの列と、不足コードの列です。助言の表示文はプラグインが持ちます。下書きの依頼文は、本ライブラリが組み立てます。

| 責務 | 内容 |
| --- | --- |
| 検査 | 内部名、設問文、答え方ごとの欄。足りなければ状態は `draft` |
| 助言 | 総数、順番、回答の躊躇、企画上の目的。状態は変えない |
| 文書 | 下記の形にそろえて返す。欠落のデフォルト値埋めと、答え方に使わない既知キーの除去を含む。呼び出した順を保つ |
| 下書き | 依頼文と `requested_count` を返し、返却文を context 付きで候補に分ける。文書には書かない |

## 文書

```text
internal_name              空は不足。回答者には出さない
questions                  順序あり。0件は不足
  prompt                   回答者に見せる文。空は不足
  answer_kind              single | multiple | short | long | rating
  required                 true | false
  identifies_respondent    true | false。運営者が付ける
  purpose                  次回の企画で何を決めるか。回答者には出さない
  choices                  single / multiple の場合、空でない文が2つ以上。それ以外では空
  score_min                rating の場合。整数。目安は 0。evaluate では埋め込まない
  score_max                rating の場合。整数。目安は 10。evaluate では埋め込まない
  label_low                rating の場合。低いスコアのラベル。空可
  label_high               rating の場合。高いスコアのラベル。空可
status                     draft | ready
```

答え方は5つです。Zoom の画面の呼び名との対応は、写像の表に任せます。本ライブラリは Zoom の型名を持ちません。

| answer_kind | 意味 | パネルの呼び名 (目安) |
| --- | --- | --- |
| `single` | 単一選択 | 単一選択 |
| `multiple` | 複数選択 | 複数選択 |
| `short` | 短い自由記述 | 短い回答 |
| `long` | 長い自由記述 | 長い回答 |
| `rating` | 数値の段階評価 | レーティング・スケール |

選択式は `single` と `multiple` です。段階評価は `rating` であり、選択肢の列にはしません。短い回答と長い回答の文字数は、文書には持ちません。添付時に Zoom のデフォルトに任せます。

`internal_name` は、Zoom の一覧にだけ出る名前です。回答者の画面には出しません。本ライブラリはそれを送りません。

`purpose` は、運営者のためです。回答者には出しません。空でも `ready` にはできます。その設問には助言 `purpose_missing` を付けます。

状態は、下記のとおりです。

| status | 意味 |
| --- | --- |
| `draft` | 不足がある。S2J Webinar はアンケートとして読まない |
| `ready` | 不足がない。助言が残っていても `ready` |

## 検査と助言

正本は [core/validation_spec.md](./core/validation_spec.md) と [core/advice_spec.md](./core/advice_spec.md) です。下記は要約です。

不足は、文書を `ready` にしません。

| コード | 条件 (要約) |
| --- | --- |
| `internal_name_empty` | 内部名が空 |
| `questions_empty` | 設問が0件 |
| `prompt_empty` | 設問文が空 |
| `answer_kind_invalid` | `answer_kind` が上記5つのどれでもない |
| `choices_missing` | `single` または `multiple` で、空でない選択肢が2つ未満 |
| `choices_unexpected` | `short` / `long` / `rating` なのに、空でない選択肢がある |
| `rating_bounds_invalid` | `rating` で境界が不正 (`evaluate` は0/10を埋め込まない) |

助言は、保存を拒みません。`ready` のままでも返します。

| コード | 条件 (要約) |
| --- | --- |
| `too_many` | 設問数が `max_questions` を超える |
| `open_before_closed` | `short` または `long` より後ろに、`single` / `multiple` / `rating` がある |
| `required_open` | `short` または `long` が必須である |
| `identity_not_last` | `identifies_respondent` が真の設問が、最後ではない |
| `purpose_missing` | `purpose` が空 |

順番の規則は、答えやすい選択と段階評価 (`single` / `multiple` / `rating`) を先にし、自由記述 (`short` / `long`) を後ろにまとめる、です。回答者を特定する問いは最後に置きます。必須の自由記述と、特定の問いは、回答を躊躇させやすいものとして助言します。

上限の本数は、本ライブラリに固定しません。プラグインが渡します。サイト設定の値であり、未設定の場合の初期値は6です。6も設定の下限・上限も、ライブラリには埋めません。

文章の誘導と、1つの設問に問いが2つあること (二重の問い) は、初版では判定しません。保存のたびに自動では見ません。誤検知のない機械規則がいまないためです。運営者への周知は、プラグインのパネル冒頭の「大事な指針」が持ちます。あとで足すなら、保存を拒まない助言か、ボタンで頼む一文の見直しに限ります。`ready` は変えません。不足コードにはしません。

## 下書き

下書きは、人が採用する前の候補です。本ライブラリはモデルを呼びません。プラグインが、ボタンを押した際に、組み立てた依頼文をサイトのコネクタに1回送り、返ってきた文を本ライブラリが候補に分けます。

依頼は3種類です。どれも1回の操作で1本の依頼文です。

| 種類 | 件数 | 中身 |
| --- | --- | --- |
| アンケート | 上限から、すでに文書にある設問数を引いた数。文書が空なら上限そのもの | 各件は候補の形 (Question)。企画上の目的は空 |
| 設問文 | いま開いている1件 | 設問文が一文 |
| 選択肢 | いま開いている `single` または `multiple` の1件 | 短いラベルを少数 |

アンケートの依頼では、`single` / `multiple` / `rating` を先にし、自由記述 (`short` または `long`) は最後に多くて1件、と依頼文に書きます。回答者を特定する問いと、必須の自由記述は依頼しません。候補の `required` と `identifies_respondent` は偽、`purpose` は空です。

候補の形は、文書の Question と同じ Zoom 非依存の形です。`rating` の目安0/10埋めは下書き分解だけです (`evaluate` では埋めません)。`build_draft_prompt` は `{ prompt_text, requested_count }` を返す (`requested_count` が0になりうるのは `survey` のみ。`prompt` / `choices` は常に1か例外)。`parse_draft_response` はその件数と文脈で切り詰め・引き継ぎします。kind `prompt` の引き継ぎはライブラリ内です。`prompt` / `choices` の `focus_index` は必須の int です。詳細は [core/draft_spec.md](./core/draft_spec.md) です。

渡してよいのは、イベントの題名、各設問の答え方、運営者が書いた企画上の目的、すでに文書にある設問文です。登壇者のメールと申込者の情報は渡しません。

返ってきた件数が `requested_count` より多い場合は、その件数だけ残します。少ない場合は、分けられた件数だけ返します。分けられない場合は、候補は空です。プラグインは文書を変えません。

採用された文は、あらためて検査と助言の入力になります。候補のままでは `ready` になりません。

## プラグインの責務 (境界。詳細はプラグイン仕様)

プラグイン仕様の詳細は [S2J Webinar Survey の docs/specs.md](https://github.com/stein2nd/s2j-webinar-survey/blob/main/docs/specs.md) です。ここには境界だけを置きます。

| 責務 | 内容 |
| --- | --- |
| 編集 | GatherPress のイベント編集画面。1イベントにつきアンケートは1つ |
| 設定 | 設問総数の上限はサイトに1つ。`manage_options` だけが変える。イベント編集には出さない |
| 保存 | 入力した文書をイベントに保存する。助言コードは保存の正本にしない |
| 表示 | 不足と助言のコードから適切なメッセージ文を表示する (国際化関数を経由)。パネル冒頭の「大事な指針」も表示する |
| 下書き | ボタンを押した場合だけ、依頼文をサイトのコネクタに1回送る。候補は、人が採用するまで文書に書かない |
| 受け渡し | `ready` の文書を、S2J Webinar が読める形で置く。Zoom には送らない |

S2J Webinar が無効でも、編集と保存はできます。添付は S2J Webinar の仕事であり、初版の本ライブラリの範囲外です。

## 設計方針

本ライブラリは [kis-wordpress エコシステム仕様](https://github.com/stein2nd/kis-wordpress/blob/main/docs_mod/specs.md) と同じく、FOP + Clean Coding を基本とします。Clean Architecture の定型分割は採用しません。

データの中身は、純粋関数と不変レコードに閉じます。

| 借用する原則 | 本ライブラリでの意味 |
| --- | --- |
| 依存の向きは外 → 内 | 純関数は WordPress / GatherPress / HTTP / Zoom を知らない |
| 内側はビジネスルール | 設問文書の検査、助言コード、下書きの依頼文と候補への分解 |
| 外側は詳細 | プラグインが画面、保存、表示文、コネクタ経由の呼び出しを持つ |

パッケージ名は `s2j/webinar-survey-service` とします。PHP の名前空間は `S2J\WebinarSurveyService\` とします。ライセンスは GPL-3.0-or-later とします。プラグインも同じです。

## 関連リポジトリ

| 名称 | 種別 | 役割 |
| --- | --- | --- |
| **本ライブラリ** | Composer | 設問文書の検査、助言コード、下書きの依頼文と分解 |
| [S2J Webinar Survey](https://github.com/stein2nd/s2j-webinar-survey) | WP プラグイン | 編集、保存、助言の表示、コネクタ経由の下書き |
| [S2J Webinar](https://github.com/stein2nd/s2j-webinar) | WP プラグイン | `ready` の文書を、作成直後にアンケートとして付ける。設問の文面は持たない |
| [s2j-webinar-service](https://github.com/stein2nd/s2j-webinar-service) | Composer | 添付リクエストの組立。設問の規則は持たない |
| [stein2nd/gatherpress](https://github.com/stein2nd/gatherpress) | WP プラグイン (フォーク) | イベントの編集画面。S2J のコードは置かない |
| [kis-wordpress](https://github.com/stein2nd/kis-wordpress) | モノレポ | サイト専用プラグイン群。本機能は kis-core に抱え込まない |

KIS のサイトは、このプラグインのユーザーの一つです。

## 実装順

1. 仕様を `docs/` に確定する (完了)。
2. 本 repo でスケルトンと純関数の初版 (PHPUnit、WordPress なし、HTTP なし)。検査、助言、下書きの依頼文と分解を含む。
3. プラグインが Composer で require し、イベント編集画面の保存と助言表示、ボタンによる下書きをつなぐ。
4. S2J Webinar Service が `ready` の文書を `PATCH /webinars/{webinarId}/survey` の材料に写す。写像は本ライブラリに入れない。

## Zoom への写像 (本ライブラリの外)

写像は [S2J Webinar Service](https://github.com/stein2nd/s2j-webinar-service) だけが持ちます。本ライブラリは Zoom のフィールドを持ちません。投票 (poll) と登録の質問は使いません。回答レポート (`GET /report/webinars/{webinarId}/survey`) は設問の作成・更新ではありません。

初版5種の `type` 文字列は、`webinarSurveyUpdate` の `custom_survey.questions[].type` で確定しています。キー名の詳細は webinar-service の写像表です。

| 文書 | Zoom (`custom_survey.questions[]`) |
| --- | --- |
| `prompt` | `name` |
| `required` | `answer_required` |
| `single` + `choices` | `type` = `single`、`answers` |
| `multiple` + `choices` | `type` = `multiple`、`answers` |
| `short` | `type` = `short_answer`。文字数は API のデフォルト |
| `long` | `type` = `long_answer`。文字数は API のデフォルト |
| `rating` + `score_*` / `label_*` | `type` = `rating_scale`、`rating_min_value` など |
| `internal_name` | 調査タイトル／内部名まわり。設問の `type` ではない |

enum 全体 (初版では後ろ3つを送らない) は、`single` / `multiple` / `short_answer` / `long_answer` / `rating_scale` / `matching` / `rank_order` / `fill_in_the_blank` です。

アンケート見出しは、未設定ならウェビナータイトルです。説明は初版では空です。「ドロップダウンとして表示」「重み」は送りません。

設問数の上限は、プラグインのサイト設定 (1以上15以下、未設定時は6) を正とします。Zoom 側の上限が判明したら、それを超える場合は、添付でとどめるか切り詰めます。画面だけでは上限を決めません。

初版以降で対応するか否かを検討するのは、下記だけです。文書にも検査にも、初版では入れません。

* 各設問への画像のアップロード
* 単一選択でのスキップロジック
* マッチング (`matching`)
* ランク順 (`rank_order`)
* 空欄に記入する (`fill_in_the_blank`)

過去のアンケート設問の流用は、非目標のままです。

## 採用した方針

確定時に採用した方針です。

* 製品のコアは、参加者が応えたくなる設問と、次回の企画に使える設問を、同じ文書で両立させることである。
* 初版は、運営者が書いた設問の検査と助言に、ボタンで頼む下書きを足す。下書きは人が採用するまで文書に入らない。企画上の目的は運営者が書く。
* 計算は本ライブラリ、編集と保存と表示文は S2J Webinar Survey、Zoom への添付は S2J Webinar である。
* 助言は保存を拒まない。不足がある場合だけ `draft` とし、S2J Webinar は `ready` だけを読む。
* 答え方は `single` / `multiple` / `short` / `long` / `rating` の5つ。選択と段階評価を先、自由記述を後にまとめる。回答者を特定する問いは最後に置く。必須の自由記述は、躊躇の助言にする。
* 各設問の企画上の目的が空なら、助言にする。目的は回答者に出さない。
* 設問総数の上限は引数である。プラグインがサイト設定の値を渡す。未設定の初期値は6。アンケートの下書きは、その上限から既存の設問数を引いた件数を、1本の依頼で返す。候補は文書の Question 形 (Zoom 非依存) とする。
* 開いている1件の設問文、またはその選択肢も、それぞれ1本の依頼で下書きできる。件数は `build_draft_prompt` の戻りを正とする。`parse` は context で引き継ぐ。保存時と画面表示と cron では頼まない。
* ライブラリには上限の初期値も、設定の下限・上限も埋め込まない。検証と助言の規則だけを持つ。
* 誘導と二重の問いは、初版では判定しない。あとで足すなら、保存を拒まない助言か、ボタンで頼む見直しに限る。周知はプラグインのパネル冒頭の指針である。
* 内部名は回答者に出さない。Zoom の一覧用であり、本ライブラリは送信しない。
* Zoom の型名と添付リクエストは本ライブラリに入れない。写像は S2J Webinar Service が持つ。
* 回答率の数値は持たない。
* ライセンスは、プラグインとライブラリの両方で GPL-3.0-or-later。
* パッケージ名は `s2j/webinar-survey-service`。プラグインのスラッグは `s2j-webinar-survey`。

## 改訂履歴

| 日付 | 内容 |
| --- | --- |
| 2026-10-06 | 初版ドラフト。設問文書の検査と助言、文章は生成しない、Zoom への添付は S2J Webinar、と記録 |
| 2026-10-06 | 下書きは、依頼文の組み立てと返却文の分解までを本ライブラリが持つ。モデルはプラグインがボタンで1回呼ぶ。人が採用するまで文書に入らない、と記録 |
| 2026-10-07 | 設問総数の上限はプラグインのサイト設定が渡す。未設定の初期値は6。ライブラリには埋め込まない、と記録 |
| 2026-10-07 | 誘導と二重の問いは初版では判定しない。あとで足すなら保存を拒まない助言かボタンの見直しに限る。周知はプラグインの指針、と記録 |
| 2026-10-07 | 答え方を `single` / `multiple` / `short` / `long` / `rating` の5つにする。写像は webinar-service。画像・スキップ・マッチング・ランク・空欄記入は初版以降の検討、と記録 |
| 2026-10-07 | 初版5種の Zoom `type` を確定 (`single` / `multiple` / `short_answer` / `long_answer` / `rating_scale`)。本ライブラリは Zoom フィールドを持たない、と記録 |
| 2026-10-07 | Similarity の docs 構成に倣い、実装向け仕様を `docs_mod/specs.md` 起点で分割した、と記録 |
| 2026-10-07 | 初版到達点に下書きの組立・分解を含める。`ready` は S2J Webinar が読む。`max_questions` が1未満は例外、と記録 |
| 2026-10-07 | 空でない選択肢がある場合、`choices_unexpected`。公開関数名は snake_case。下書きの `max_questions` 未満も例外、と記録 |
| 2026-10-07 | 下書き候補の形を文書の Question にそろえる。`rating` / `long` / `short` の欄と Zoom 非依存を [core/draft_spec.md](./core/draft_spec.md) に記録 |
| 2026-10-07 | `score_*` は evaluate で埋めず下書き分解だけ目安埋める。`prompt` は focus から引き継ぐ。規則正本は core/。公開面は関数3つ、と記録 |
| 2026-10-07 | `build_draft_prompt` は件数付き戻り。`parse` は context で引き継ぎ。正規化はデフォルト値埋めと不要キー除去。focus 不整合は build 時例外、と記録 |
| 2026-10-07 | `requested_count` 0は `survey` のみ。`focus_index` は kind 別に必須を明確化。bool は coerce、用語はデフォルト値に統一、と記録 |
| 2026-10-07 | 契約表を入力/正規化後で分離。bool coerce を明示リストにし `(bool)` キャストを使わない、と記録 |
| 2026-10-07 | 確定仕様として `docs/` に移行。改訂案は `docs_mod/` で起草する、と記録 |
| 2026-10-09 | プラグイン仕様リンクを docs/ に更新。usage の評価タイミング注記、survey の document 欠落は既存0件、と記録 |
