<h3>ログイン</h3>
<form id="login" action="" method="POST">
    <p class="annotation">ユーザID</p>
    <input type="text" name="user_id" value="[::posted]"required  /><br/>
    <p class="annotation">パスワード</p>
    <input type="password" name="pword" required /><br/>
    <p class="annotation" style="margin:10px 0px">ログイン状態を保持する<input type="checkbox" name="auto-login"></p>
    <input type="hidden" name="token" value="[::token]">
    <input type="submit" value="ログイン" /></br>
</form>
