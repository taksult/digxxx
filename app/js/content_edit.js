/*
$(document).ready(function(){var s = $(".raw-spell").val(); $(".encode-spell").prop('value',encodeSlash(encodeURI(s)))}); $(document).on("change",".raw-spell",function(){var s = $(".raw-spell").val(); $(".encode-spell").attr('value',encodeSlash(encodeURI(s)))});
*/
articleBody = '<div class="article-body"></div>';

$(document).ready(function(){
    $(".content-edit").hide();
    getMarkup();
});

$('.editArticle').click(function(){
    $(".content-edit").slideToggle(0);
});

$(document).on("change",".content-edit .article-edit", function(){
    getMarkup();
});

var getMarkup = function(){
    articleBody = '<div class="article-body">';
    var markup = $(".article-edit").val();
    var rows = markup.split(/\n/);
    
    rows.forEach(function(val,i,array){
        articleBody += '<p>' + escape_html(val) + '</p>';
    });
    articleBody += '</div>';
};

$(document).on("click",".previewArticle",function(){
    articleBody = replaceTags(articleBody);
    $(".preview").html(articleBody);
});

var replaceTags = function(str){
    return str.replace(/<p><\/p>/g,'<br/>')
                .replace(/<p>\[:hl\s*(.*?)\]\]<\/p>/g,'<h2>$1</h2>')
                .replace(/<p>\[:hm\s*(.*?)\]\]<\/p>/g,'<h3>$1</h3>')
                .replace(/<p>\[:hs\s*(.*?)\]\]<\/p>/g,'<h4>$1</h4>')
                .replace(/\[:b\s*(.*?)\]\]/g,'<span style="font-weight:bold">$1</span>')
                .replace(/\[:i\s*(.*?)\]\]/g,'<span style="font-style:italic">$1</span>')
                .replace(/<p>\[\[line\]\]<\/p>/g,'<hr class="style1">')
                .replace(/\[\[\s*link\s*(http:\/\/[^\s]*?)\s*\]\]/g,'<a href="/jump/?url=$1" target="_blank">$1</a>')
                .replace(/\[\[\s*link\s*(https:\/\/[^\s]*?)\s*\]\]/g,'<a href="/jump/?url=$1" target="_blank">$1</a>')
                .replace(/\[\[\s*namelink\s*(http:\/\/.*?)\s+(.*?)\]\]/g,'<a href="/jump/?url=$1" target="_blank">$2</a>')
                .replace(/\[\[\s*namelink\s*(https:\/\/.*?)\s+(.*?)\]\]/g,'<a href="/jump/?url=$1" target="_blank">$2</a>')
                .replace(/\[=(.*?)\]\]/g,'<a href="/content/a/$1" target="_blank">$1</a>');
};
