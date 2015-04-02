WEBSOCKET_PORT = 3000

WEBSOCKET_SERVER = "http://" + window.location.hostname + ":" + WEBSOCKET_PORT.toString()

var socket = io(WEBSOCKET_SERVER);

socket.on("connect", function(){
    console.log("Client has connected!");
});

socket.on("progress", function (progress) {
    jQuery('#ocr #extractOutput').css('display','inline');
    jQuery('#ocr #extractOutput').text("Page " + progress + " has been processed.");
});

socket.on("data", function (data) {
    jQuery('#ocr .loader').css('display','none');
    jQuery('#ocr #extractOutput').css('display','none');
    jQuery('#ocr [type="button"]').attr('disabled',false);

    jQuery('#question_4 textarea').text(data);
});

socket.on("error", function (data) {
    alert('Ein Fehler ist aufgetreten');
    console.log(data);
});










