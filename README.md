cebe/markdownを使用するためのエクステンションクラス
=================================

概要
--------------------------------------------------
複数フォーマットに対応した、MarkdownParser、cebe/markdownを利用するためのExtensionクラスです。

cebe/markdownの概要は以下を参照して下さい。
[http://markdown.cebe.cc/](http://markdown.cebe.cc/)

パッケージ管理
--------------------------------------------------
EnviCebeMarkdownExtensionパッケージをEnviMvcにバンドルさせるには、

`envi install-bundle new https://raw.githubusercontent.com/EnviMVC/EnviCebeMarkdownExtension/master/bundle.yml`

コマンドを実行します。

インストール・設定
--------------------------------------------------

パッケージがバンドルされていれば、

`envi install-extension {app_key} {DI設定ファイル} cebe_markdown`

コマンドでインストール出来ます。

