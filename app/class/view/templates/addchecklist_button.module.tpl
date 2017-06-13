<div class="addchecklist_button">
    <button class="addChecklist" value="[::content_name]">check</button>
    <button class="addOrigin" value="[::content_name]">origin</button>
    <div id="status"></div>
</div>
<div class="dialog" id="check-dialog">
    <p class="target"></p>
    <p class="annotation">一度追加すると削除できません(非公開は可能)</p>
    <input class="checkbox-favorite" type="checkbox" name="favorite" value="1">お気に入りに追加
    <input class="checkbox-hidden" type="checkbox" name="hidden" value="1">非公開で追加
    <p class="annotation">コメント</p>
    <textarea class="check-dialog-comment"name="user_comment"></textarea>
    <p class="annotation">参考URL</p>
    <input type="text" class="check-dialog-reference" name="user_ref">
</div>
<div class="dialog" id="origin-dialog">
</div>
<script type='text/javascript' src="/js/checklist.js"></script>
