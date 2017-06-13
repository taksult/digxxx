taste_with_target_tmpl = '';
genre = '';
category = '';
tags = '';
limit = 10;
offset = 0;

table_header = "";

$(document).ready(function(){
    table_header = $(".user-list").html();
    genre = $('.param-genre').val();
    category = $('.param-category').val();
    tags = $('.param-tags').val();
    //テンプレートの読み込み
    $.when($.get(PATH_JSRENDER + "jsrender-content-user.tpl"))
    .done(function(response){
        taste_with_target_tmpl = response;
        getTasteWithContentFollowers();
    })
    .fail(function(response){
    });
    getMyListCount();
});

var getMyListCount = function(){
    $('.mypart-count').html(GIF_LOADING);
    $.ajax({
        url: PATH_API + 'checklist/count/?target_id=' + $('#me').text() +
                                    '&genre=' + genre +
                                    '&category=' + category +
                                    '&tags=' + tags,
        type: 'GET',
        dataType: 'json',
        //data:Data,
        /*
        xhrFields:{ withCredentials:true
        },
        */
        timeout: 5000,
    })
    .done(function(response){
        $('.mypart-count').html(response);
    })
    .fail(function(){
        $('.mypart-count').html('取得失敗');
    });

    $.ajax({
        url: PATH_API + 'checklist/count/?target_id=' + $('#me').text(),
        type: 'GET',
        dataType: 'json',
        //data:Data,
        /*
        xhrFields:{ withCredentials:true
        },
        */
        timeout: 5000,
    })
    .done(function(response){
        $('.mylist-count').html(response);
    })
    .fail(function(){
        $('.mylist-count').html('取得失敗');
    });
};

var getTasteWithContentFollowers = function(){
    var params = getParams();
    $('#taste-status').html(GIF_LOADING);
    Data = {'content_id':params[2],'genre':genre,'category':category,'tags':tags,
            'limit':limit, 'offset':offset};
    $.ajax({
        url: PATH_API + 'relationship/taste/?content_id=' + params[2] +
                                    '&genre=' + genre +
                                    '&category=' + category +
                                    '&tags=' + tags +
                                    '&limit=' + limit +
                                    '&offset='+ offset,
        type: 'GET',
        dataType: 'json',
        //data:Data,
        /*
        xhrFields:{ withCredentials:true
        },
        */
        timeout: 5000,
    })
    .done(function(response){
        renderTastes(response);
        if(response.length < limit){
            $('#taste-status').html('最後のユーザです');
            $('#read-more').hide();
        }
        offset = offset + limit;
    })
    .fail(function(){
        $('#taste-status').html('通信失敗');
    });
};

var renderTastes = function(Data){
    if(taste_with_target_tmpl !== ''){
        $.templates({tmpl: taste_with_target_tmpl});
        $(".user-list").append($.render.tmpl(Data));
    }
};

$(document).on("click","#changeCondition" ,function(){
    $('.user-list').html('');
    $('.user-list').html(table_header);
    $('#read-more').show();
    offset = 0;
    genre = $('.param-genre').val();
    category = $('.param-category').val();
    tags = $('.param-tags').val();

    getTasteWithContentFollowers();
    getMyListCount();
});
