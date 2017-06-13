<h3>id:[::user_id]の[::type]</h3>
<table class="user-list">
[:!loop FFList]
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
