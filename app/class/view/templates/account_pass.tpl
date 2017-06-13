<a href="/account/">プロフィール編集に戻る</a>
<h3>パスワード変更</h3>
<form id="change-pass" action=""method="POST" />
<p class="annotation">現在のパスワード</p>
<input type="password" name="current_pass" required required pattern="^[0-9a-zA-Z!$%&/+*,;(){}^~]{8,}$" value="" />
<p class="annotation">新しいパスワード</p>
<input type="password" name="new_pass" required pattern="^[0-9a-zA-Z!$%&/+*,;(){}^~]{8,}$" />
<p class="annotation">新しいパスワードを再入力</p>
<input type="password" name="confirm" required pattern="^[0-9a-zA-Z!$%&/+*,;(){}^~]{8,}$"/>
<p class="annotation">半角英数および!$%&/+*,;(){}^~の記号で8文字以上</p>
<input type="hidden" name="token" value="[::token]"/>
<input type="hidden" name="user_id" value="[::user_id]"/>
<input type="submit" value="変更" />
</form>
