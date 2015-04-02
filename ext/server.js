var http = require('http');
var fs = require('fs');
var pdf_extract = require('pdf-extract');
var path = require('path');
//var debug = require('debug')('jra.websocket.server')

var server = http.createServer(function(req, res){
    res.writeHead(200, {'Content-Type': 'text/html'});
    res.end('');
}).listen(3000, function(){
    console.log("Server running");
});
var io = require('socket.io')(server);

io.on('connection', function extractPDF(socket){
    console.log('a user connected');
    socket.on('extract', function(payload) {
        console.log ('I have received a request!');
        console.log(payload);

        options = {};
        if (payload.type === "text") {
            options.type = "text";
        }
        else if (payload.type === "ocr" || payload.type === "image") {
            options.type = "ocr";
        }

        var processor = pdf_extract(payload.path, options, function(err) {
            if (err) {
                console.log(err);
                return io.emit('error', err);
            }
        });
        processor.on('page', function(data) {
            io.emit("progress", data.index);
        });
        processor.on('complete', function(data) {
            io.emit("data", data.text_pages);
        });
        processor.on('error', function(err) {
            return io.emit('error', err);
        });
    });
    socket.on('disconnect', function(){
        console.log("disconnected");
    });
});

