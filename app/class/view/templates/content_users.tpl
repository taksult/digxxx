<h2 class="content-title">[::spell]</h2>をチェックしているユーザー
<div id="me" style="display:none">[::user_id]</div>
<h4 class="conditions">一致要素カウント条件</h4>
<table>
    <tr>
        <td>ジャンル</td>
        <td><select class="param-genre" name="genre">
            <option value="[::genre]">[::genre]</option>
            [:!loop genre_list]
                <option value="[::genre]">[::genre]</option>
            [:!end]
        </select></td>
    <tr>
    <tr>
        <td>カテゴリー</td>
        <td><select class="param-category" name="category">
            <option value="[::category]">[::category]</option>
            [:!loop category_list]
                    <option value="[::category]">[::category]</option>
            [:!end]
        </select></td>
    </tr>
    <tr>
        <td>タグ</td><td><input class="param-tags" type="text" value="[::tags]" placeholder="タグ(,区切り)"></td>
        <td><button id="changeCondition">条件変更</button></td>
    </tr>
</table>
<p style="margin-top:10px;">マイリスト:<span class="mypart-count">[::mypart_count]</span>/<span class="mylist-count">[::mylist_count]</span>件</p>
<table class="user-list" style="margin-top:30px;">
    <tr>
        <th style="text-align:left">ユーザー</th><th>リスト一致</th>
    </tr>
</table>
<button value="read_more" id="read-more">read more</button>
<div id="taste-status"></div>
<script type="text/javascript" src="/js/taste_with_content_followers.js"></script>

