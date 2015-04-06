<html>
    <head>
        <title>socket.io php server manager test</title>
    </head>
    <body>
        <div>
            <input type="button" id="buttonStartServer" value="Start Server" style="width:140px;margin-right: 10px;margin-bottom: 10px">
            <input type="button" id="buttonStopServer" value="Stop Server" style="width:140px;margin-right: 10px;margin-bottom: 10px">
        </div>
        <div>
            <input type="button" id="buttonConnect" value="Connect to Server" style="width:140px;margin-right: 10px;margin-bottom: 10px">
            <input type="button" id="buttonDisconnect" value="Disconnect from Server" style="width:140px;margin-right: 10px;margin-bottom: 10px">
        </div>
        <div id="status">
            <pre id="statusLog"></pre>
        </div>
        <script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
        <script src="socket.io.js"></script>
        
        <script>
            WEBSOCKET_PORT = 3000;

            WEBSOCKET_SERVER = "http://" + window.location.hostname + ":" + WEBSOCKET_PORT.toString();

            var socket = io(WEBSOCKET_SERVER);
            
            socket.on("connect", function () {
                console.log("Client connected to Server!");
                jQuery('#statusLog').append('<br>Client connected to Server!');
            });

            socket.on("socketID", function (socketid) {
                console.log(socketid);
                jQuery('#statusLog').append('<br>Socket ID: ' + socketid);
            });
            socket.on("progress", function (progress) {
                jQuery('#ocr #extractOutput').css('display', 'inline');
                jQuery('#ocr #extractOutput').text("Page " + progress + " has been processed.");
            });

            socket.on("data", function (data) {
                jQuery('#ocr .loader').css('display', 'none');
                jQuery('#ocr #extractOutput').css('display', 'none');
                jQuery('#ocr [type="button"]').attr('disabled', false);

                jQuery('#question_4 textarea').text(data);
            });

            socket.on("error", function (data) {
                alert('Ein Fehler ist aufgetreten');
                console.log(data);
            });
            socket.on('disconnect', function () {
                jQuery('#statusLog').append('<br>Disconnected from Server.');
            });
            socket.on('connect_error', function() {
                jQuery('#statusLog').append('<br>Node server not running.');
                socket.disconnect();
            });
            jQuery(document).ready(function () {
                jQuery('#buttonStartServer').click(function(){
                    jQuery.get('/plugins/ocrstream/ext/server_scripts.php', {start: true}, function (data) {
                    console.log(data);
                    output = JSON.parse(data);
                    jQuery('#statusLog').append('<br>' + output[0]);
                    });
                });
                jQuery('#buttonStopServer').click(function(){
                    jQuery.get('/plugins/ocrstream/ext/server_scripts.php', {stop: true}, function (data) {
                    console.log(data);
                    output = JSON.parse(data);
                    jQuery('#statusLog').append('<br>' + output[0]);
                    });
                });
                jQuery('#buttonConnect').click(function(){
                    socket.connect();
                });
                jQuery('#buttonDisconnect').click(function(){
                    socket.disconnect();
                });
            });
        </script>     
    </body>
</html>

