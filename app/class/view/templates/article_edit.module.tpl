<hr class="style1">
<button class="editArticle">記事編集</button>
<div class="content-edit">
<form class="article-edit-form" action="" method="post">
    <p class="item-name"  style="display:inline">表記</p>
    <input  name="spell" type="text" value="[::spell]">
    <p class="item-name" style="display:inline">読み</p>
    <input  name="yomigana" type="text" value="[::yomigana]">
    <p></p>
    <p class="item-name" style="display:inline">ジャンル</p>
    <select name="genre"  id="genre-list">
            <option selected value="[::genre]">[::genre]</option>
            [:!loop genre_list]
                <option value="[::genre]">[::genre]</option>
            [:!end]
    </select>
    <p class="item-name" style="display:inline">カテゴリー</p>
    <select id="category-list" name="category">
            <option selected value="[::category]">[::category]</option>
            [:!loop category_list]
                <option value="[::category]">[::category]</option>
            [:!end]
    </select>
    <p class="item-name" style="display:inline">発表(活動開始)年</p>
    <select id="rlsdate-list" name="rlsdate">
            <option selected value="[::rlsdate]">[::rlsdate]</option>
        [:!loop years]
            <option value="[::year]">[::year]</option>
        [:!end]
    </select>
    <p class="item-name" style="padding-top:10px">記事内容</p>
    <p class="annotation">記法</p>
<p class="annotation">　[:hl 大見出し]]　[:hm 中見出し]]　[:hs 小見出し]]</p>
<p class="annotation">　[:b 太字]]　[:i イタリック]]</p>
<p class="annotation">　水平線 [[line]]
<p class="annotation">　リンク [[link http://~]]　名前付きリンク [[namelink http://~ リンク名]](http,https以外は反映されません)</p>
<p class="annotation">　diglue記事リンク [=コンテンツ名]]
<p class="annotation">　登録画像表示(準備中) [[img #(番号)]]</p>

    <textarea name="article" class="article-edit">[>>raw_article]</textarea>
    <button type="button" class="previewArticle">プレビュー</button>
    <div class="preview"></div>
    <p class="item-name">編集コメント</p>
    <textarea name="edit_comment" class="comment-edit"></textarea>
    <input type="hidden" name="form-type" value="article-edit">
    <input type="hidden"  class="token" name="token" value="[::token]">
    <input type="submit" value="編集"><br/>
</form>
<p class="annotation">※不適切な編集防止のため編集内容とユーザIDはサーバに記録されます</p>
</div>
<script type="text/javascript" src="/js/content_edit.js"></script>
<br><br>
