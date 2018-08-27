from flask import Flask
from importlib import import_module
from flask import Flask, flash, redirect, render_template, request, session, abort, Response
from flaskext.mysql import MySQL
import os
import pymysql
import cv2
from sqlalchemy.orm import sessionmaker
from tabledef import *
engine = create_engine('sqlite:///tutorial.db', echo=True)

# import camera driver
if os.environ.get('CAMERA'):
    Camera = import_module('camera_' + os.environ['CAMERA']).Camera
else:
    from camera import Camera

# Raspberry Pi camera module (requires picamera package)
from camera_pi import Camera


app = Flask(__name__)


db = pymysql.connect("localhost","monitor", "password", "temps")


class Camera2(object):
  def __init__(self):
    self.cap = cv2.VideoCapture(0)

  def get_frame(self):
    ret, frame = self.cap.read()
    cv2.imwrite('stream.jpg',frame)
    return open('stream.jpg', 'rb').read()




@app.route('/suggestions')
def suggestions():
	cursor = db.cursor()
        sql = "SELECT * FROM tempdat ORDER BY tdate DESC, ttime DESC LIMIT 1"
      	cursor.execute(sql)
        results = cursor.fetchall()
	return render_template('suggestions.html', results=results)



def gen(camera2):
  while True:
    frame = camera2.get_frame()
    yield (b'--frame\r\n'
      b'Content-Type: image/jpeg\r\n\r\n' + frame + b'\r\n')

@app.route('/')
def video_feed2():
  return Response(gen(Camera2()),
    mimetype='multipart/x-mixed-replace;boundary=frame') 



@app.route('/')
def home():
    if not session.get('logged_in'):
        return render_template('login.html')
    else:
    	  cursor = db.cursor()
   	  sql = "SELECT * FROM tempdat ORDER BY tdate DESC, ttime DESC LIMIT 1"
    	  cursor.execute(sql)
     	  results = cursor.fetchall()
	  return render_template('streaming.html', results=results)

@app.route('/login', methods=['POST'])
def do_admin_login():

    POST_USERNAME = str(request.form['username'])
    POST_PASSWORD = str(request.form['password'])

    Session = sessionmaker(bind=engine)
    s = Session()
    query = s.query(User).filter(User.username.in_([POST_USERNAME]), User.password.in_([POST_PASSWORD]) )
    result = query.first()
    if result:
        session['logged_in'] = True
    else:
        flash('wrong password!')
    return home()

@app.route("/logout")
def logout():
    session['logged_in'] = False
    return home()





def gen(camera):
    """Video streaming generator function."""
    while True:
	frame = camera.get_frame()
        yield (b'--frame\r\n'
               b'Content-Type: image/jpeg\r\n\r\n' + frame + b'\r\n')


@app.route('/video_feed')
def video_feed():
    """Video streaming route. Put this in the src attribute of an img tag."""
    return Response(gen(Camera()),
                    mimetype='multipart/x-mixed-replace; boundary=frame')







if __name__ == "__main__":
    app.secret_key = os.urandom(12)
    app.jinja_env.auto_reload = True
    app.config['TEMPLATES_AUTO_RELOAD'] = True
    app.run(debug=True, host='0.0.0.0', port=8080)
