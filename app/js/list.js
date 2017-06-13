elements = new Array();
e_checking = new Array();
e_favorite = new Array();
e_origin = new Array();

list_tmpl = null;

$(document).ready(function(){
    //テンプレートの読み込み
    $.when($.get(PATH_JSRENDER + "jsrender-mylist.tpl"))
    .done(function(response){
        list_tmpl = response;
        getCheckList();
    })
    .fail(function(response){
    });
});



var getCheckList = function(){
    user = $('.list-user-name').attr("name");
    $.ajax({
            url: PATH_API + 'checklist/user/?target_id=' + user,
            type: 'GET',
            dataType: 'json',
            /*
            xhrFields:{ withCredentials:true
            },
            */
            timeout: 5000,
        })
        .done(function(response){
            //振り分け
            for(i=0;i < response.length; i++){
                response[i].regdate = response[i].regdate.replace(/.{2}(.*?)\s(.*)/,"$1");
            }
            elements = response;
            renderList();
        })
        .fail(function(response){
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
                $('body').html("<script>alert('" + response.message + "')</script>");
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
                $('body').html("<script>alert('" + response.message + "')</script>");
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
                $('body').html("<script>alert('" + response.message + "')</script>");
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
                $('body').html("<script>alert('" + response.message + "')</script>");
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
