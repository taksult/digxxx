<?php
class PostAPI extends APIController{
/*  継承
    protected $params;
    protected $request;
    protected $method;
 */

    private static $retColumns = ['user_num' => null, 'content_name' => null, 'post_image_name' => null,
        'post_comment' => null, 'reference_url' => null, 'tags' => null, 'dig' => null, 'nsfw' => null];

    private $me;

    function __construct(){
        parent::__construct();
        $this->me = new User($_SESSION['user_id']);
        if(!$this->me->is_exist()){
            $this->error('user does not exist');
        }
    }

    function post(){
        if($this->params[0] == 'create'){
            if($this->request->chkToken()){  //トークンチェック
                $newpost = new Post();
                $postData = [];
                $postData = $this->request->getPostValues();
                $postData['user_num'] = $this->me->getNum();
                $postData['user_id'] = $this->me->getId();
                if($postData['nsfw'] != ''){
                    $postData['nsfw'] = true;
                }
                else{
                    $postData['nsfw'] = false;
                }
                if($postData['dig'] != ''){
                    $postData['dig'] = true;
                }
                else{
                    $postData['dig'] = false;
                }
                //重複投稿チェック(6時間以内に同じ投稿があればfalseを返す)
                $checkData = [
                            'where' => ['user_num' => ['=', $postData['user_num'], 'AND'], 'content_name' => ['=', $postData['content_name'], 'AND'],
                                        'post_comment' => ['=', $postData['post_comment'], 'AND'], 'regdate' => ['>=',@date('Y-m-d H:i:s',strtotime('-6 hour',time()))]
                                        ]
                            ];

                if(!PostDataDatabase::checkOverlap($checkData)){
                    if(isset($_POST['images'])){
                         //画像処理
                        //print_r($_POST);
                        //print_r($this->request->getPostValues());
                        $postData['post_image_name'] = '';
                        $dir = PATH_ROOT . "file/img/post/";
                        $img = [];
                        $ext = [];
                        for($i=0;$i < 4 && $i < count($_POST['images']); $i++){
                            $img_tmp = base64_decode($postData[$i],true);
                            //ファイルサイズ判定
                            if(strlen($img_tmp) > 10000000){
                                $this->error('ファイルサイズが大きすぎます ファイル番号:'. $i);
                            }

                            //ファイル形式判定
                            $finfo = finfo_open();
                            $mime_type = finfo_buffer($finfo,$img_tmp,FILEINFO_MIME_TYPE);
                            $ext_tmp = array_search($mime_type,['gif' => 'image/gif','jpg' => 'image/jpeg','png' => 'image/png'],true);
                            if(!$ext_tmp){
                                $this->error( '不正なファイル形式です ファイル番号:' .$i);
                            }
                            $img[] = $img_tmp;
                            $ext[] = $ext_tmp;
                        }
                        //画像ファイル保存
                        for($i = 0; $i < count($img); $i++){
                            $filename = makeRandStr();
                            $j = 16;
                            while(is_file($dir . $filename . '.' .$ext[$i])){
                                $filename = makeRandStr($j);
                                $j++;
                            }
                            unset($j);
                            $filename = $filename . '.' . $ext[$i];
                            if(file_put_contents($dir.$filename,$img[$i])){
                                //Exif情報の削除
                                if($ext[$i] === 'jpg'){
                                    $gd = imagecreatefromjpeg($dir.$filename);
                                    $w = imagesx( $gd );
                                    $h = imagesy( $gd );
                                    $gd_out = imagecreatetruecolor( $w, $h );
                                    imagecopyresampled( $gd_out, $gd, 0,0,0,0, $w,$h,$w,$h );
                                    imagejpeg( $gd_out, $dir.$filename);
                                }
                                //サムネイル生成 & 1MB以上の画像をリサイズ
                                $width_origin = 0;
                                $height_origin = 0;
                                list($width_origin,$height_origin) = getimagesize($dir.$filename);
                                $aspect = $width_origin / $height_origin;
                                if($aspect > 1){
                                    $width_thumb = THUMB_SIZE;
                                    $height_thumb = intval(THUMB_SIZE / $aspect); 
                                }
                                else{
                                    $height_thumb = THUMB_SIZE;
                                    $width_thumb = intval(THUMB_SIZE * $aspect); 
                                }
                                $oversize = false;
                                if(strlen($img[$i]) > 1000000){   
                                    $reduc = sqrt(1000000 / strlen($img[$i]));     //画像縮小率
                                    $width_reduc = intval($width_origin * $reduc);
                                    $height_reduc = intval($height_origin * $reduc);
                                    $oversize = true;
                                }
                                switch($ext[$i]){
                                    case 'jpg':
                                        $original = imagecreatefromjpeg($dir.$filename);
                                        $thumb = ImageCreateTrueColor($width_thumb, $height_thumb);
                                        if($oversize){
                                            $reduced = ImageCreateTrueColor($width_reduc, $height_reduc);
                                        }
                                        break;
                                    case 'png':
                                        $original = imagecreatefrompng($dir.$filename);
                                        $thumb = ImageCreateTrueColor($width_thumb, $height_thumb);
                                        imagealphablending($thumb, false);
                                        imagesavealpha($thumb, true); 
                                        if($oversize){
                                            $reduced = ImageCreateTrueColor($width_reduc, $height_reduc); 
                                            imagealphablending($reduced, false);
                                            imagesavealpha($reduced, true); 
                                        }
                                        break;
                                    case 'gif':
                                        $original = imagecreatefromgif($dir.$filename);
                                        $thumb = ImageCreateTrueColor($width_thumb, $height_thumb);
                                        $alpha = imagecolortransparent($original);
                                        imagefill($thumb, 0, 0, $alpha);       
                                        imagecolortransparent($thumb, $alpha);
                                        if($oversize){
                                            $reduced = ImageCreateTrueColor($width_reduc, $height_reduc);
                                            imagefill($reduced, 0, 0, $alpha);       
                                            imagecolortransparent($reduced, $alpha);
                                        }
                                        break;
                                    default:
                                        break;
                                }
                                ImageCopyResampled($thumb,$original,0,0,0,0,$width_thumb,$height_thumb,$width_origin,$height_origin);
                                if($oversize){
                                    ImageCopyResampled($reduced,$original,0,0,0,0,$width_reduc,$height_reduc,$width_origin,$height_origin);
                                }
                                switch($ext[$i]){
                                    case 'jpg':
                                        imagejpeg($thumb,$dir. 'thumb/thumb_' . $filename);
                                        if($oversize){
                                            imagejpeg($reduced, $dir . $filename); 
                                        }
                                        break;
                                    case 'png':
                                        imagepng($thumb,$dir. 'thumb/thumb_' . $filename);
                                        if($oversize){
                                            imagepng($reduced, $dir . $filename); 
                                        }
                                        break;
                                    case 'gif':
                                        imagegif($thumb,$dir. 'thumb/thumb_' . $filename);
                                        if($oversize){
                                            imagegif($reduced, $dir . $filename); 
                                        }
                                        break;
                                    default:
                                        break;
                                }
                                imagedestroy($original);
		                        imagedestroy($thumb);
                                if($oversize){
                                    imagedestroy($reduced);
                                }
                                $postData['post_image_name'] = $postData['post_image_name'] . ',' . $filename;
                            }
                        }
                        unset($i);
                    }
                    //投稿
                    $newpost->set($postData);
                    if($newpost->post()){
                        $retData = array_overwrite(self::$retColumns,$postData);
                        echo json_encode(['result'=>'succsess','message' => 'complete','action' => 'create'] + $retData );
                    }
                    else{
                        $this->error($newpost->validateData());
                    }
                }
                else{
                    $this->error('すでに同じ内容の投稿があります');
                }
            }
            else{
                $this->error('unknown error occurred');
            }
        }
        else{
            $this->error('parameter is not found');
        }
    }
    function delete(){
        $target = $this->request->getGetValues('target_num');
        if($target == null || !is_int($target)){
            $this->error('incorrect parameter');
        }
        else{
            $post = new Post();
            $post->setFromDB($target);
            if($this->me->getNum() === $post->getUserNum()){
                if($post->delete()){
                    echo json_encode(['result'=>'succsess','message' => 'complete','action' => 'delete'] + $post->get());
                }
                else{
                    $this->error('failed');
                }
            }
        }
    }
}


?>
