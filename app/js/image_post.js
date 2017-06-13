post_image_cnt = 0;
post_image_num = 0;
$(document).on('change','.input-image',function(){
    console.log(this.files);
    var file = this.files;
    if(post_image_cnt + file.length > 4){
        alert("1投稿に添えられる画像は4枚までです");
    }
    Array.from(file).forEach(function(val){
        if(post_image_cnt < 4){
            var reader = new FileReader();
            var data;
            reader.readAsDataURL(val);
            reader.onload = function(){
                console.log(post_image_cnt);
                data = reader.result;
                $('#preview').append("<div id=\"image"+ post_image_num++ +"\" class=\"preview\"><img class=\"remove\" src=\"/resource/remove.png\"><img class=\"data\" src=\"" + data + "\"></div>");
            };
            //$('#input-image-label' + post_image_cnt).hide();
            post_image_cnt++;
        }   
    });
});

$(document).on('click','.remove',function(){
    $(this).parent().fadeOut('fast', function(){$(this).remove(); post_image_cnt--; });
});
