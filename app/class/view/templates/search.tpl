<form action="/search/" method="GET">
    <input type="text" name="keyword" placeholder="検索" required value="[::keyword]"><button type="submit"> <img src="/resource/searchbutton.png" height="20" width="20"></button>
</form>
<h3>"[::keyword]"の検索結果</h3>
<h3>コンテンツ</h3>
<table class="content-list">
    <tr><th>コンテンツ名</th><th>分類</th><th>ユーザ数</th><th>記事更新日</th></tr>
[:!loop content_list]
    <tr>
        <td>
           <p class="content_name"><a href="/content/a/[::content_link]">[::spell]</a></p>
        </td>
        <td>
            <p class="genre">[[::genre],[::category],[::tags]]</p>
        </td>
        <td>
            <p class="user-count"><a href="/content/u/[::content_link]">[::user_count]users</a></p>
        </td>
        <td>
            <p class="moddate">[::moddate]</p>
        </td>
    </tr>
[:!end]
</table>
[>>no_result]
<h3>ユーザ</h3>
<table class="user-list">
[:!loop user_list]
<tr>
    <td>
    <div class="list-user-info">
        <a href="/user/p/[::user_id]"><div class="user-icon"><img class="user-icon" src="/file/img/icon/[::user_icon]"></div></a>
        <div class="user-id-name"><p class="user-name">[::user_name]</p><h3 class="user-id"><a href="/user/p/[::user_id]">id:[::user_id]</a></h3></div>
        <div class="info-clear"></div>
    </div>
    </td>
    <td>
        <div class="list"><a href="/list/[::user_id]">リスト</a></div>
    </td>
    <td>
        <button name="[::user_id]" value="[::follow_status]" class="follow_button [::user_id]">[::is_follow]</button>
</div>
    </td>
</tr>
[:!end]
</table>
<script type="text/javascript" src="/js/relationship.js"></script>
