<?php
class Content{

    private $property = [ 'content_num' => null,
                        'content_name' => null,
                        'spell' => null,
                        'yomigana' => null,
                        'content_comment' => null,
                        'official_ref' => null,
                        'content_image' => null,
                        'genre' => null,
                        'category' => null,
                        'rlsdate' => null,
                        'regdate' => null,
                        'moddate' => null,
                        'user_count' => null,
                        'article' => null,
                        'raw_article' => null
                    ];
    private $exist = false;

    function __construct($id = null){
        $this->setContent($id);
    }

    public function setContent($id = null){
        //コンテンツ名or番号が指定されていてかつ該当レコードが存在したらデータをセット
        if($id !== null){
            if(is_int($id)){
                $record = ContentDataDatabase::selectByNum($id);
            }
            else if(is_string($id)){
                $record = ContentDataDatabase::selectByName($id);
            }
            if(!empty($record)){
                $this->property = array_overwrite($this->property,$record);
                $this->exist = true;
                if(is_readable(PATH_CONTENT_ARTICLES . $this->property['content_comment'])){
                    $this->property['raw_article'] = file_get_contents( PATH_CONTENT_ARTICLES . $this->property['content_comment']);
                    $this->property['article'] = self::convertArticle($this->property['raw_article']);
                }
                if($this->property['rlsdate'] != null){
                    $date = new DateTime($this->property['rlsdate']);
                    $this->property['rlsdate'] = $date->format('Y');
                }
                $this->property['user_count'] = $this->countUsers();
            }
            else{
                $this->exist = false;
            }
        }
        else{
            $this->exist = false;
        }
    }

    public function is_exist(){
        return $this->exist;
    }

    public function getData(){
        return $this->property;
    }

    public function getNum(){
        return $this->property['content_num'];
    }

    public function getName(){
        return $this->property['content_name'];
    }

    public function getSpell(){
        return $this->property['spell'];
    }

    public function getComment(){
        return $this->property['content_comment'];
    }

    public function getGenre(){
        return $this->property['genre'];
    }

    public function getCategory(){
        return $this->property['category'];
    }

    public function getArticle(){
        return $this->property['article'];
    }
    public function getRawArticle(){
        return htmlspecialchars($this->property['raw_article'],ENT_QUOTES,'UTF-8');
    }
    public function getUserCount(){
        return $this->property['user_count'];
    }

    public function countUsers(){
        if($this->is_exist()){
            return CheckListDatabase::countUsers($this->getNum());
        }
        else{
            return false;
        }
    }

    public function update($updateData){
        if($this->is_exist()){
            $Data = array_overwrite($this->property,$updateData);
            if($updateData['article'] != ''){
                $file_name  = preg_replace('/[\/\\<>.:*?\s]/','_',$this->property['content_name']);    //一部の記号をアンダースコアに
                //ファイル名の頭から3文字以内がディレクトリ名
                if(strlen($file_name) >= 2){
                    $article_dir = PATH_CONTENT_ARTICLES . mb_substr($file_name,0,2);
                    $Data['set']['content_comment'] =  mb_substr($file_name,0,2) . DS;
                }
                else{
                    $article_dir = PATH_CONTENT_ARTICLES . $file_name;
                    $Data['set']['content_comment'] = $file_name . DS;
                }
                file_put_contents($article_dir . DS . $file_name . '.html', $updateData['article']);
                $logdata =  mydate().' '.$_SESSION['user_id']."\r\n".$updateData['edit_comment']."\r\n\r\n".$updateData['article']."\r\n\r\n";
                file_put_contents($article_dir . DS . $file_name . '.log', $logdata ,FILE_APPEND | LOCK_EX);
            }
            return ContentDataDatabase::updateContent($Data);
        }
    }


    public static function convertArticle($raw){
        $rows = preg_split('/\R/u', htmlspecialchars($raw,ENT_QUOTES,'UTF-8'));
        $ret = '';
        foreach($rows as $r){
            $ret = $ret."\r\n".'<p>'.$r.'</p>';
        }
        $ret = preg_replace('/<p><\/p>/',"<br/>",$ret);
        $ret = preg_replace('/<p>\[:hl\s*(.*?)\]\]<\/p>/','<h2>$1</h2>',$ret);
        $ret = preg_replace('/<p>\[:hm\s*(.*?)\]\]<\/p>/','<h3>$1</h3>',$ret);
        $ret = preg_replace('/<p>\[:hs\s*(.*?)\]\]<\/p>/','<h4>$1</h4>',$ret);
        $ret = preg_replace('/\[:b\s*(.*?)\]\]/','<span style="font-weight:bold">$1</span>',$ret);
        $ret = preg_replace('/\[:i\s*(.*?)\]\]/','<span style="font-style:italic">$1</span>',$ret);
        $ret = preg_replace('/<p>\[\[line\]\]<\/p>/','<hr class="style1">',$ret);
        $ret = preg_replace('/\[\[\s*link\s*(http[s]{0,1}:\/\/[^\s]*?)\s*\]\]/','<a href="/jump/?url=$1" target="_blank">$1</a>',$ret);
        //$ret = preg_replace('/\[\[\s*link\s*(https:\/\/[^\s]*?)\s*\]\]/','<a href="/jump/?url=$1" target="_blank">$1</a>',$ret);
        $ret = preg_replace('/\[\[\s*namelink\s*(http[s]{0,1}:\/\/.*?)\s+(.*?)\]\]/','<a href="/jump/?url=$1" target="_blank">$2</a>',$ret);
        //$ret = preg_replace('/\[\[\s*namelink\s*(https:\/\/.*?)\s+(.*?)\]\]/','<a href="/jump/?url=$1" target="_blank">$2</a>',$ret);
        $ret = preg_replace('/\[=(.*?)\]\]/','<a href="/content/a/$1">$1</a>',$ret);
        return $ret;
    }
}

?>
