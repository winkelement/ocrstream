var pdf_extract = require('pdf-extract');
var path = require('path');
var io = require('socket.io').listen(3000);

io.on('connection', function extractPDF(socket){
    console.log('a user connected with SID: ' + socket.id);
    socket.emit('socketID', socket.id);
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
        processor.on('log', function(data) {
            console.log(data);
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

