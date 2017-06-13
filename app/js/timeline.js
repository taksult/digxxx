//タイムライン関連

//表示中のタイムライン管理用グローバル変数
var currentTimeline = new Object();
var currentTimeline = {'type':'','param':'?', 'oldest':0, 'latest':0, 'topic':'', 'option':false};
var unread_num = 0;
//絞り込み用タグ管理
var tags = '';
var me = '';

//nsfw表示制御
var nsfw = '';

//自動更新
 updateTimer = null;
//自動更新タイマー設定
function setUpdate(){
    updateTimer = setInterval(updateCallback, 10000);
}
//自動更新停止
function removeUpdate(){
    clearInterval(updateTimer);
    updateTimer = null;
}

//定数
TIMELINE_READMORE = 1;
TIMELINE_UPDATE = 2;

//twitter シェアボタン
window.twttr = (function (d,s,id) {
  var t, js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return; js=d.createElement(s); js.id=id;
  js.src="https://platform.twitter.com/widgets.js"; fjs.parentNode.insertBefore(js, fjs);
  return window.twttr || (t = { _e: [], ready: function(f){ t._e.push(f) } });
}(document, "script", "twitter-wjs"));

var timeline_tmpl = null;  //タイムラインのテンプレート
//youtubeの自動再生周り
var current_player_id = 0;
var prev_player_id = 0;
var TL_player_id = 1;
var player;
var player_state = false;
var sc_player;

var direct_change = true;

var autoplay = false;
/* initialize YouTube Player */
$(document).ready(function(){
    if($('#autoplay').prop('checked')){
        autoplay = true;
        $(".player-controller .loop").addClass("loop-active");
        $(".player-controller .loop").removeClass("loop");
    }
    if($('#get-nsfw').prop('checked')){
        nsfw = true;
    }
    var YT_tag = document.createElement('script');
    YT_tag.src = "https://www.youtube.com/iframe_api";
    var firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag.parentNode.insertBefore(YT_tag, firstScriptTag);
});
function onYouTubeIframeAPIReady(){
    initializeTimeline();
}

//ページ読み込み時の初期タイムライン表示
var initializeTimeline = function(){
    current_player_id = 0;
    prev_player_id = 0;
    TL_player_id = 1;
    player = new Object();

    me = $('#me').text();
    
    //テンプレートの読み込み
    $.when($.get(PATH_JSRENDER + "jsrender-timeline.tpl"))
    .done(function(response){
        timeline_tmpl = response;

        var args = currentTimeline;
        var uri = location.pathname;
        var params = getParams();
        // /user/ ならそのユーザの投稿一覧を表示
        if(params[0] == "user"){
            args.type = params[0] + '/' + params[2] + '/';
            args.topic = params[2] + ":all";
        }
        //ゲストならstream
        else if(me === 'guest'){
            args.type = "stream/",
            args.topic = "steam:all";
        }
        //それ以外ならフォローしているユーザの投稿一覧
        else{
            args.type = "friends/",
            args.topic = "friends:all";
        }

        var hash = window.location.hash;
        tags = decodeURI(hash);
        tags = tags.replace(/^#(.*)/,"$1");
        if(tags != ''){
            tags = tags + ',';
            args.param = args.param + '&tags=' +  tags;
            args.topic = "friends:" + tags;
            $('#extract').val(tags);
        }
        
        getTimeline(args);

        //自動更新まわり
        setUpdate();
        var update_limit = setTimeout(removeUpdate,30000); //   30秒操作がなければ自動更新停止
        $('body').on('keydown mousemove mousedown',function(){
            clearTimeout(update_limit);
            if(updateTimer == null){
                setUpdate();
            }
            update_limit = setTimeout(removeUpdate,30000);
        });
    })
    .fail(function(){
    });

};

//パラメータを渡してajaxで投稿データを取得
var getTimeline = function(args){
    $('#status').html('通信中...');
    
    $.ajax({
            url: PATH_API + 'timeline/' + args.type + args.param + '&nsfw=' + nsfw,
            type: 'GET',
            dataType: 'json',
            /*
            xhrFields:{ withCredentials:true
            },
            */
            timeout: 5000,
        })
        .done(function (response) {
            //投稿データが取得できた場合
            if(response.length != 0){
                var send = new Object();        //取得データ(renderに投げる)
                send = {data : response, topic:args.topic};
                //optionのチェック
                if(args.option == TIMELINE_READMORE){
                    send.option = TIMELINE_READMORE;
                }
                else if(args.option == TIMELINE_UPDATE){
                    send.option = TIMELINE_UPDATE;
                }
                else{
                    send.option = false;
                }
                /*
                while(YT.Player === undefined){
                    ;
                }
                */
                renderTimeline(send);   //タイムラインをレンダリング

                //read more以外なら最新投稿番号を更新
                if(args.option != TIMELINE_READMORE){
                    currentTimeline.latest = response[0].post_num;
                }
                currentTimeline.type = args.type;
                currentTimeline.param = args.param.replace(/&offset=.*/,''); //オフセットをパラメータから削除
                currentTimeline.oldest = response[response.length-1].post_num;
                currentTimeline.topic = args.topic;
            }
            //1件も取得できなかった場合
            else{
                $('#status').html('最後の投稿です');
            }
        })
        .fail(function(){
            $('#status').html('失敗');
            currentTimeline.type = 'error/';
            currentTimeline.oldest = 0;
        });
}

//自動更新
updateCallback = function(force){
    var defer = new $.Deferred;
    var args = new Object();
    args = {type:currentTimeline.type,
            param:currentTimeline.param,
            topic:currentTimeline.topic,
            option:TIMELINE_UPDATE};
    $.ajax({
            url: PATH_API + 'timeline/' + currentTimeline.type + 'update/',
            type: 'POST',
            dataType: 'json',
            data:{'tags':tags,'nsfw':nsfw, 'latest':currentTimeline.latest},
            /*
            xhrFields:{ withCredentials:true
            },
            */
            timeout: 5000,
        })
        .done(function (response) {
            //投稿データが取得できた場合
            if(response.length != 0){
                var send = new Object();        //取得データ(renderに投げる)
                send = {data : response, topic:args.topic};
                send.option = TIMELINE_UPDATE;
                renderTimeline(send);   //タイムラインをレンダリング

                currentTimeline.type = args.type;
                currentTimeline.param = args.param;
                currentTimeline.latest = response[0].post_num;
                currentTimeline.topic = args.topic;
                if(force !== undefined){
                    showUnreadPosts();
                }
            }
            //1件も取得できなかった場合
            else{
            }
        })
        .fail(function(){
        });
}

//投稿削除
$(document).on('click','.delete',function(e){
    var target = $(e.target).attr('name');
    if(window.confirm('この投稿を削除します')){
        $.ajax({
            url: '/i/post/?target_num=' + target,
            type: 'DELETE',
            dataType: 'json',
            /*
            xhrFields:{ withCredentials:true
            },
            */
            timeout: 5000,
        })
        .done(function (response){
            //$('body').append("<script>alert('" + response.message + "')</script>");
            $('#'+target).remove();
        })
        .fail(function(response){
            $('body').append("<script>alert('" + response.message + "')</script>");
        });
    }
    else{
    }
});

//ジャンルボタンで絞り込みしてタイムライン表示
$(document).on('click', '.getTimeline', function(e){
    tags = $('#extract').val();
    args = new Object();
    args = {type:e.target.value, param:'?tags=' + tags, topic:e.target.name + ':' + tags};

    if(tags == '' ){
        args.topic = e.target.name + ':all';
    }

    current_player_id = 0;
    prev_player_id = 0;
    TL_player_id = 1;
    player = new Object();
    $('#timeline').html('');
    window.scrollTo( 0, $('#topic').scrollTop() + 300);
    getTimeline(args);
});

//タグで絞り込み
$(document).on('click','.tag', function(e){
    if($('#extract').val() == ''){
        tags = '';
    }
    tags = tags + $(e.target).text() + ',';
    $('#extract').val(tags);

    args =  { type:currentTimeline.type,
                param:'?tags=' + tags,
                topic:currentTimeline.topic.replace(/^(.*?:).*$/,"$1") + tags,
                option:false
    };
    current_player_id = 0;
    prev_player_id = 0;
    TL_player_id = 1;
    player = new Object();
    $('#timeline').html('');
    window.scrollTo( 0, $('#topic').scrollTop() + 300);
    getTimeline(args);
});

$(document).on('change','#extract', function(e){
})

$(document).on('click', '.clear-tags', function(){
    $('#extract').val('');
});

//新着(未読)投稿の表示
var showUnreadPosts = function(){
    var unread = $('#timeline').find('.post');
    for(var i = 0; i < unread_num; i++){
        $(unread[i]).show();
    }
    unread_num = 0;
    $('#unread_note').empty();
};

$('#main-column').on("click", "#unread_note", function(){
    showUnreadPosts();
});

//read moreボタン
$('#readMore').click(function(){
    var args = new Object();
    args =  { type:currentTimeline.type, param:currentTimeline.param + '&offset=' + (currentTimeline.oldest - 1),
                topic:currentTimeline.topic,
                option:TIMELINE_READMORE
    };
    getTimeline(args);
})

var re_yt = new RegExp(/^https:\/\/youtu.be\/.*?$|^https:\/\/www.youtube.com\/watch\?v=.*?$|^https:\/\/m.youtube.com\/watch\?v=.*?$/);
var re_sc = new RegExp(/.*?soundcloud.com\/.*?￥|(track_id|playlist_id)=([0-9]*?).*$/);
var re_sc_default = new RegExp(/(http|https):\/\/soundcloud.com\/(.*?)\/(.*)$/);

//JsRenderでテンプレートに投稿一覧をレンダリング(取得した投稿データと見出しを投げる)
var renderTimeline = function(args){
    if(timeline_tmpl !== null){
        $('#topic').html('<h3>' + escape_html(args.topic) + '</h3>');
        $.templates({tmpl: timeline_tmpl});
        Object.keys(args.data).forEach(function (key) {
            args.data[key].me = me;
            //URLを表示用に短縮
            if(args.data[key].reference_url != null && args.data[key].reference_url.length >= 32 ){
                //args.data[key].display_url =  args.data[key].reference_url.replace(/^(.*?):\/\/(.*?)\/.*$/,"$2/...");
                args.data[key].display_url =  args.data[key].reference_url.substr(0,29) + '...';
            }
            else{
                 args.data[key].display_url =  args.data[key].reference_url;
            }
            //画像ありなし判定
            args.data[key].img = false;
            if(args.data[key].post_image_name.length >= 1 && args.data[key].post_image_name[1] !== ''){
                args.data[key].img = true;
            }
            //YouTube埋め込み判定
            if(args.data[key].reference_url != null && re_yt.test(args.data[key].reference_url)){
                args.data[key].embed = true;
                args.data[key].yt = true;
                args.data[key].embed_id = args.data[key].reference_url.replace(/^https:\/\/youtu.be\/(.*?)$|^https:\/\/www.youtube.com\/watch\?v=(.*?)$|^https:\/\/m.youtube.com\/watch\?v=(.*?)$/,"$1"+"$2"+"$3");
                
                $.ajax({
                    url: '/i/youtu/?id=' + args.data[key].embed_id,
                    type: 'GET',
                    dataType: 'json',
                    timeout: 5000,
                })
                .done(function (response){
                    args.data[key].yt_title = response.items[0].snippet.title;
                    $("#"+args.data[key].post_num + " .yt .yt-thumnail .title").text(args.data[key].yt_title);
                })
                .fail(function(response){
                    args.data[key].yt_title = "動画タイトルの取得に失敗しました";
                    $("#"+args.data[key].post_num + " .yt .yt-thumnail .title").text(args.data[key].yt_title);
                });
                
                args.data[key].player_id = TL_player_id;
                TL_player_id++;
            }
            //SoundCloud埋め込み判定
            else if(args.data[key].reference_url != null && re_sc.test(args.data[key].reference_url)){
                args.data[key].embed = true;
                args.data[key].sc = true;
                args.data[key].embed_id = args.data[key].reference_url.replace(/.*?soundcloud.com\/.*?\?\|(track_id|playlist_id)=([0-9]*).*$/,"$1/$2");
                args.data[key].embed_id = args.data[key].embed_id.replace(/track_id/,'tracks');
                args.data[key].embed_id = args.data[key].embed_id.replace(/playlist_id/,'playlists');
                args.data[key].player_id = TL_player_id;
                TL_player_id++;
            }
            
            //track_idが取得できない場合(プレイヤーに含めないが埋め込みはする)
            else if(args.data[key].reference_url != null && re_sc_default.test(args.data[key].reference_url)){
                $.ajax({
                    url: 'https://soundcloud.com/oembed?url=' + encodeURI(args.data[key].reference_url) + '&hide_related=tru&show_comments=false&maxheight=81&liking=false&sharing=false&buying=false&show_reposts=false&download=false',
                    type: 'GET',
                    dataType: 'json',
                    timeout: 10000,
                })
                .done(function (response){
                    args.data[key].embed_player = response.html;
                    $("#" + args.data[key].post_num + ' .reference_url').html(args.data[key].embed_player);
                    $("#" + args.data[key].post_num + ' .reference_url').append('<p class="annotation">このプレイヤーは自動再生されません</p>');
                })
                .fail(function(response){
                    console.log(response);
                });
                
            }
            args.data[key].post_comment_replaced = nl_text(args.data[key].post_comment);
        });
        //オフセットつきの場合末尾に追加
        if(args.option == TIMELINE_READMORE){
            $("#timeline").append($.render.tmpl(args.data));
            currentTimeline.option = false;   //optionフラグを落とす
        }
        //更新の場合先頭に追加
        else if(args.option == TIMELINE_UPDATE){
            //新着投稿を非表示で追加
            var unread = $.render.tmpl(args.data);
            $($(unread).get().reverse()).each(function(i,e){
                if(e.innerHTML !== undefined){
                    $(e).css('display','none');
                    $('#timeline').prepend($(e));
                }
            });
           //新着投稿の通知を表示
            unread_num += args.data.length;
            $("#unread_note").html(unread_num + '件の新着投稿');
            $("#unread_note").css('background','#aaaaaa');
            currentTimeline.option = false;   //optionフラグを落とす
        }
        //それ以外は新規でタイムライン表示
        else{
            /* こうするとなぜか条件に合わなくてもグローバル変数が初期化されて死ぬ
            current_player_id = 0;
            prev_player_id = 0;
            TL_player_id = 1;
            player = new Object();
            */
            $("#timeline").html($.render.tmpl(args.data));
        }
        $('#status').html('');
        //twitterシェアボタンリロード
        twttr.widgets.load();

        //埋め込みプレーヤー(プレーヤーオブジェクトはplayerで管理)
        Object.keys(args.data).forEach(function(key) {
            //youtube
            if(args.data[key].yt !== undefined && args.data[key].yt == true){
                player[args.data[key].player_id] = new Object();
                player[args.data[key].player_id].embed_id = args.data[key].embed_id;
                
                //PCならYouTubeは遅延読み込み
                if(getDevice() === 'pc'){
                    player[args.data[key].player_id].type = 'yt';
                    player[args.data[key].player_id].active = false;
                }
                
                //スマホその他
                else{
                    $('#embed-'+args.data[key].player_id).empty();
                    player[args.data[key].player_id].controller
                        = new YT.Player('embed-'+args.data[key].player_id,{
                                width: 'auto',
                                height: 'auto',
                                videoId: args.data[key].embed_id,
                                events: {
                                    'onStateChange': onPlayerStateChange
                                }
                    });
                    player[args.data[key].player_id].type = 'yt';
                    player[args.data[key].player_id].active = 'active';
                }
            }
            
            //soundcloud
            else if(args.data[key].sc !== undefined && args.data[key].sc == true){
                player[args.data[key].player_id] = new Object();
                var iframeElement   = document.querySelector('#embed-'+args.data[key].player_id);    
                player[args.data[key].player_id].controller = SC.Widget(iframeElement);
                player[args.data[key].player_id].player_id = args.data[key].player_id;
                var widget = player[args.data[key].player_id];
                widget.controller.bind(SC.Widget.Events.READY, function() {
                    widget.controller.setVolume(0.5);
                    widget.controller.bind(SC.Widget.Events.PLAY, function(){
                       onSCPlay(widget);
                    });
                    widget.controller.bind(SC.Widget.Events.FINISH, function(){
                       onSCFinish(widget);
                    });
                    widget.controller.bind(SC.Widget.Events.PAUSE, function(){
                       onSCPause(widget);
                    });
                });
                player[args.data[key].player_id].type = 'sc';
                player[args.data[key].player_id].active = true;
            }
        });
        

    }
    else{
        $('#status').html('失敗');
    }

}

//埋め込みプレーヤーの制御
var re_pl = new RegExp(/^embed-(.*?)$/);

//YouTubeの遅延読み込み
$(document).on('click','.area-to-play', function(e){
    var player_id = Number($(e.target).parent().parent()[0].id.replace(/embed-/,''));
    $(e.target).parent().parent().empty();
    lazyLoadYT(player_id);
});

var lazyLoadYT = function(player_id){
    player[player_id].controller
                    = new YT.Player('embed-'+player_id,{
                            width: 'auto',
                            height: 'auto',
                            videoId: player[player_id].embed_id,
                            events: {
                                'onStateChange': onPlayerStateChange,
                                'onReady': function(){player[player_id].controller.playVideo();}
                            }
                });
    player[player_id].active = true;
}

//youtubeのイベント
function onPlayerStateChange(e){
    //動画が再生されたとき
    if(e.data === YT.PlayerState.PLAYING){
        player_state = true;
        prev_player_id = current_player_id;
        if(e.player_id !== undefined){
            current_player_id = e.player_id;
        }
        else{
            current_player_id = e.target.a.id.replace(/^embed-([0-9]*)$/,"$1");
        }
        //別の動画を再生していたら停止
        if(current_player_id !== 0 && prev_player_id != 0 && current_player_id != prev_player_id){
            if(player[prev_player_id].type == 'yt'){
                player[prev_player_id].controller.pauseVideo();
            }
            else if(player[prev_player_id].type == 'sc'){
                player[prev_player_id].controller.pause();
            }
        }
        prev_player_id = current_player_id - 1;
        $(".player-controller .play").addClass("pause");
        $(".player-controller .play").removeClass("play");
    }

    //動画が終了したら次の動画を再生する
    else if(e.data === YT.PlayerState.ENDED){
        if(autoplay == true){
            playerNext();
        }
        else{
            $(".player-controller .pause").addClass("play");
            $(".player-controller .pause").removeClass("pause");
        }
    }
    
    //一時停止
    else if(e.data === YT.PlayerState.PAUSED){
        if(direct_change){
            player_state = false;
            $(".player-controller .pause").addClass("play");
            $(".player-controller .pause").removeClass("pause");
        }
        else{
            direct_change = true;
        }
    }
}

//soudcloudのイベント
function onSCPlay(widget){
    player_state = true;
    prev_player_id = current_player_id;
    current_player_id = widget.player_id;
    //別の動画を再生していたら停止
    if(current_player_id !== 0 && prev_player_id != 0 && current_player_id != prev_player_id){
        if(player[prev_player_id].type == 'yt'){
            player[prev_player_id].controller.pauseVideo();
        }
        else if(player[prev_player_id].type == 'sc'){
            player[prev_player_id].controller.pause();
        }
    }
    prev_player_id = current_player_id - 1;
    $(".player-controller .play").addClass("pause");
    $(".player-controller .play").removeClass("play");
}

function onSCFinish(widget){
    if(autoplay == true){
        player_state = true;
        playerNext();
    }
    else{
        $(".player-controller .pause").addClass("play");
        $(".player-controller .pause").removeClass("pause");
    }
}

function onSCPause(widget){
    if(direct_change){
        player_state = false;
        $(".player-controller .pause").addClass("play");
        $(".player-controller .pause").removeClass("pause");
    }
    else{
        direct_change = true;
    }
}


function playerPlay(){
    player_state = true;
    if(current_player_id == 0){
        current_player_id = 1;
        prev_player_id = Object.keys(player).length;
    }
     //動画が未読み込みなら読み込み
    if(player[current_player_id].type == 'yt' && player[current_player_id].active == false){
            lazyLoadYT(current_player_id);
    }
    //再生
    else{
        if(player[current_player_id].type == 'yt'){
            player[current_player_id].controller.playVideo();
        }
        else if(player[current_player_id].type == 'sc'){
            player[current_player_id].controller.play();
        }
    }
    $(".player-controller .play").addClass("pause");
    $(".player-controller .play").removeClass("play");
}

function playerPause(){
    player_state = false;
    //停止
    if(player[current_player_id].type == 'yt'){
        player[current_player_id].controller.pauseVideo();
    }
    else if(player[current_player_id].type == 'sc'){
        player[current_player_id].controller.pause();
    }
    $(".player-controller .pause").addClass("play");
    $(".player-controller .pause").removeClass("pause");
}

function playerPrev(){
    //事前にプレイヤーを再生したことがあればcurrent_playerを1つ戻す
    if(current_player_id !== 0){ 
        if(current_player_id == 1){
            current_player_id = Object.keys(player).length;
            prev_player_id = current_player_id -1;
        }
        else if(current_player_id == 2){
            current_player_id = prev_player_id;
            prev_player_id = Object.keys(player).length;
        }
        else{
            current_player_id = prev_player_id;
            prev_player_id = current_player_id -1;
        }
        
        //player_stateがtrueなら再生
        if(player_state == true){
             //動画が未読み込みなら読み込み
            if(player[current_player_id].type == 'yt' && player[current_player_id].active == false){
                lazyLoadYT(current_player_id);
            }
            else{
                player[current_player_id].controller.seekTo(0);
                if(player[current_player_id].type == 'yt'){
                    player[current_player_id].controller.playVideo();
                }
                else if(player[current_player_id].type == 'sc'){
                    player[current_player_id].controller.play();
                }
            }
            $(".player-controller .play").addClass("pause");
            $(".player-controller .play").removeClass("play");
        }
        else{
            if(player[current_player_id].active == true){
                player[current_player_id].controller.seekTo(0);
            }
        }
    }
}

function playerNext(){
    //事前にプレイヤーを再生したことがあればcurrent_playerを1つ進める
    var state_buf = player_state;
    if(current_player_id !== 0){ 
        if(player_state === true){
            if(current_player_id !== 0){
                if(player[current_player_id].type == 'yt'){
                    player[current_player_id].controller.pauseVideo();
                }
                else if(player[current_player_id].type == 'sc'){
                    player[current_player_id].controller.pause();
                }
            }
        }
        prev_player_id = current_player_id;
        if(current_player_id == Object.keys(player).length){
            current_player_id = 1;
        }
        else{
            current_player_id++;
        }
        
        player_state = state_buf;
        //player_stateがtrueなら再生
        if(player_state == true){
             //動画が未読み込みなら読み込み
            if(player[current_player_id].type == 'yt' && player[current_player_id].active == false){
                lazyLoadYT(current_player_id);
            }
            else{
                player[current_player_id].controller.seekTo(0);
                if(player[current_player_id].type == 'yt'){
                    player[current_player_id].controller.playVideo();
                }
                else if(player[current_player_id].type == 'sc'){
                    player[current_player_id].controller.play();
                }
            }
            $(".player-controller .play").addClass("pause");
            $(".player-controller .play").removeClass("play");
        }
        else{
            if(player[current_player_id].active == true){
                player[current_player_id].controller.seekTo(0);
            }
        }
    }
}

//プレイヤーコントローラー
//再生
$(document).on('click', ".player-controller .play", function(){
    playerPlay();
});

//一時停止
$(document).on('click', ".player-controller .pause", function(){
    playerPause();
});

//前に戻る
$(document).on('click', ".player-controller .prev", function(){
    if(player_state){
        direct_change = false;
    }
    seek_start = false;
    /*
    soundcloudのgetPosition対応策思いつくまで放置
    if(current_player_id !== 0 && player[current_player_id].type === 'yt'){
        if(player[current_player_id].controller.getCurrentTime() > 3.0){
            seek_start = true;
        }
    }
    else if(current_player_id !== 0 && player[current_player_id].type === 'sc'){
        player[current_player_id].controller.getPosition(function(p) {
            console.log(p);
            if(p > 3000){
                seek_start = true;
                console.log(seek_start);
            }
        });
        console.log(seek_start);
    }
    */
    if(seek_start){
        player[current_player_id].controller.seekTo(0);
    }
    else{
        if(player_state === true){
            playerPause();
            player_state = true;
        }
        playerPrev();
    }
});

//次に進む
$(document).on('click', ".player-controller .next", function(){
    if(player_state){
        direct_change = false;
    }
    if(player_state === true){
        playerPause();
        player_state = true;
    }
    playerNext();
});

//ループ再生
$(document).on('click', ".player-controller .loop", function(){
    $('#autoplay').prop("checked",true);
    autoplay = true;
    $(".player-controller .loop").addClass("loop-active");
    $(".player-controller .loop").removeClass("loop");
});

//ループ終了
$(document).on('click', ".player-controller .loop-active", function(){
    $('#autoplay').prop("checked",false);
    autoplay = false;
    $(".player-controller .loop-active").addClass("loop");
    $(".player-controller .loop-active").removeClass("loop-active");
});

$(document).on('change','#autoplay',function(e){
    if(e.target.checked){
        autoplay = true;
    }
    else{
        autoplay = false;
    }
})

$(document).on('click','#get-nsfw',function(e){
    if(e.target.checked){
        nsfw = true;
    }
    else{
        nsfw = '';
    }
})
