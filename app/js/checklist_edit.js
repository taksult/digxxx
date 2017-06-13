$(document).ready(function(){
    $('#check-dialog').dialog({
        autoOpen:false,
        modal:true,
        width:'auto',
        buttons:[
            {
                text:'OK',
                click:function(){
                    var Data = {'target_id':$('.addChecklist').val(),
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
                        $('#status').html("<script>alert('" + response.message + "')</script>");
                        $('#check-dialog').dialog('close');
                    })
                    .fail(function(response){
                        $('#status').html("<script>alert('" + response.message + "')</script>");
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

$(document).on("click", ".addChecklist", function(){
    $('#check-dialog').show();
    $('#check-dialog').children('.target').html($('.addChecklist').val()+"をチェックリストに追加します");
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
