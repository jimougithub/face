import argparse
import face_recognition
import numpy as np
import io
import MySQLdb

# Parameters
parser = argparse.ArgumentParser(description='--pic /var/tmp/1.jpg --id 1000001')
parser.add_argument('--pic', type=str, default=None)
parser.add_argument('--id', type=int, default=1000001)
args = parser.parse_args()

conn = MySQLdb.connect(host='localhost',user='ai',passwd='AiPassword',db='ai',charset='utf8')
cursor = conn.cursor()

new_image = face_recognition.load_image_file(args.pic)
new_encoding = face_recognition.face_encodings(new_image)[0]
output = io.BytesIO()
np.savetxt(output, new_encoding)
content = output.getvalue()
content = content.replace("\n", ";")

picname = str(args.id)+".jpg"

sql = "INSERT INTO knownpic VALUES('%d', '%s', '%s')" % \
(args.id, picname, content)
cursor.execute(sql)
conn.commit()