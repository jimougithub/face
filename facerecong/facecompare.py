import argparse
import face_recognition
import numpy as np
import io
import MySQLdb
import conn

# Define varilables
known_encodings = []
known_pplids = []
rtnary = []

# Parameters
parser = argparse.ArgumentParser(description='--pic /var/tmp/1.jpg --num 1')
parser.add_argument('--pic', type=str, default=None)
parser.add_argument('--num', type=int, default=1)
args = parser.parse_args()

# Connect to database
conn = MySQLdb.connect(host='localhost',user=conn.dbuser,passwd=conn.dbpass,db=conn.dbname,charset='utf8')
cursor = conn.cursor()

# Detect face
unknown_image = face_recognition.load_image_file(args.pic)
unknown_encoding = face_recognition.face_encodings(unknown_image)[0]

# Retrieve faces data from database
sql = "SELECT picnumpy,pplid FROM knownpic"
cursor.execute(sql)
results = cursor.fetchall()
for row in results:
  content = row[0].replace(";", "\n")
  known_encoding = np.loadtxt(io.BytesIO(content.encode('latin-1')))
  known_encodings.append(known_encoding)
  known_pplids.append(str(row[1]))

# Compare with known faces data and come out distance value
distances = face_recognition.face_distance(known_encodings, unknown_encoding).tolist()

# Combine distance value and People ID into 1 array. Sort by distance
tmpary = np.column_stack((distances,known_pplids)).tolist()
tmpary.sort()

# Return number of records base on input. Default only return the best match record
for i in range(len(tmpary)):
  rtnary.append(tmpary[i])
  if args.num>0 and i>=args.num-1:
    break

print(dict(rtnary))