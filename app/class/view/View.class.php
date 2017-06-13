<?php
//多重ループを実装しようと思い色々おかしな部分ができました(ループを逆順に処理するとか)
//結局多重ループはできないので注意

include_once(PATH_LIBRARY);

//テンプレートエンジン(指定タグへの代入と繰り返し処理のみ)
class View{
    private $template = ''; //テンプレートを文字列で保持
    private $assigned = []; //アサイン済みのタグと変数の組を保持
    private $tags = [];     //タグ一覧を保持
    private $insert = [];   //外部ファイルの挿入場所
    private $nests = [];    //ループ箇所を実際にアサインする子インスタンスを保持

    function __construct($path = null,$isstring = false){
        if($path !== null){
            $this->setTemplate($path,$isstring);
        }
//debug
//print_r($this->tags);
    }


    //テンプレートファイル読み込み($isstring 文字列をそのまま投げるときfalseに指定)
    public function setTemplate($path, $isstring = false){
        if($isstring){
            $this->template = $path;    //渡された文字列をテンプレートに代入
            $this->extractTags();
            return true;
        }

        $subpath = PATH_TEMPLATES . $path; //$pathがファイル名のとき
        //パス(ファイル名)のチェック
        if(is_readable($path)){
            $this->template = file_get_contents($path);
        }
        else if(is_readable($subpath)){
            $this->template = file_get_contents($subpath);
        }
        else{
            self::classError(1,'ファイルが存在しないかパスが正しくありません');
            return false;
        }
        //テンプレートが読み込めたらタグ抽出
        $this->extractTags();
        return true;
    }

    //テンプレートのタグ名抽出(setTemplateから呼び出す)
    private function extractTags(){
        //テンプレートがなければfalseを返す
        if($this->template === null){
            self::classError(1,'テンプレートが設定されていません');
            return false;
        }

        else{
            $tpl = $this->template;
            $tpl_loop_replaced = $tpl;  //タグ抽出用のコピー
            //ループタグをすべて抽出
            $loopnames = array_reverse($this->extractLoopNames($tpl));

            //ループ部分の処理
            if(!empty($loopnames)){
                
                //各ループの内部を抜き出して子インスタンスに投げる
                foreach($loopnames as $i => $loopname){
                    if(!isset($this->tags[$loopname])){
//debug
//echo 'loopname:' . $loopname . '<br/>';
                        preg_match('/(?s)(\[:!loop[\s]+' . $loopname .'\])(.*?)(\[:!end\])/u',$tpl_loop_replaced,$loop);
                        $neststr = preg_replace('/(?s)(\[:!loop[\s]+' . $loopname .'\])(.*?)(\[:!end\])/u',"$2",$loop[0]);
//debug
//echo $neststr . '<br/>';
                        $this->nests[$i] = new View($neststr,true);             //子インスタンス生成
                        $this->tags[$loopname] = $this->nests[$i]->getTags();   //ループ部分のタグ名を取得
                        $this->assigned[$loopname] = array();                   //ループ部分用のアサイン済み配列の初期化

                    //このループをタグ抽出用テンプレートから削除
                        $tpl_loop_replaced = preg_replace('/(?s)(\[:!loop[\s]+'. $loopname .'\])(.*?)(\[:!end\])/u','',$tpl_loop_replaced);
//debug
//echo $tpl_loop_replaced . '<br/><br/><br/>';
                    }
                }
            }
            //ループ以外の処理
            //タグ抽出
            preg_match_all('/(\[::)([A-Za-z0-9_]+)[]]/u',$tpl_loop_replaced,$matches,PREG_PATTERN_ORDER);
            $matches = $matches[0];
            //置換してタグ名のみ取得
            $replaced = preg_replace('/(\[::)([A-Za-z0-9_]+)[]]/u',"$2",$matches);
            foreach($replaced as $key => $val){
                $this->tags[] = $val;
            }
        }
        return true;
    }

    public function getTags(){
        return $this->tags;
    }

    //タグに変数orループの要素をアサイン
    public function assign($value = null, $key = null, $loop = null){
        //ループ指定なし
        if($loop === null){
            $this->assigned[$key] = h($value);
        }
        //ループ指定あり(ループ部分は子インスタンスでのassign時にエスケープする)
        else{
        /*
            foreach($value as $k => $v){
                $value[$k] = h($v);
            }
        */
            $this->assigned[$loop][] = $value;
        }
        return true;
    }


    //配列ですべてアサイン(毎回assign呼び出すからオーバーヘッドでかいかも?)
    public function assignAll($values = null){
        if(is_array($values)){
            //ループ指定なし(全体一括)
            foreach($values as $key => $val){
                //ループの処理
                if(is_array($val)){
                    foreach($val as $element){
                        if(self::assign($element,null,$key)){
                            $this->classError(1,'アサイン中にエラーが発生しました');
                            return false;
                        }
                    }
                }
                //通常のタグ処理
                else{
                    if(!self::assign($val,$key)){
                        $this->classError(1,'アサイン中にエラーが発生しました');
                        return false;
                    }
                }
            }
            return true;
        }
        else{
            self::classError(1,'配列を渡してください');
            return false;
        }
    }

    //ループの要素をアサイン
    public function assignLoopElements($elements = null, $loop = null){
        if(!is_array($elements)){
            self::classError(1,'ループの要素には連想配列を設定してください');
            return false;
        }
        foreach($elements as $e){
            if(!self::assign($e,null,$loop)){
                $this->classError(1,'アサイン中にエラーが発生しました');
                return false;
            }
        }
    }

    //タグとアサインされた値を置換
    private function render(){
//debug
//print_r($this->assigned);
        $tpl = $this->template;
        $tpl_loop_replaced = $tpl;
        $output = $tpl;
        //ループ部分をバッファ(ループ内にある外と同名のタグの置換防止)
        $loopnames = array_reverse($this->extractLoopNames($tpl));
        $loopbuf = array(); //バッファしておいて後で置換
        $i = 0;
        //各ループの処理
        foreach($loopnames as $i => $loopname){
            $loopbuf[$loopname] = '';

            //ループにアサインされた要素分、子インスタンス(ループ分をテンプレートとして持つ)で置換処理
            foreach($this->assigned[$loopname] as $values){
                if(!empty($values)){
                    $this->nests[$i]->assignAll($values);       //ループに割り当てられた変数はここでエスケープされる
                    $loopbuf[$loopname] = $loopbuf[$loopname] . $this->nests[$i]->render(); //$nests[]のrenderを呼び出してループの出力に結合
                                                                                            //(ループがないので下記の通常タグの置換処理になる
                }
            }
        }
        unset($i);
        //通常タグの置換
        foreach($this->assigned as $key => $value){
            if(!is_array($value)){
                $v = preg_replace("/\\\\/","\\\\\\\\",$value);  //バックスラッシュ(\)を二重にする
                //echo 'value:' . $v .  '<br/>';
                $output = preg_replace("/\[::" . $key ."\]/u",$v,$output);  //置換後はバックスラッシュ1つに戻る
                //echo 'preg:' . preg_replace('/hoge/',$v,'hoge') . '<br/>';
            }
        }
        //ループの置換
        foreach($loopbuf as $loopname => &$lbuf){
            $lbuf = preg_replace("/\\\\/","\\\\\\\\",$lbuf);
            $output = preg_replace('/(?s)(\[:!loop[\s]+' . $loopname . '\])(.*?\[:!end\])/u',$lbuf,$output); //1ループずつ置換
        }
        unset($lbuf);
        //残ったタグは空白で置き換え
        $output = preg_replace('/\[::[A-Za-z0-9_]+\]/u','',$output); //通常タグ
        $output = preg_replace('/\[>>[A-Za-z0-9_]+\]/u','',$output); //挿入タグ
        return $output;
    }

    //renderして出力
    public function display(){
        $output = $this->render();
        echo $output;
     }

    public function insertExternalTemplate($expath = null,$tag = null, $isstring = false){

        $extemplate = '';   //外部テンプレート保持用

        if($isstring){
            $extemplate = $expath;    //渡された文字列を挿入テンプレートに代入
            $this->template = preg_replace('/\[>>' . $tag . '\]/u',$extemplate,$this->template); //元のテンプレートの挿入用タグと挿入テンプレートを置換
        }
        else{
            $subpath = PATH_TEMPLATES . $expath; //$pathがファイル名のとき用
            //パス(ファイル名)のチェック
            if(is_readable($expath)){
                $extemplate = file_get_contents($expath);
            }
            else if(is_readable($subpath)){
                $extemplate = file_get_contents($subpath);
            }
            else{
                self::classError(1,'ファイルが存在しないかパスが正しくありません');
                return false;
            }

            $this->template = preg_replace('/\[>>' . $tag . '\]/u',$extemplate,$this->template); //元のテンプレートの挿入用タグと挿入テンプレートを置換
        }
        $this->extractTags();
        return true;
    }

    //テンプレートの末尾に文章付け足し
    public function addTail($str){
        $this->template = $this->template . $str;
    }

 /* !!!!!!developing(多重ループ実装の残骸)
 *      //debug
 *      //print_r($this->assigned);
 *      $tpl = $this->template;
 *      $output = $this->template;
 *      $looptags = array();
 *
 *      //ループタグ取得
 *      preg_match_all('/(?s)(\[:!loop[\s]+)([A-Za-z0-9_]+)(\])/',$tpl,$looptags);
 *      $looptags = array_reverse($looptags[0]);
 *      foreach($looptags as $looptag){
 *         $loopnames[] =  preg_replace('/(?s)(\[:!loop[\s]+)([A-Za-z0-9_]+)(\])/',"$2",$looptag);
 *       }
 *      //debug
 *      //print_r($looptags);
 *      $loopbuf = array(); //バッファしておいて後で置換
 *      $i = 0;
 *      //各ループの処理
 *      //ループ名抽出
 *      foreach($loopnames)
 */



    private function extractLoopNames($tpl){

        preg_match_all('/(?s)(\[:!loop[\s]+)([A-Za-z0-9_]+)(\])/u',$tpl,$looptags);
        //ループタグがある場合の処理
        if(!empty($looptags)){
            $loopnames = array();
            $looptags = $looptags[0];
            //ループ名抽出
            foreach($looptags as $looptag){
                $loopnames[] =  preg_replace('/(?s)(\[:!loop[\s]+)([A-Za-z0-9_]+)(\])/u',"$2",$looptag);
            }
            return $loopnames;
        }
        return false;
    }


    //エラーメッセージ出力
    protected function classError($nest,$msg){
        $caller = debug_backtrace();
        echo __CLASS__ . '::';
        for($i=$nest; $i >= 1; $i--){
            if($i == $nest && $i != 1){
               echo $caller[$i]['function'] . '()=>';
            }
            else{
               echo $caller[$i]['function'] . '():' . $msg . '<br/>';
            }
        }
    }
}
?>
