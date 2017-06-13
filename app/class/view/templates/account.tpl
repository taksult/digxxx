<div id="account-config">
    <h3>プロフィール編集</h3>
    <h4>ユーザID:[::user_id]</h4>
    <form class="account-setting"  enctype="multipart/form-data" action="" method="POST">
        <h4>パスワード</h4>
        <a href="/account/p/"><p>パスワード変更</p></a>
        <h4>ユーザ名:[::user_name]</h4>
        <input type="text" name="user_name" value="[::user_name]" maxlength="64">
        <p class ="annotation">64文字以内</p>
        <h4>プロフィールコメント</h4>
        <textarea name="user_comment" maxlength="255">[::user_comment]</textarea><p class="annotation">255文字以内</p>
        <h4>ユーザアイコン</h4>
        <div class="user-icon">
            <a href="[::path_img_icon][::user_icon]"><img class="user-icon" src="[::path_img_icon][::user_icon]"></a>
        </div>
        <input type="file" name="upload_icon">
        <p class="annotation">1MBまで</p>
        <p class="annotation">※そのうちリサイズできるようにします</p>
        <h4>自分の投稿をstreamに表示しない<input type="checkbox" name="stream" value="1" [::stream_checked]></h4>
        <h4>NSFW投稿をデフォルトで表示する<input type="checkbox" name="show_nsfw" value="1" [::nsfw_checked]></h4>
        <input type="hidden" name="token" value=[::token]>
        <div style="text-align:right;"><input type="submit" value="変更を保存"></div>
    </form>
</div>
