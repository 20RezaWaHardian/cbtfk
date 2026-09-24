import cv2

img = cv2.imread('fahmi.PNG')

b, g, r = cv2.split(img)
img = cv2.merge((b, g, r))

x = img[:, :, 1]
cv2.imshow('image', x)
cv2.waitKey(0)
