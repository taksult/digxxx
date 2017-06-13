<table>
    <tr>
        <td>ジャンル</td>
        <td><select class="param-genre" name="genre">
            [:!loop genre_list]
                <option value="[::genre]">[::genre]</option>
            [:!end]
        </select></td>
    <tr>
    <tr>
        <td>カテゴリー</td>
        <td><select class="param-category" name="category">
            [:!loop category_list]
                    <option value="[::category]">[::category]</option>
            [:!end]
        </select></td>
    </tr>
    <tr>
        <td>タグ</td><td><input class="param-tags" type="text" value="[::tags]" placeholder="タグ"></td>
        <td><button class="extract">絞り込み</button></td>
    </tr>
</table>
