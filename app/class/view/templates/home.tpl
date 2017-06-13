<! -- 検索 -->

    <form action="/search/" method="GET">
        <input type="text" name="keyword" placeholder="検索" required><button type="submit"> <img src="/resource/searchbutton.png" height="20" width="20"></button>
    </form>
<div id="page-cover">
    <div id="side-column">
        <!-- プロフィール -->
        <div id="profile">
            <p id="me" style="display:none">[::me]</p>
            <div class="user-icon" style="width:100px;height:100px">
               <a href=/user/p/[::user_id]><img class="user-icon" src="[::path_img_icon][::user_icon]" style="max-width:100px;max-height:100px"></a>
            </div>
            <p class="user-name">[::user_name]</p>
            <a href=/user/p/[::user_id]><p class="user-id">id:[::user_id]</p></a>
            <div class="user-comment"><p>[::user_comment]</p></div>
            <div id="checkList">
                <a href="/mylist/">マイリスト</a>
            </div>
            <div id="FFlist">
                <p><a href="/user/ft/[::user_id]">フォロー:[::followingcount]</a>/<a href="/user/fb/[::user_id]">フォロワー:[::followercount]</a></p>
            </div>
            <p class="annotation" style="text-align:right;"><a href="/account/">設定</a></p>
            <hr>
        </div>
    [>>controller]
    </div>

    <div id="main-column">
        <!-- 投稿フォーム -->
        <div id="main-form">
            <input type="text" class="content_name" name="content_name" placeholder="コンテンツ名" required maxlength="255">
            <textarea  class="post_comment" name="post_comment" placeholder="コメント(20文字以内)" maxlength="20"></textarea>
            <input type="text"  class="reference_url" name="reference_url" placeholder="参考リンク">
            <input type="text"  class="tags" name="tags" placeholder="タグ(,区切り合計500文字以内)">
            <p class="annotation" style="display:inline">標準タグ:</p><select class="std-tag" name="std-tag">
                <option value="">-</option>
                [:!loop std_tags]
                    <option value="[::tag]">[::tag]</option>
                [:!end]
            </select>
            <p class="annotation" style="display:inline">dig</p><input id="is-dig" type="checkbox" name="dig">
            <p class="annotation" style="display:inline">NSFW(R-18)</p><input id="is-nsfw" type="checkbox" name="nsfw"><br/>
            <div id="image-form">
                <label id="input-image-label" for="input-image">
                    <img class="photo-icon" src="/resource/addphoto.png">
                    <input id="input-image" class="input-image" multiple accept="image/*" type="file" style="display:none">
                </label>
            </div>
            <div id="preview">
            </div>
            <p class="annotation">合計ファイルサイズが大きいと通信に失敗する場合があります</p>
            <input type="hidden"  class="token" name="token" value="[::token]">
            <input type="submit" value="POST" class="createPost">
        </div>
        <div class="postStatus"></div>
        <script type="text/javascript" src="/js/image_post.js"></script>
        <script type="text/javascript" src="/js/post.js"></script>
        [>>timeline]
    </div>
