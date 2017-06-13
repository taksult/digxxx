<div id="{{:post_num}}" class ="post">
    <div class="user-info">
        <a href="/user/p/{{:user_id}}"><div class="user-icon"><img class="user-icon" src="/file/img/icon/{{:user_icon}}"></div></a>
        <div class="id">
            <p class="user_name">{{:user_name}}</p>
            <h3 class="user_id"><a href="/user/p/{{:user_id}}">id:{{:user_id}}</a></h3>
        </div> 
        {{if nsfw == true}}<img class="status-icon" src="/resource/nsfw_2.png">{{/if}}
        {{if dig == true}}<img class="status-icon" src="/resource/dig.png">{{/if}}
        <div class="icon_clear"></div>
    </div>
    <p class="content_name"><a href="/content/a/{{:content_link}}">{{:content_name}}</a></p>
    {{if img == true}}
       <div class="thumbnails">
        {{props post_image_name}}{{if prop != ''}}<div class="thumb"><a href="/file/img/post/{{:prop}}" target="_blank"><img src="/file/img/post/thumb/thumb_{{:prop}}"></a></div>{{/if}}{{/props}}
        </div>
    {{/if}}
    {{if embed === undefined || img == true}}<p class="reference_url"><a  href="/jump/?url={{:reference_url}}" target="_blank">{{:display_url}}</a></p>
    {{else yt == true}}<div id="embed-{{:player_id}}" class="yt">
                                       <div class="yt-thumnail">
                                            <a href="https://youtu.be/{{:embed_id}}" target="_blank"><span class="title">{{:yt_title}}</span></a>
                                            <div class="screen-cover"></div>
                                            <div class="area-to-play"></div>
                                            <svg class="ytp-button-copy" height="100%" version="1.1" viewBox="0 0 68 48" width="42px"><path class="ytp-button-copy-color" d="m .66,37.62 c 0,0 .66,4.70 2.70,6.77 2.58,2.71 5.98,2.63 7.49,2.91 5.43,.52 23.10,.68 23.12,.68 .00,-1.3e-5 14.29,-0.02 23.81,-0.71 1.32,-0.15 4.22,-0.17 6.81,-2.89 2.03,-2.07 2.70,-6.77 2.70,-6.77 0,0 .67,-5.52 .67,-11.04 l 0,-5.17 c 0,-5.52 -0.67,-11.04 -0.67,-11.04 0,0 -0.66,-4.70 -2.70,-6.77 C 62.03,.86 59.13,.84 57.80,.69 48.28,0 34.00,0 34.00,0 33.97,0 19.69,0 10.18,.69 8.85,.84 5.95,.86 3.36,3.58 1.32,5.65 .66,10.35 .66,10.35 c 0,0 -0.55,4.50 -0.66,9.45 l 0,8.36 c .10,4.94 .66,9.45 .66,9.45 z" fill="#1f1f1e" fill-opacity="0.81"></path><path d="m 26.96,13.67 18.37,9.62 -18.37,9.55 -0.00,-19.17 z" fill="#fff"></path><path  d="M 45.02,23.46 45.32,23.28 26.96,13.67 43.32,24.34 45.02,23.46 z" fill="#ccc"></path>
                                            </svg>
                                            <img src="https://i.ytimg.com/vi/{{:embed_id}}/mqdefault.jpg" style="max-width:inherit;">
                                        </div>
                                </div>
    {{else sc == true}}<iframe id="embed-{{:player_id}}" class="sc" width="100%" height="300" scrolling="no" frameborder="no" src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/{{:embed_id}}&amp;auto_play=false&amp;hide_related=true&amp;show_comments=false&amp;show_user=true&amp;show_reposts=false&amp;sharing=false&amp;visual=true&amp;liking=false&amp;buying=false&amp;download=false&amp;show_artwork=false"></iframe>
    {{else}}<p class="reference_url"><a  href="/jump/?url={{:reference_url}}" target="_blank">{{:display_url}}</a></p>
    {{/if}}
    <div class="comment"><p>{{:post_comment}}</p></div>
    <div class="tags">tags:{{props tags}}{{if prop != ''}}<a href="#{{:prop}}">[<p class="tag">{{:prop}}</p>]</a>{{/if}}{{/props}}</div>
    <p class="regdate">{{:regdate}}</p>
    {{if user_id == me}}<div class="delete" name="{{:post_num}}">　　</div><p class="post-num" style="display:none">{{:post_num}}</p>
    <a href="https://twitter.com/share" class="twitter-share-button" data-text="タイムラインに投稿しました&#13;{{:content_name}}  {{:reference_url}} {{:post_comment_replaced}}&#13;"  data-via="diglue" data-url=" ">　</a>
    {{/if}}
    <hr>
</div>
<script>
        !function(d,s,id){
            var js,
            fjs=d.getElementsByTagName(s)[0],
            p=/^http:/.test(d.location)?'http':'https';
            if(!d.getElementById(id)){
                js=d.createElement(s);
                js.id=id;
                js.src=p+'://platform.twitter.com/widgets.js';
                fjs.parentNode.insertBefore(js,fjs);
            }
        }(document, 'script', 'twitter-wjs');
    </script>
