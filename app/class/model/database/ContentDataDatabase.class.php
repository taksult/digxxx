<?php
class ContentDataDatabase extends Database{
    protected static $tableName = 'content_data';
    protected static $columns = []; //カラム名 コンストラクタ(基底クラスに記述)で取得する
                                    //$columns['カラム名'] = ['カラムの型']

    protected static $selectCols;
    private static $userUpdatable = ['spell' => null, 'yomigana' => null,'content_comment' => null, 
                            'genre' => null, 'category' => null, 'rlsdate' => null, 'moddate' => null
                            ];

    //コンテンツを新規登録
    public static function createContent($contentData){
        $Data = [
            'set' => ['content_name' => null, 'spell' => null, 'content_comment' => null,
            'content_hash' => null, 'regdate' => mydate(), 'moddate' => mydate()]
        ];
        if(isset($contentData['content_name']) && $contentData['content_name'] != null && 
            empty(self::selectByName($contentData['content_name'])) && isset($contentData['spell'])){
            $Data['set'] = array_overwrite($Data['set'] ,$contentData); //とりあえず渡された全データで上書き
            
            //コンテンツ名処理
            $request_name = preg_replace('/.*content\//','',$contentData['content_name']);   
            echo $request_name;
            $request_name = preg_replace('/\+/','＋',$request_name);
            $request_name = str_replace('　',' ',urldecode($request_name));
            $request_name = preg_replace('/＋/','+',$request_name);
            $request_name = preg_replace('/^\s*(.*)\s*$/',"$1",$request_name);
            $request_name = preg_replace('/\s{2,}/',' ', $request_name);

            $registration_name = strtolower($request_name);

            $Data['set']['content_name'] = $registration_name;
            $Data['set']['content_hash'] = md5($registration_name);

            //スペル処理
            $spell = preg_replace('/\+/','＋',$contentData['spell']);
            $spell = urldecode($spell);
            $spell =  preg_replace('/＋/','+',$spell);
            $Data['set']['spell'] = $spell;

            //記事ファイル関連処理
            $file_name  = preg_replace('/[\/\\\\<>\"\|.:*?\s]/','_',$registration_name);    //一部の記号をアンダースコアに
            //ファイル名の頭から3文字以内をディレクトリ名に
            if(strlen($file_name) >= 2){
                $article_dir = PATH_CONTENT_ARTICLES . mb_substr($file_name,0,2);
                $Data['set']['content_comment'] =  mb_substr($file_name,0,2) . DS;
            }
            else{
                $article_dir = PATH_CONTENT_ARTICLES . $file_name;
                $Data['set']['content_comment'] = $file_name . DS;
            }
            //ディレクトリがなければ作成
            if(!is_dir($article_dir)){
                mkdir($article_dir);
                //mkdir(mb_convert_encoding($article_dir, 'SJIS', 'auto'));
            }
            //記事ファイル作成
            $default = "まだ解説記事のないコンテンツです\r\nよければ概要を作成してください";
            $i = 2;
            while(is_readable($article_dir . DS .$file_name . '.html')){
                $file_name = $file_name . '_' . (string)$i;
                $i++;
            }
            unset($i);
            //file_put_contents(mb_convert_encoding($article_dir . DS . $file_name . '.html','SJIS_win','auto'), $default);
            file_put_contents($article_dir . DS . $file_name . '.html', $default);
            $Data['set']['content_comment'] = $Data['set']['content_comment'] . $file_name . '.html';
        
            //データベース登録
            if(parent::insert($Data)){
                file_put_contents($article_dir . DS . $file_name . '.log', mydate(). $_SESSION['user_id'] . '　コンテンツ作成　', LOCK_EX);
                return true;
            }
            else{
                return false;
            }
        }
    }

    public static function updateContent($updateData){
        if(isset($updateData['content_num'])){
            $Data = [
                'set' => [],
                'where' => ['content_num' => ['=',$updateData['content_num']]]
            ];
            $content = new Content($updateData['content_num']);
            if($content->is_exist()){
                $old = array_overwrite(self::$userUpdatable,self::selectByNum($updateData['content_num']));
                $Data['set'] = array_overwrite($old,$updateData);
                return parent::update($Data);
            }
            else{
                return false;
            }
        }
        else{
            return false;
        }
    }

    public static function selectByNum($content_num = null){
        $Data = [
                'where' => [ 'content_num' =>['=', $content_num]
                ]
        ];

        $result = parent::select($Data);
        if(!empty($result) && count($result) == 1){
            return $result[0];
        }
        else{
            return $result;
        }
    }


    public static function selectByName($content_name = null){
        $Data = [
                'where' => [ 'content_name' =>['=', $content_name]
                ]
        ];

        $result = parent::select($Data);
        if(!empty($result) && count($result) == 1){
            return $result[0];
        }
        else{
            return $result;
        }
    }

    public static function searchByKeys($keys = '',$offset = 0, $limit = 20){
        $keys = str_replace("　"," ",$keys);
        preg_match('/^\s*$/',$keys,$none);
        if(empty($none)){
            $keys = explode(" ",$keys);
            if(is_array($keys) && !empty($keys)){
                $values = [];
                $sql = "SELECT content_num FROM content_data WHERE ";
                foreach($keys as $k){
                    $sql = $sql . "CONCAT(IFNULL(content_name,''), ' ', IFNULL(spell,''), ' ', IFNULL(yomigana,'')) collate utf8_unicode_ci LIKE ? AND ";
                    $val = str_replace('\\',"\\\\",$k);
                    $val = str_replace('%',"\%",$val);
                    $val = str_replace('_',"\_",$val);
                    $values[] = '%' . $val . '%';
                }
                $sql = preg_replace('/^(.*)AND\s*$/',"$1",$sql);
                return parent::get($sql,$values);
            }
            else{
                return [];;
            }
        }
        else{
            return [];
        }
    }

    public static function searchByKey($key=''){
        /*
        $Data =[
                'where' => ['content_name' => ['=', $key, 'OR'], $spell => ['=',$key]],
            ];
         */
        preg_match('/^\s*$/',$key,$none);
        if(empty($none)){
            $sql = "SELECT content_num,content_name,spell,yomigana,genre,category,tags,rlsdate,moddate FROM content_data WHERE content_name = ?
                OR spell = ?";
            $values = [$key,$key];

            $result = parent::get($sql,$values);
    
            if(!empty($result) && count($result) == 1){
                return $result[0];
            }
            else{
                return $result;
            }
        }
        return -1;
    }

    public static function extractGenres(){
        $sql = "SELECT DISTINCT genre FROM content_data";
        return parent::get($sql);
    }
}


?>
