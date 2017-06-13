<h3>新規登録</h3>
<form id="registration" action=""method="POST" />
<p class="annotation">ユーザID</p>
<input type="text" name="user_id" required pattern="^[0-9A-Za-z-_]{1,32}$"value="[::posted]" />
<p class="annotation">半角英数および_(アンダースコア)で32文字以内</p>
<!--メールアドレス<input type="email" name="email" value="[::posted2]" required /><br/> -->
<p class="annotation">パスワード</p>
<input type="password" required pattern="^[0-9a-zA-Z!$%&/+*,;(){}^~]{8,}$" name="pword" />
<p class="annotation">半角英数および!$%&/+*,;(){}^~の記号で8文字以上</p>
<input type="hidden" name="token" value="[::token]"/>
<input type="submit" value="登録" />
</form>
