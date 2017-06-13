<div class="element" id="{{:content_num}}">
    <table class="overview">
        <td class="genre">[{{:genre}}]</td><td class="content-name" value="{{:content_name}}"><a href="/content/a/{{:content_link}}">{{:spell}}</a></td><td class="user-count">{{:user_count}}users</td><td class="regdate">{{:regdate}}</td><td class="detail_button" data-content-num="{{:content_num}}">詳細</td>
    <td class="unstar status-button"></td><td class="public status-button"></td><td class="not-origin-yet status-button"></td></table>
    <div class="detail" id="detail_{{:content_num}}">
        <img src="/file/img/checklist/{{:user_image}}">
        <p>{{:user_comment}}</p>
        <p><a href="/jump/?url={{:user_ref}}">{{:user_ref}}</a></p>
        <div class="tags">tags:{{props tags}}{{if prop != ''}}<a href="#{{:prop}}"><p class="tag">{{:prop}}</p></a> {{/if}}{{/props}}</div>
        <p class="edit_button annotation" data-content-num="{{:content_num}}">編集</p>
        <div class="edit" id="edit_{{:content_num}}">
            <form action="" method="POST">
                <p class="annotation">コンテンツ説明</p>
                <textarea name="user_comment" class="comment_edit">{{:user_comment}}</textarea>
                <p class="annotation">参考リンク</p>
                <input type="text" style="width:200px" name="user_ref" value="{{:user_ref}}">
                <p class="annotation">タグ(,区切り500文字以内)</p>
                <input type="text" name="tags" value="{{:tags}}">
                <input class="token" type="hidden" name="token" value="{{:token}}">
                <input type="hidden" name="content_num" value="{{:content_num}}">
                <input type="hidden" name="content_name" value="{{:content_name}}">
                <input type="submit" value="edit"><br/>
            </form>
        </div>
        <hr class="style1" style="width:100%; margin:10px 0px;">
    </div>
</div>


