var list_tmpl = null;
var user = '';
var publish = '';

var elements = new Array();
var e_checking = new Array();
var e_favorite = new Array();
var e_origin = new Array();

var add_target = '';

var genre = '';
var category = '';
var tags = '';

$(document).ready(function(){
    //テンプレートの読み込み
    var params = getParams();
    var filename = '';
    var uri = window.location.href;
    var g_match = uri.match(/\?genre=(.*?)[&\/]|&genre=(.*?)[&\/]|\?genre=(.*?)\s*$|&genre=(.*?)\s*$/);
    var c_match = uri.match(/\?category=(.*?)[&\/]|&category=(.*?)[&\/]|\?category=(.*?)\s*$|&category=(.*?)\s*$/);
    var t_match = uri.match(/\?tags=(.*?)[&\/]|&tags=(.*?)[&\/]|\?tags=(.*?)\s*$|&tags=(.*?)\s*$/);

    for(var i=1; i<=4; i++){
        if(g_match != null && g_match[i] !== undefined){
            genre = escape_html(decodeURI(g_match[i]));
        }
        if(c_match !== null && c_match[i] !== undefined){
            category = escape_html(decodeURI(c_match[i]));
        }
        if(c_match !== null && t_match[i] !== undefined){
            tags = escape_html(decodeURI(t_match[i]));
        }

    }
    $('.param-genre').val(genre);
    $('.param-category').val(category);
    $('.param-tags').val(tags);

    if(params[0] == 'mylist'){
        filename = "mylist.tpl";
    }
    else if(params[0] == 'list'){
        filename = "checklist.tpl";
    }
    $.when($.get(PATH_JSRENDER + "jsrender-" + filename))
    .done(function(response){
        list_tmpl = response;
        getCheckList();
    })
    .fail(function(response){
    });
});



var getCheckList = function(){
    $('#list-status').html(GIF_LOADING);
    user = $('.list-user-name').attr("name");
    var params = getParams()
    if(params[0] == 'list'){
        publish = '&public=true';
    }
    $.ajax({
            url: PATH_API + 'checklist/user/?target_id=' + user + publish + '&genre=' + genre + '&category=' + category + '&tags=' + tags,
            type: 'GET',
            dataType: 'json',
            /*
            xhrFields:{ withCredentials:true
            },
            */
            timeout: 5000,
        })
        .done(function(response){
            $('#list-status').html('');
            //振り分け
            for(i=0;i < response.length; i++){
                response[i].regdate = response[i].regdate.replace(/.{2}(.*?)\s(.*)/,"$1");
            }
            elements = response;
            renderList();
        })
        .fail(function(response){
            $('#list-status').html('通信失敗');
        });
}

var renderList = function(){
    //レンダリング前処理
    e_favorite = [];
    e_origin = [];
    e_checking = [];
    elements.forEach(function(e,i){
        //テンプレートアサイン用に振り分け
        if(e.origin){
            e_origin.push(e);
        }
        else if(e.favorite){
            e_favorite.push(e);
        }
        else{
            e_checking.push(e);
        }
    });

    if(list_tmpl !== null){
        $.templates({tmpl: list_tmpl});
        $("#elements-favorite").html($.render.tmpl(e_favorite));
        $("#elements-checking").html($.render.tmpl(e_checking));
        $("#elements-origin").html($.render.tmpl(e_origin));
    }

    //レンダリング後処理
    //ボタン切り替え
    elements.forEach(function(e,i){
        if(e.favorite){
            $("#" + e.content_num + " .overview .unstar").removeClass("unstar").addClass("star");
        }
        if(e.hidden){
            $("#" + e.content_num + " .overview .public").removeClass("public").addClass("unpublic");
        }
        if(e.origin){
            $("#" + e.content_num + " .overview .not-origin-yet").remove();
            $("#" + e.content_num + " .overview .star").remove();
            $("#" + e.content_num + " .overview .unstar").remove();
        }
    });
    $('.edit').hide();
    $('.detail').hide();
    $('.token').attr("value",$('#token').val());
    nl_textarea();
}

//全ジャンル表示
$('.all_button').click(function(e){
    $('#list > div').show();
    $('.edit').hide();
    $('.detail').hide();
}
);

//特定ジャンル表示
$('.genre_button').click(function(e){
    $('#list > div').show();
    $('.edit').hide();
    $('.detail').hide();
    $('#list > div:not(.' + e.target.name + ')').hide();
});


//詳細のスライド表示
$(document).on('click','.detail_button', function(e){
    $("#detail_"+ e.target.dataset.contentNum).slideToggle(0);
});

//編集フォームのスライド表示
$(document).on('click','.edit_button', function(e){
    $("#edit_"+ e.target.dataset.contentNum).slideToggle(0);
});

//コメント編集時textareaに<p>タグを除去して既存コメントを配置
var e = document.getElementsByClassName('comment_edit');
$.each($(e),function(i,v){
    v.value = v.value.replace(/<\/p><p>/g,'');
});


//ステータス変更関連
$(document).on("click", ".unstar", function(e) {
    target = e.target.parentNode;
    if(window.confirm($(target).children(".content-name").text() + ' をお気に入りに追加します')){
        $('#status').html('処理中...');
        var Data = new Object();
        Data['target_id'] = $(target).children(".content-name").attr("value");

        $.ajax({
            url: '/i/checklist/star/',
            type: 'POST',
            dataType: 'json',
            data:Data,
            /*
            xhrFields:{ withCredentials:true
            },
            */
            timeout: 5000,
            })
            .done(function (response){
                $('body').append("<script>alert('" + response.message + "')</script>");
                $(target).children('.unstar').removeClass('unstar').addClass('star');
                $('#elements-favorite').append($(target.parentNode.parentNode.parentNode).clone());
                $(target.parentNode.parentNode.parentNode).remove();
            })
            .fail(function(response){
                $('body').append("<script>alert('" + response.message + "')</script>");
                console.log(response);
                alert(response.responseJSON.error.message);
            });
    }
    else{
    }
});

$(document).on("click", ".star", function(e) {
    target = e.target.parentNode;
    if(window.confirm($(target).children(".content-name").text() + ' のお気に入りを解除します')){
        $('#status').html('処理中...');
        var Data = new Object();
        Data['target_id'] = $(target).children(".content-name").attr("value");

        $.ajax({
            url: '/i/checklist/unstar/',
            type: 'POST',
            dataType: 'json',
            data:Data,
            /*
            xhrFields:{ withCredentials:true
            },
            */
            timeout: 5000,
            })
            .done(function (response){
                $('body').append("<script>alert('" + response.message + "')</script>");
                $(target).children('.star').removeClass('star').addClass('unstar');
                $('#elements-checking').append($(target.parentNode.parentNode.parentNode).clone());
                $(target.parentNode.parentNode.parentNode).remove();
            })
            .fail(function(response){
                $('body').append("<script>alert('" + response.message + "')</script>");
                console.log(response);
                alert(response.responseJSON.error.message);
            });
    }
    else{
    }
});

$(document).on("click", ".public", function(e) {
    target = e.target.parentNode;
    if(window.confirm($(target).children(".content-name").text() + ' を非公開にします')){
        $('#status').html('処理中...');
        var Data = new Object();
        Data['target_id'] = $(target).children(".content-name").attr("value");

        $.ajax({
            url: '/i/checklist/unpublish/',
            type: 'POST',
            dataType: 'json',
            data:Data,
            /*
            xhrFields:{ withCredentials:true
            },
            */
            timeout: 5000,
            })
            .done(function (response){
                $('body').append("<script>alert('" + response.message + "')</script>");
                $(target).children(".public").removeClass("public").addClass("unpublic");

            })
            .fail(function(response){
                $('body').append("<script>alert('" + response.message + "')</script>");
                console.log(response);
                alert(response.responseJSON.error.message);
            });
    }
    else{
    }
});

$(document).on("click", ".unpublic", function(e) {
    target = e.target.parentNode;
    if(window.confirm($(target).children(".content-name").text() + ' を公開します')){
        $('#status').html('処理中...');
        var Data = new Object();
        Data['target_id'] = $(target).children(".content-name").attr("value");

        $.ajax({
            url: '/i/checklist/publish/',
            type: 'POST',
            dataType: 'json',
            data:Data,
            /*
            xhrFields:{ withCredentials:true
            },
            */
            timeout: 5000,
            })
            .done(function (response){
                $('body').append("<script>alert('" + response.message + "')</script>");
                $(target).children(".unpublic").removeClass("unpublic").addClass("public");

            })
            .fail(function(response){
                $('body').append("<script>alert('" + response.message + "')</script>");
                console.log(response);
                alert(response.responseJSON.error.message);
            });
    }
    else{
    }
});

$(document).on("click", ".not-origin-yet", function(e) {
    target = e.target.parentNode;
    if(window.confirm($(target).children(".content-name").text() + " をOriginに移動します\n(※元に戻すことはできません)")){
        $('#status').html('処理中...');
        var Data = new Object();
        Data['target_id'] = $(target).children(".content-name").attr("value");

        $.ajax({
            url: '/i/checklist/origin/',
            type: 'POST',
            dataType: 'json',
            data:Data,
            /*
            xhrFields:{ withCredentials:true
            },
            */
            timeout: 5000,
            })
            .done(function (response){
                $('body').append("<script>alert('" + response.message + "')</script>");
                $(target).children(".not-origin-yet").remove();
                $(target).children(".star").remove()
                $(target).children(".unstar").remove();
                $('#elements-origin').append($(target.parentNode.parentNode.parentNode).clone());
                $(target.parentNode.parentNode.parentNode).remove();
            })
            .fail(function(response){
                $('body').append("<script>alert('" + response.message + "')</script>");
                console.log(response);
                alert(response.responseJSON.error.message);
            });
    }
    else{
    }
});

//並べ替え
$(document).on("click",".sort",function(e){

    $("#elements-" + e.target.name).children(".element").addClass("ui-state-default");
    $(".element" + e.target.name).sortable();
});

var extract = function(){
    genre = $('.param-genre').val();
    category = $('.param-category').val();
    tags = $('.param-tags').val();

    $('#list-status').html(GIF_LOADING);
    $.ajax({
            url: PATH_API + 'checklist/user/?target_id=' + user + publish + '&genre=' + genre + '&category=' + category + '&tags=' + tags,
            type: 'GET',
            dataType: 'json',
            /*
            xhrFields:{ withCredentials:true
            },
            */
            timeout: 5000,
        })
        .done(function(response){
            $('#list-status').html('');
            //振り分け
            for(i=0;i < response.length; i++){
                response[i].regdate = response[i].regdate.replace(/.{2}(.*?)\s(.*)/,"$1");
            }
            elements = response;
            renderList();
        })
        .fail(function(response){
            $('#list-status').html('通信失敗');
        });

}

$(document).on("click", ".extract", function(){
    extract();
});

$(document).on("click", ".tag", function(e){
    tags = tags + $(e.target).text() + ',';
    $('.param-tags').val(tags);
    extract();
});


$(document).ready(function(){
    $('#check-dialog').dialog({
        autoOpen:false,
        modal:true,
        width:'auto',
        buttons:[
            {
                text:'OK',
                click:function(){
                    add_target = $('#check-dialog').children('.target').val();
                    var Data = {'target_id':add_target,
                        'favorite':$('#check-dialog').children('.checkbox-favorite').prop("checked"),
                        'hidden':$('#check-dialog').children('.checkbox-hidden').prop("checked"),
                        'user_comment':$('#check-dialog').children('.check-dialog-comment').val(),
                        'user_ref':$('#check-dialog').children('.check-dialog-reference').val()
                    };
                    $.ajax({
                        url: PATH_API + 'checklist/create/',
                        type: 'POST',
                        dataType: 'json',
                        data:Data,
                        /*
                        xhrFields:{ withCredentials:true
                        */
                        timeout: 5000,
                    })
                    .done(function (response){
                        $('body').append("<script>alert('" + response.message + "')</script>");
                        $('#check-dialog').dialog('close');
                    })
                    .fail(function(response){
                        $('body').append("<script>alert('" + response.message + "')</script>");
                        console.log(response);
                        alert(response.responseJSON.error.message);
                    });
                }
            },
            {
                text:'キャンセル',
                click:function(){
                    $(this).dialog("close");
                }
            }
        ]
    });
});

$(document).on("click", ".addChecklist", function(e){
    if(e.target.value != $('#check-dialog').children('.target').val()){
        $('#check-dialog').children(".checkbox-favorite").prop('checked',false);
        $('#check-dialog').children(".checkbox-hidden").prop('checked',false);
        $('#check-dialog').children(".check-dialog-comment").val('');
        $('#check-dialog').children(".check-dialog-reference").val('');
    }

    $('#check-dialog').show();
    $('#check-dialog').children('.target').html(e.target.value+"をチェックリストに追加します");
    $('#check-dialog').children('.target').val(e.target.value);
    $('#check-dialog').dialog("open");
});

$(document).on('click touchend','.ui-widget-overlay', function(){
    $(this).prev().find('.ui-dialog-content').dialog('close');
});


var addChecklist = function(data){
    $('#status').html('処理中...');
    var Data = new Object();
    Data['target_id'] = $('.addChecklist').attr('value');
    $.ajax({
        url: '/i/checklist/create/',
        type: 'POST',
        dataType: 'json',
        data:Data,
        /*
        xhrFields:{ withCredentials:true
        },
        */
        timeout: 5000,
        })
        .done(function (response){
            $('#status').html("<script>alert('" + response.message + "')</script>");
        })
        .fail(function(response){
            $('#status').html("<script>alert('" + response.message + "')</script>");
            console.log(response);
            alert(response.responseJSON.error.message);
        });
}
