<div class="element" id="{{:content_num}}">
    <table class="overview">
        <td class="genre">[{{:genre}}]</td><td class="content-name" value="{{:content_name}}"><a href="/content/a/{{:content_link}}">{{:spell}}</a></td><td class="user-count">{{:user_count}}users</td><td class="regdate">{{:regdate}}</td><td class="detail_button" data-content-num="{{:content_num}}">詳細</td>
    <td><button class="addChecklist" name="{{:spell}}" value="{{:content_name}}">check</button></td></table>
    <div class="detail" id="detail_{{:content_num}}">
        <img src="/file/img/checklist/{{:user_image}}">
        <p>{{:user_comment}}</p>
        <p><a href="/jump/?url={{:user_ref}}">{{:user_ref}}</a></p> 
        <div class="tags">tags:{{props tags}}{{if prop != ''}}<a href="#{{:prop}}"><p class="tag">{{:prop}}</p></a> {{/if}}{{/props}}</div>
        <hr class="style1" style="width:100%;">
    </div>
</div>


