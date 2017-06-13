<div class="timeline-controller">
    <p class="annotation">絞り込み,区切り)</p>
    <input id="extract" placeholder="タグorコンテンツ名"><button class="clear-tags">clear</button>
    <p><button name="stream" value="user/[::user_id]/" class="getTimeline">reload</button></p>
    <div class="nsfw-selector">
        <p class="annotation">NSFW投稿を表示する<input id="get-nsfw" type="checkbox" name="nsfw" [::nsfw_checked]></p>
    </div>
    <div class="player-controller">
        <button class="player-controll prev"></button>
        <button class="player-controll play"></button>
        <button class="player-controll next"></button>
        <button class="player-controll loop"></button>
    </div>
    <div class="autoplay-selector"><p class="annotation">埋め込みプレイヤーを連続再生する<input id="autoplay" type="checkbox" name="autoplay"></p>
    </div>
    <hr class="controller-border">
</div>
