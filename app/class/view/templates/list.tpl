<div id="checklist">
    <h3  class="list-user-name" name="[::user_id]">チェックリスト <a href="/user/p/[::user_id]">[::user_id]</a></h3>
    [>>follow_button]
    <div class="search">[>>list_search]</div>
    <div id="list-status"></div>
    <h3 class="list-title">Favorite</h3>
    <div id="elements-favorite">
    </div>

    <h3 class="list-title">Checking</h3>
    <div id="elements-checking">
    </div>

    <h3 class="list-title">Origin</h3>
    <div id="elements-origin">
    </div>
</div>
<div class="dialog" id="check-dialog">
    <p class="target"></p>
    <p class="annotation">一度追加すると削除できません(非公開は可能)</p>
    <input class="checkbox-favorite" type="checkbox" name="favorite" value="1">お気に入りに追加
    <input class="checkbox-hidden" type="checkbox" name="hidden" value="1">非公開で追加
    <p class="annotation">コメント</p>
    <textarea class="check-dialog-comment" name="user_comment"></textarea>
    <p class="annotation">参考URL</p>
    <input type="text" class="check-dialog-reference" name="user_ref">
</div>

<script type="text/javascript" src="/js/checklist.js"></script>

