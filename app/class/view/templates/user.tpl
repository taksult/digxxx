<div id="page-cover">
    <div id="side-column">
        <p id="me" style="display:none">[::me]</p>
        <div id="profile">
            <div class="user-icon" style="width:100px;height:100px">
               <a href=/user/p/[::user_id]><img class="user-icon" src="[::path_img_icon][::user_icon]" style="max-width:100px;max-height:100px"></a>
            </div>
            <p class="user-name">[::user_name]</p>
            <a href=/user/p/[::user_id]><p class="user-id">id:[::user_id]</p></a>
            <div class="user-comment"><p>[::user_comment]</p></div>
            <div id="checkList">
                <a href="/list/[::user_id]">チェックリスト</a>
            </div>
            <div id="FFlist">
                <p><a href="/user/ft/[::user_id]">フォロー:[::followingcount]</a>/<a href="/user/fb/[::user_id]">フォロワー:[::followercount]</a></p>
            </div>
            [>>follow_button]
            <hr>
        </div>
        [>>controller]
    </div>

    <div id="main-column">
    <!-- 投稿一覧 -->
        [>>timeline]
    </div>


