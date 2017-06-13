<p id="genre_bar">
    <button type="button" class="all_button" name="">all</button>&nbsp;
    [:!loop genreList]
        <button type="button" class="genre_button" name="[::genre]">[::genre]</button>&nbsp;
    [:!end]
    </p>
    [:!loop list]
        <table class="overview">
            <td class="genre">[[::genre]]</td><td class="content-name"><a href="/content/[::content_name]">[::spell]</a></td><td class="user-count">[::users]users</td><td class="regdate">[::regdate]</td><td class="detail_button" data-content-num="[::content_num]">詳細</td>
        </table>
        <div class="detail" id="detail_[::content_num]">
            <img src="[::path_img][::user_image]">
            <p>[::user_comment]</p>
            <p><a href="/jump/?url=[::user_ref]">[::user_ref]</a></p>
            <font size="2"><span class="edit_button" data-content-num="[::content_num]">編集</span></font>
            <div class="edit" id="edit_[::content_num]">
                <form action="" method="POST">
                    <p>コンテンツ説明</p>
                    <textarea name="user_comment" class="comment_edit">[::user_comment]</textarea>
                    <p>参考リンク</p>
                    <input type="text" style="width:200px" name="user_ref" value="[::user_ref]">
                    <input type="hidden" name="token" value="[::token]">
                    <input type="hidden" name="content_num" value="[::content_num]">
                    <input type="hidden" name="content_name" value="[::content_name]">
                    <input type="submit" value="edit"><br/>
                </form>
            </div>
        </div>
    [:!end]
    
<br>
<br>
