import face_recognition
import numpy as np
import io
import os
import MySQLdb
import conn

conn = MySQLdb.connect(host='localhost',user=conn.dbuser,passwd=conn.dbpass,db=conn.dbname,charset='utf8')
cursor = conn.cursor()

sql = "DELETE FROM knownpic"
cursor.execute(sql)
conn.commit()

for folderName, subfolders, filenames in os.walk('/var/www/html/ai/face/knownpic'):
  for filename in filenames:
    known_image = face_recognition.load_image_file(folderName +'/' + filename)
    known_encoding = face_recognition.face_encodings(known_image)[0]
    output = io.BytesIO()
    np.savetxt(output, known_encoding)
    content = output.getvalue().decode()
    content = content.replace("\n", ";")
    pplid = int(filename.replace(".jpg", ""))

    sql = "INSERT INTO knownpic VALUES('%d', '%s', '%s')" % \
    (pplid, filename, content)
    cursor.execute(sql)
    conn.commit()

    print(filename)

print("saved done")
