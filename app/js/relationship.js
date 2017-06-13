var follow = function(target_id){
    $(this).html('通信中...');
    $.ajax({
        url: PATH_API + 'relationship/create/',
        type: 'POST',
        dataType: 'json',
        data:{'target_id' :target_id},
        /*
        xhrFields:{ withCredentials:true
        },
        */
        timeout: 5000,

        })
        .done(function (response) {
            $('.follow_button.'+target_id).each(function(i,e){
                $(e).html('following');
                e.setAttribute('value','true');
            });
        })
        .fail(function(){
            $(this).html('失敗');
        });
}

var remove = function(target_id){
    $(this).html('通信中...');
     $.ajax({
        url: PATH_API + 'relationship/remove/',
        type: 'POST',
        dataType: 'json',
        data:{'target_id' : target_id},
        /*
        xhrFields:{ withCredentials:true
        },
        */
        timeout: 5000,

        })
        .done(function (response) {
            $('.follow_button.'+target_id).each(function(i,e){
                $(e).html('follow');
                e.setAttribute('value','false');
            });

        })
        .fail(function(){
            $(this).html('失敗');
        });
}

$('.follow_button').hover(
    function(e){
        var follow_status = e.target.value;
        if(follow_status == 'true'){
            $(this).html('remove?');
            e.target.setAttribute('style','background-color:#888888');
        }
        else{
           e.target.removeAttribute('style');
        }
    },
    function(e){
        var follow_status = e.target.value;
        if(follow_status == 'true'){
            $(this).html('following');
            e.target.removeAttribute('style');
        }
        else{
            e.target.removeAttribute('style');
        }

    }
)

$('.follow_button').click(function(e){
    var target_id = e.target.name;
    var follow_status = e.target.value;
    if(follow_status == 'true'){
        e.target.setAttribute('disabled',true);
        remove(target_id);
        e.target.removeAttribute('disabled');
    }
    else{
        e.target.setAttribute('disabled',true);
        follow(target_id);
        e.target.removeAttribute('disabled');
    }
})
