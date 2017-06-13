//IE対策
/*
$(document).ready(function () {
    // window.console が未定義なら、オブジェクトにする
    if (typeof window.console === "undefined") {
        
         window.console = {}
    ;
    }
    // window.console.log が function でないならば、空の function を代入する
    if (typeof window.console.log !== "function") {
         window.console.log = function () {}
    }
});
*/

var getParams = function(){
    var uri = location.pathname;
    var params = uri.split('/');
    params.shift();
    return params;
}

function escape_html (string) {
  if(typeof string !== 'string') {
    return string;
  }
  return string.replace(/[&'`"<>]/g, function(match) {
    return {
      '&': '&amp;',
      "'": '&#x27;',
      '`': '&#x60;',
      '"': '&quot;',
      '<': '&lt;',
      '>': '&gt;',
    }[match]
  });
}
/*
escape_HTML = function(str){
  return str.replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
};
*/
encodeSlash = function(str){
    return str.replace(/\//g,'%2F');
}

var nl_text = function(str){
    if(str !== null){
        return str.replace(/<\/p><p>/g,"\n").replace(/<p>|<\/p>/,'');
    }
};
    

var nl_textarea = function(){
    $('textarea').each(function(i){
        var t = $(this).text().replace(/<br\/>/g,"<p></p>");
        $(this).text(t.replace(/<\/p><p>/g,"\n"));
    });
}

$(document).ready(function(){
    nl_textarea();
});

//UAでデバイス判定
getDevice = function(){
    var ua = navigator.userAgent;
    if(ua.indexOf('iPhone') > 0 || ua.indexOf('iPod') > 0 || ua.indexOf('Android') > 0 && ua.indexOf('Mobile') > 0){
        return 'sp';
    }else if(ua.indexOf('iPad') > 0 || ua.indexOf('Android') > 0){
        return 'tab';
    }else{
        return 'pc';
    }
};
