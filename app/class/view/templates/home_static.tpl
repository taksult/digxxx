<! -- 検索 -->
<div id="searchForm">
    <form action="/serch/" method="GET">
        <input type="text" name="keyword" placeholder="検索" required>
        <button type="submit"> <img src="/resource/searchbutton.png" height="20" width="20"></button>
    </form>
</div>


<!-- プロフィール -->
<div id="myProfile">
    <div id="myIcon">
        <img src="[::path_img][::user_icon]">
    </div>
    <div id="myComment">
        <p>[::user_name]</p>
        <p><font size="2">[::user_id]</font></p>
        <p>[::user_comment]</p>
    </div>
    <div id="checkList">
        <a href="/mylist/">マイリスト</a>
    </div>
    <div id="FFlist">
        <p><a href="#followList">フォロー:[::followingcount]</a>/<a href="#followedList">フォロワー:[::followercount]</a></p>
    </div>
</div>


<!-- 投稿フォーム -->
<div id="postForm">
    <form action="" method="POST">
        <input type="text" name="content_name" placeholder="コンテンツ名" required>
        <input type="text" name="genre" placeholder="ジャンル" required><br/>
        <textarea name="post_comment" placeholder="コメント(20文字以内)" maxlength="20"></textarea><br/>
        <input type="text" name="reference_url" placeholder="参考リンク">
        <input type="hidden" name="token" value="[::token]">
        <input type="hidden" name="content_num" value="[::content_num]">
        <input type="submit" value="POST"><br/>
    </form>
</div>

