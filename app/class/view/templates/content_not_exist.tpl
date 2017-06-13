<h2>[::content_name]</h2>
<h3>このコンテンツはまだデータベースに存在しません</h3>
<p>チェックリストに追加するにはまずコンテンツをデータベースに登録してください</p>
<div class="create-content">
    <p>コンテンツ名:[::registration_name]<span class="annotation">※英字はすべて小文字で登録されます</span></p>
    <div class="annotation">
    <p>※登録したコンテンツはユーザ操作で削除することができません</p>
    <p>　誤字等がないか確認の上登録してください</p>
    </div>
    <form method="POST" action="/content/a/[::content_link]">
    表記：<input class="spell" name="spell" value="[::spell]">
        <input type="hidden"  class="content_name" name="content_name" value="[::content_name]">
        <input type="hidden"  class="token" name="token" value="[::token]">
        <input type="hidden" name="form-type" value="registration">
        <input type="submit" value="登録">
    </form>
    <script type="text/javascript" src="/js/content-edit.js"></script>
</div>
