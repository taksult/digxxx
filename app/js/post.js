//投稿関連
var defaultForm;    //投稿後リフレッシュ用innnerHTML格納変数

$(document).ready(function(){
    defaultForm = document.getElementById('main-form').innerHTML;
});

//入力データのバリデーション
var validateFormData = function(formId,Data){
    var ret = true;
    if(Data.content_name == undefined || Data.content_name == ''){
        $('#' + formId+' .content_name').attr('placeholder','コンテンツ名は入力必須です');
        $('#' + formId+' .content_name').css('border','1px solid red');
        ret = false;
    }
    return ret;
};


//フォームの内容をajaxで送信
$(document).on("click", ".createPost", (function(){
    $('.postStatus').html('送信中...');
    
    var Data = new Object();
    $('#main-form').children().each(function(i,e){
        Data[e.name] = e.value;
    });
    $('#main-form').children().each(function(i,e){
        Data['e.name'] = e.value;
    });
    Data.tags = $('.std-tag').val() +  ',' + $('.tags').val();
    //nsfw処理
    if($('#is-nsfw').prop('checked')){
        Data.nsfw = true;
        Data.tags = 'nsfw,' +  Data.tags;
    }
    else{
        Data.nsfw = null;
    }
    //dig処理
    if($('#is-dig').prop('checked')){
        Data.dig = 'true';
        Data.tags =  'dig,' +  Data.tags;
    }
    else{
        Data.dig =  null;
    }
    
    //画像ファイル
    //base64
    Data.images = new Array();
    $('#main-form .preview').each(function(i,e){
        var img = $(e).children('.data').attr('src');
        img = img.replace(/^.*?,(.*)$/,'$1');
        Data.images.push(img);
        console.log(img);
    });
    
    //formから
    //Data.set('image', $('input[type=file]')[0].files[0]);
    if(validateFormData('main-form',Data)){

        $.ajax({
            url: '/i/post/create/',
            type: 'POST',
            dataType: 'json',
            data:Data,
            /*
            xhrFields:{ withCredentials:true
            },
            */
            timeout: 30000,
            })
            .done(function (response){
                $('.postStatus').html('送信完了');
                $('#main-form').html(defaultForm);
                post_image_num = 0;
                post_image_cnt = 0;
                if(updateCallback !== undefined){
                    removeUpdate();
                    updateCallback(true);
                    setUpdate()
                }
            })
            .fail(function(response){
                $('.postStatus').html('送信失敗');
                console.log(response);
                alert(response.responseJSON.error.message);
            });
    }
    else{
        $('.postStatus').html('入力にエラーがあります');
    }
}));






