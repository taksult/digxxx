root/
  ├ api/
  ├ class/
  ｜  ├ controller/
  ｜  ├ model/
  ｜  ｜  └ database/
  ｜  └ view/
  ｜  
  ├ file/
  ｜  └ img/
  ｜
  ├ resource/
  ｜
  ├ SQL/
  ｜
  ├ definition.php
  ├ library.php
  └ index.php

=====================================================================================
Dispatcherクラス
  URLから各コントローラーを呼び出して実行する
  非ログイン時はどんなURLでもトップにリダイレクト
  ログイン時にトップに飛ぼうと場合にはホームにリダイレクト

=====================================================================================
■モデル
Databaseクラス
  ・共通(継承)メソッド
    initDB() : PDOでDBに接続する。各スクリプト開始時に一度だけ実行。
    init() : 子クラス(テーブル)ごとの初期化。 DBからカラム名を取得
    
    set($sql,$params) : データ登録系(INSERT,UPDATE,DELETE)の実行用
                        正常終了でtrue,エラー時はメッセージを表示してfalseを返す

    get($sql,$params) : データ取得系(実質SELECTのみ)の実行用
                        正常終了で取得レコードの配列を,エラー時はメッセージを表示してfalse
    
    insert($Data),select($Data),update($Data),delete($Data)
    : 以下のフォーマットで$Dataを渡し、バリデーションとSQL生成をして
      上記のset(),get()に渡す
      フォーマット
      
    $Data = [
    'set'   => ['col1' => $val1, 'col2' => $val2],
    'where' => ['col3' => ['=',$val3,'AND'], 'col4' => ['>=',$val4,'OR'],
                'col5' => ['IN', [$v1,$v2,$v3,...] ]  ]
    ];

  子クラス
    UserDataDatabase
    PostDatadatabase
    FollowListDatabase

    子クラスのメソッドでは基本的に他のモデルorコントローラから各種データを受け取り
    上記のフォーマットに整形して各種共通メソッド(insert(),update,select(),delete())に投げる
    select()で、プライマリキーorユニークキーで取得する場合はレコードが一つなので
    戻り値をレコードの配列ではなく、単体のレコードに変換して返すことにする

--------------------------------------------------------------------------------

Valuesクラス
  グローバル変数の管理用クラス。プロパティ$valuesを持ちそれに対する操作諸々
  ・共通(継承)メソッド
    setValues() : 各子クラスで実装。グローバル変数を$valuesに代入
                  コンストラクタから呼び出す
    updateValues() : setValues()を実行

    get($key=null) : 指定のキーの値を返す、キーが存在しなければfalseを返す
                     キー指定がない場合は$valuesを返す

    子クラス
      GetValues  (名前がややこしくてキレそう)
      PostValues (これもややこしい)
      SessionValues

--------------------------------------------------------------------------------
Requestクラス
  PostValuesとGetValuesをまとめてインスタンスとして持つ SessionValuesも入れてたけど外した
  ・メソッド
  update() : それぞれのupdateValues()を実行
  get()
  getPostValues($key = null)
  getGetValues($key = null)
  
  Values系は作ったはいいもののめちゃくちゃ非効率な感じがするし名前が頭悪そうでつらい


=====================================================================================
■コントローラー
Controllerクラス
・共通(継承)メソッド
    run() : ページ実行。ビュー操作、モデルへのデータ受け渡し、セッション変数管理等

子クラス
    TopController
    HomeController
    LoginController
    UserController


=====================================================================================
■ビュー
Viewクラス
  テンプレートエンジン。 テンプレート読み込み、タグへの値アサイン、ページ出力を行う
  ・メソッド
    setTemplate($path,$isstring = false) : $pathのテンプレートファイル読み込み
                                           $isstringがtrueなら$pathが持つ文字列事態を
                                           テンプレートとして処理する

    assign($val,$key) : $keyで指定したタグに割り当て 
      assign('takewo', 'follow_user');

    assignAll($values)  連想配列を投げてキーと一致するタグに割り当て
      $vals = array('user' => 'takewo', 'user_comment' => 'hogehgoe', 'date' => mydate());
      assignAll($vals);

    assignLoopElements($values,$loop) : 指定したループ$loopに"1要素の連想配列"の
                                        "配列"を投げてループ単位で割り当て
        $e = array();
        $e[] = array('user' => 'takewo', 'user_comment' => 'hogehgoe');
        $e[] = array('user' => 'yawata', 'user_comment' => 'fugafuga');
        $e[] = array('user' => 'student', 'user_comment' => 'foobar');

        assignLoopElements($e);

    display() : タグとアサインされた値を置換(実際にはrender()が処理する)して
                ページ出力する。未アサインのタグは消去して表示される

テンプレート
  ・タグフォーマット
    [::tagname]
        → 単一の値をアサイン可

    [:!loop loopname]
        [::tag1]  [::tag2]
        [::tag3]
    [:!end]
      → ループ要素の数だけ繰り返し

