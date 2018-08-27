#!/usr/bin/python
# http://picamera.readthedocs.io/en/latest/recipes2.html#web-streaming

import mysql.connector


import io
import picamera
import logging
import socketserver
from threading import Condition
from http import server

PAGE="""\
<html>
<head>
<title>Raspberry Pi</title>
<script type="text/javascript" src="jquery-1.10.2.min.js"></script>
</head>
<body>
<center><h1>Camera videosorveglianza</h1></center>
<center><img src="stream.mjpg" width="640" height="480"></center>
</head>
<body>
<p>Here is my variable: {{ variable }}</p>


<iframe src="test.txt"></iframe>


<script>
var http = require('http');
var fs = require('fs');
http.createServer(function (req, res) {
  //Open a file on the server and return it's content:
  fs.readFile('test.txt', function(err, data) {
    res.writeHead(200, {'Content-Type': 'text/html'});
    res.write(data);
    return res.end();
  });
}).listen(8080);


</script>

</body>
</html>

"""


out_file = open("test.txt","w")
out_file.write("This Text is going to out file\nLook at it and see\n")
out_file.close()


















#import requests

#url = "http://domain.name/script.php"
#r = requests.get(url)


#mydb = mysql.connector.connect(
#  host="localhost",
#  user="monitor",
#  passwd="password",
 # database="temps",
#)

#mycursor = mydb.cursor()

#mycursor.execute("SELECT * FROM tempdat")

#myresult = mycursor.fetchall()

#for x in myresult:
#  print(x)





class StreamingOutput(object):
    def __init__(self):
        self.frame = None
        self.buffer = io.BytesIO()
        self.condition = Condition()

    def write(self, buf):
        if buf.startswith(b'\xff\xd8'):
            # New frame, copy the existing buffer's content and notify all
            # clients it's available
            self.buffer.truncate()
            with self.condition:
                self.frame = self.buffer.getvalue()
                self.condition.notify_all()
            self.buffer.seek(0)
        return self.buffer.write(buf)

class StreamingHandler(server.BaseHTTPRequestHandler):
    def do_GET(self):
        if self.path == '/':
            self.send_response(301)
            self.send_header('Location', '/index.html')
            self.end_headers()
        elif self.path == '/index.html':
            message= "oddeo"
            content = PAGE.encode('utf-8')
            self.send_response(200)
            self.send_header('Content-Type', 'text/html')
            self.send_header('Content-Length', len(content))
            self.end_headers()
            self.wfile.write(content)
        elif self.path == '/stream.mjpg':
            self.send_response(200)
            self.send_header('Age', 0)
            self.send_header('Cache-Control', 'no-cache, private')
            self.send_header('Pragma', 'no-cache')
            self.send_header('Content-Type', 'multipart/x-mixed-replace; boundary=FRAME')
            self.end_headers()
            try:
                while True:
                    with output.condition:
                        output.condition.wait()
                        frame = output.frame
                    self.wfile.write(b'--FRAME\r\n')
                    self.send_header('Content-Type', 'image/jpeg')
                    self.send_header('Content-Length', len(frame))
                    self.end_headers()
                    self.wfile.write(frame)
                    self.wfile.write(b'\r\n')
            except Exception as e:
                logging.warning(
                    'Removed streaming client %s: %s',
                    self.client_address, str(e))
        else:
            self.send_error(404)
            self.end_headers()

class StreamingServer(socketserver.ThreadingMixIn, server.HTTPServer):
    allow_reuse_address = True
    daemon_threads = True

with picamera.PiCamera(resolution='640x480', framerate=24) as camera:
    output = StreamingOutput()
    #Uncomment the next line to change your Pi's Camera rotation (in degrees)
    #camera.rotation = 90
    camera.start_recording(output, format='mjpeg')
    try:
        address = ('', 8081)
        server = StreamingServer(address, StreamingHandler)
        server.serve_forever()
    finally:
        camera.stop_recording()





