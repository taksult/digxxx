<?php
class YoutuAPI extends APIController{
/*  継承
    protected $params;
    protected $request;
    protected $method;
*/

    private $target_id = null;

    function __construct(){
        parent::__construct();

         if($this->method == 'get'){
            if($this->request->getGetValues('id') != null){
                $this->target_id = $this->request->getGetValues('id');
            }
        }
        /*
        else if($this->method == 'post'){
            if($this->request->getPostValues('target_num') != null){
                $this->target_num = intval($this->request->getPostValues('target_num'));
            }
            if($this->request->getPostValues('target_id') != null){
                $this->target_id = $this->request->getPostValues('target_id');
            }
        }
        */
    }
    
    public function get(){
        if($this->target_id !== null){
            $url = "https://www.googleapis.com/youtube/v3/videos?id=" . $this->target_id . "&key=yourAPIkey&part=snippet,status";
            $ref = "https://yourhostname/i/youtu/";
            $option = [
                CURLOPT_TIMEOUT        => 3, // タイムアウト時間
            ];
            $curl = curl_init($url);
            curl_setopt_array($curl, $option);
            curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($curl, CURLOPT_REFERER, $ref);
            //curl_setopt($curl,CURLINFO_HEADER_OUT,true); //debug 
            $res = curl_exec($curl);
            //echo curl_getinfo($curl,CURLINFO_HEADER_OUT); //debug 
            curl_close($curl);
            //echo json_encode($res[0]);
            exit;
        }
    }
}
?>
