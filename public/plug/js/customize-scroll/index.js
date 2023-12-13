$(function(){
    if(!isMobile()){
        connectCustomizeScroll();
    }
});

var connectCustomizeScroll = function(){
    var head = document.getElementsByTagName('HEAD')[0];
    var link = document.createElement('link');

    link.rel = 'stylesheet';
    link.type = 'text/css';
    link.href = 'public/css/customize_scroll.css';

    head.appendChild(link);
}
