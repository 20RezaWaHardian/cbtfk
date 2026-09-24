import cv2
import ctypes
import sys


namaFile=sys.argv[1]
#print("nama file: "+namaFile)


img = cv2.imread(namaFile)

face = cv2.CascadeClassifier('face-detect.xml')
eye = cv2.CascadeClassifier('eye-detect.xml')


gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

wajah = face.detectMultiScale(gray, 1.3, 5) 
for (x,y,w,h) in wajah: 
    cv2.rectangle(img, (x,y), (x+w, y+h), (0,255,0), 1)

    #cv_warna = img[y:y+h, x:x+w]
    #cv_gray = gray[y:y+h, x:x+w]
    
    
    
how_many_faces = str(len(wajah))
#ctypes.windll.user32.MessageBoxW(0, "Your text" +how_many_faces, "Your title", 1)

print(how_many_faces);
#cv2.imshow('Foto Normal', img)
cv2.waitKey(0)
cv2.destroyAllWindows()
