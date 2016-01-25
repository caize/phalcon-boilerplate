$(function(){
    if ($('html').is('.ie6, .ie7, .ie8')) {
        alert("You are using an old version of IE. This website requires IE9 or greater");
        window.location.href = '/guest/browsers';
    }
});