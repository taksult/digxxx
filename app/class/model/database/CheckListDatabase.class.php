<?php
class CheckListDatabase extends Database{

    protected static $tableName = 'user_checklist';
    protected static $columns = []; //カラム名 コンストラクタ(基底クラスに記述)で取得する
                                        //$columns['カラム名'] = ['カラムの型']
    protected static $selectCols = null;
    private static $userUpdatable = "user_comment,user_ref,user_image,tags,favorite,origin,hidden";

    //リスト要素を新規登録
    public static function createElement($createData){
        $Data = [
            'set' => ['content_num' => null, 'content_name' => '', 'user_num' => null,
            'genre' => null, 'user_comment' => null, 'user_ref' => null, 'user_image' => 'noimage.png', 'tags' => ',,',
            'regdate' => mydate(), 'favorite' => false,'hidden' => false, 'origin' => false]
        ];

        if(!isset($createData)){
            return false;
        }
        else{
            //受け取ったデータで上書き
            $Data['set'] = array_overwrite($Data['set'],$createData);

            //既にレコードが存在すればtrueを返す
            if(!empty(self::selectElementByUser($Data['set']['user_num'],$Data['set']['content_num']))){
                return true;
            }
            //それ以外ならレコード追加
            else{
                return parent::insert($Data);
            }
        }
    }

    //リスト要素の情報変更(お気に入り追加・解除等)
    public static function updateElement($updateData){
        $Data = [
            'where' => ['content_num' => ['=',$updateData['content_num'],'AND'], 'user_num' =>['=',$updateData['user_num']] ]
        ];

        //渡されたデータにセットされたテーブルのみ、元データと置き換えて更新する
        $old = self::selectElementByUser($updateData['user_num'],$updateData['content_num'],self::$userUpdatable);
        $Data['set'] = $old;
        $Data['set'] = array_overwrite($Data['set'], $updateData);
        $Data['set']['tags'] =  ',,' . $Data['set']['tags'];
        if(mb_substr($Data['set']['tags'],-1) != ','){
            $Data['set']['tags'] =  $Data['set']['tags'] . ',';
        }
        return parent::update($Data);
    }

    //あるユーザのチェックリストを取得
    public static function selectByUser($user_num, $tags = '', $flags = null){
        if(is_int($user_num)){
            $Data = [
                'where' => [ 'user_num' => ['=', $user_num, 'AND' ] ],
                'order' => 'regdate',                        //登録日時でソート
                'option' => 'DESC'
            ];
            if(!empty($flags)){
                $Data['where']['user_num'][2] = 'AND';
                $flagsData = self::setFlagsData($flags);
                $Data['where'] = $Data['where'] + $flagsData['where'];
            }
            $ex_tags = [];
            foreach(explode(',',$tags) as $t){
                $ex_tags[] = ',' . $t . ',';
            //$ex_tags[] = '%' . str_replace('%','\%',$t) . '%';
            }
            $Data['where']['tags'] = ['LIKE',$ex_tags,'AND'];
            return parent::select($Data);
        }
        else{
            //echo 'ユーザ番号はintで渡す';
            return false;
        }
    }

    //あるユーザのリストから要素を取得
    public static function selectElementByUser($user_num,$content_num, $selectCols = null){
        if(is_int($user_num) && is_int($content_num)){
            $Data = [
                'where' => ['user_num' => ['=',$user_num,'AND'], 'content_num' => ['=',$content_num] ]
            ];
            $result = parent::select($Data,$selectCols);
            if(!empty($result)){
                return $result[0];
            }
            return $result;
        }
        else{
            echo '引数はint型で渡す';
            return false;
        }

    }


    public static function countUsers($content_num = null){
        if($content_num == null){
            echo('コンテンツ番号が未設定です');
        }
        $Data = [
                'where' => [ 'content_num' =>['=', $content_num]
                ]
            ];
        $selectCol = 'COUNT(check_num)';
        $result = parent::select($Data,$selectCol);
        if(!empty($result)){
            return $result[0][$selectCol];
        }
        else{
            return false;
        }
    }


    //要素のステータスをDBに投げるデータ書式にして返す
    private static function setFlagsData($flags){
        $ret = ['set' => [], 'where' => []];
        if(isset($flags['favorite']) && is_bool($flags['favorite'])){
            //$ret['set']['favorite'] = $flags['favorite'];
            $ret['where']['favorite'] = ['=',$flags['favorite'],];
        }
        if(isset($flags['origin']) && is_bool($flags['origin'])){
            //$ret['set']['origin'] = $flags['origin'];
            $ret['where']['origin'] = ['=',$flags['origin'],];
        }
        if(isset($flags['active']) && is_bool($flags['active'])){
            //$ret['set']['active'] = $flags['active'];
            $ret['where']['active'] = ['=',$flags['active'],];
        }
        if(isset($flags['hidden']) && is_bool($flags['hidden'])){
            //$ret['set']['hidden'] = $flags['hidden'];
            $ret['where']['hidden'] = ['=',$flags['hidden'],];
        }

        $i = 1;
        //論理演算子を追加
        foreach($ret['where'] as $key => &$val){
            if($i < count($ret['where'])){
                $val[2] = 'AND';
            }
        }
        unset($i);
        unset($val);
        return $ret;
    }
}

?>
