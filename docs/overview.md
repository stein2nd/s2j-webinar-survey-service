<!--
目的：「プロジェクトの存在理由、概要、基本情報」の明文化
-->

# S2J Webinar Survey Service - 概要

本ドキュメントは、本プロジェクトの **基本情報および前提理解** を目的とします。
本ライブラリが、どのような位置付けで提供され、何を責務とし、何を扱わないかを明確にします。

## はじめに

本プロジェクトは、Zoom Webinar 終了後のアンケート設問について、運営者が書いた内容を検査し、回答の負担と企画への使い道について助言するための Composer ライブラリです。

両立したいのは、次の2つです。

* 参加者が応えたくなる設問である (順番、総数、躊躇しやすい必須の自由記述など)。
* 運営者が、回答を次回以降のウェビナー企画に使える (各設問の企画上の目的)。

呼び出し側の WordPress プラグインは [S2J Webinar Survey](https://github.com/stein2nd/s2j-webinar-survey) です。Zoom への添付は [S2J Webinar](https://github.com/stein2nd/s2j-webinar) / [S2J Webinar Service](https://github.com/stein2nd/s2j-webinar-service) です。

## 概要

本プロジェクトは、PHP 環境における再利用可能な **Composer パッケージ** として提供されます。

主な特徴は、下記のとおりです。

* 設問文書の検査と、保存を拒まない助言コードを返す。
* 下書き用の依頼文を組み立て、返ってきた文を候補に分ける。
* モデル HTTP、WordPress、Zoom には依存しない。
* パッケージ名は `s2j/webinar-survey-service`、名前空間は `S2J\WebinarSurveyService\`、ライセンスは GPL-3.0-or-later とする。

### 提供機能

* 文書の不足判定と状態 (`draft` / `ready`)
* 助言コードの列
* 下書き依頼文の組立と、返却文の候補への分解

### 利用環境 (想定)

* PHP v8.1以上 (Composer)
* 呼び出し側は、主に WordPress プラグイン (本ライブラリは WP に依存しない)

## Composer パッケージの責務と非対応スコープ

### 責務

* 設問文書が足りているかを判定すること。
* 回答の負担と企画上の目的について、助言コードを返すこと。
* 呼び出し側が保存し、あとから S2J Webinar が読める形に、文書をそろえること。
* 下書きの依頼文を組み立て、返ってきた文を候補に分けること。

### 非対応スコープ (Out of Scope)

* モデルの呼び出し、コネクタ、API キー
* 下書きを、人が採用する前に、文書に書くこと
* Zoom への作成・更新・削除、アンケート添付リクエスト、設問型の写像
* 回答・回答率の取り込み、過去設問の台帳
* WordPress フック、設定画面、イベントメタの読み書き
* 誘導文・二重問いの自動判定 (初版)
* WordPress.org への掲載

## 関連ドキュメント

* 背景とユースケース: [concept.md](./concept.md)
* レイヤ構成: [architecture.md](./architecture.md)
* 統合見取り図: [service_spec.md](./service_spec.md)
