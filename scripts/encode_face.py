import sys
import face_recognition
import numpy as np
import json

def encode_face(image_path):
    image = face_recognition.load_image_file(image_path)
    encodings = face_recognition.face_encodings(image)

    if len(encodings) > 0:
        encoding = encodings[0]
        return json.dumps(encoding.tolist())  # Convertit l'encodage en JSON
    return None

if __name__ == "__main__":
    image_path = sys.argv[1]
    encoding = encode_face(image_path)
    if encoding:
        print(encoding)  # Retourne l'encodage pour l'utiliser dans Symfony
    else:
        print("")