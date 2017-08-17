$.fn.tips = function(options)
{
    var defaults = {
        title : '提示信息',
        message : '这是内容，参数为{message:内容}',
        tips : '如果是请点击确定按钮 ，否则请点取消。',
        timeout : 1000,
        submitText : '确定',
        cancleText : '取消',
        confirm : function(e){},
        cancel : function(e){}
    };
    var js=document.scripts;
    var jsPath = '';
    for(var i=0;i<js.length;i++)
    {
        if(js[i].src.indexOf("tips.js")>-1)
        {
            jsPath=js[i].src.substring(0,js[i].src.lastIndexOf("/")+1);
        }
    }
    var eleId = 'tips_'+ Math.round(Math.random() * 99 +1);
    var opts = $.extend({},defaults,options);
    var html = '';
    html += '<div class="tip" id="'+eleId+'">';
    html += '  <div class="tiptop"><span>'+ opts.title +'</span><a></a></div>';
    html += '  <div class="tipinfo">';
    html += '      <span><img src="'+ jsPath +'../images/ticon.png" /></span>';
    html += '      <div class="tipright">';
    html += '          <p>'+ opts.message +'</p>';
    html += '          <cite>'+ opts.tips +'</cite>';
    html += '      </div>';
    html += '  </div>';
    html += '  <div class="tipbtn">';
    html += '      <input name="confirm" type="button" data-mod="confirm"  class="sure" value="确定" />&nbsp;';
    html += '      <input name="calcel" type="button" data-mod="cancel" class="cancel" value="取消" />';
    html += '  </div>';
    html += '</div>';
    $('#' + eleId).remove();
    $('body').append(html);
    $('#'+ eleId).fadeIn(200);
    $(".tip").fadeIn(200);
    $('#' + eleId).find('[data-mod=confirm]').bind('click',function(e){
        opts.confirm(e);
        $('#' + eleId).remove();
    });
    $('#' + eleId).find('[data-mod=cancel]').bind('click',function(e){
        opts.cancel(e);
        $('#' + eleId).remove();
    });
}