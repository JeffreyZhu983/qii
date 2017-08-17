$.fn.pages = function(options){
    var defaults = {
        ref : 'data-ref',
        total : 'data-total',
        current : 'data-current',
        page : 'data-page'
    };
    options = $.extend({}, defaults, options);

    var ref = $(this).attr(options.ref);
    var current = parseInt($(this).attr(options.current));
    var total = parseInt($(this).attr(options.total));
    $(this).find('[data-page]').bind('click', function(){
        var page = $(this).attr(options.page);
        if(page == 'next') {
            page = current + 1;
        }else if(page == 'more'){
        	page = current + 5;
        	page = page > total ? total : page;
        }else if(page == 'less'){
        	page = current -5;
        	page = page <= 0 ? 1 : page;
        }else if(page == 'pre'){
            page = current - 1;
        }else if(page == 'end') {
            page = total;
        }else if(page == current) {
            return;
        }
        if(page > total || page < 1) return;
        var url;
        //去掉当前ref中的currentPage参数
        ref = ref.replace(/[\?&]currentPage=[^&]*&?$/g, "");
        if(ref.indexOf('?') == -1) {
            url = ref + '?currentPage=' + page;
        }else{
            url = ref + '&currentPage=' + page;
        }
        window.location.href = url;
    });
}