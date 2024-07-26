import sys
import json
import traceback
import logging
import cv2
import numpy as np
import os
import pytesseract
from difflib import SequenceMatcher
from PIL import Image

log_path = 'C:/xampp/htdocs/guardarDocs/python_script.log'
logging.basicConfig(filename=log_path, level=logging.DEBUG, 
                    format='%(asctime)s - %(levelname)s - %(message)s')

pytesseract.pytesseract.tesseract_cmd = r'C:\Program Files\Tesseract-OCR\tesseract.exe'

def preprocess_image(image):
    gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
    blurred = cv2.GaussianBlur(gray, (5, 5), 0)
    thresh = cv2.adaptiveThreshold(blurred, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C, cv2.THRESH_BINARY, 11, 2)
    kernel = np.ones((1, 1), np.uint8)
    img = cv2.dilate(thresh, kernel, iterations=1)
    img = cv2.erode(img, kernel, iterations=1)
    return img

def fuzzy_search(text, query, threshold=0.8):
    words = text.split()
    for word in words:
        if SequenceMatcher(None, word.lower(), query.lower()).ratio() > threshold:
            return True
    return False

def search_pdf(pdf_path, query):
    print(f"Buscando en PDF: {pdf_path}")
    print(f"Query de búsqueda: {query}")
    logging.debug(f"Searching PDF: {pdf_path} for query: {query}")
    try:
        image = cv2.imread(pdf_path)
        if image is None:
            print(f"No se pudo leer la imagen: {pdf_path}")
            raise ValueError(f"No se pudo leer la imagen: {pdf_path}")
        
        preprocessed = preprocess_image(image)
        
        custom_config = r'--oem 3 --psm 6'
        text = pytesseract.image_to_string(preprocessed, config=custom_config)
        
        print(f"Texto extraído (primeros 100 caracteres): {text[:100]}")
        
        found = fuzzy_search(text, query)
        
        print(f"¿Se encontró la query? {found}")
        logging.debug(f"Search complete. Query found: {found}")
        return {"found": found, "text": text[:100]}
    except Exception as e:
        print(f"Error en search_pdf: {str(e)}")
        logging.error(f"Error in search_pdf: {str(e)}")
        logging.error(traceback.format_exc())
        return {"error": str(e), "traceback": traceback.format_exc()}

if __name__ == "__main__":
    print(f"Script iniciado con argumentos: {sys.argv}")
    logging.debug(f"Script started with arguments: {sys.argv}")
    try:
        if len(sys.argv) != 3:
            raise ValueError("Número incorrecto de argumentos. Uso: script.py <pdf_path> <query>")
        
        pdf_path = sys.argv[1]
        query = sys.argv[2]
        
        print(f"Ruta del PDF: {pdf_path}")
        print(f"Query de búsqueda: {query}")
        
        if not os.path.exists(pdf_path):
            print(f"Archivo PDF no encontrado: {pdf_path}")
            raise FileNotFoundError(f"PDF file not found: {pdf_path}")
        
        result = search_pdf(pdf_path, query)
        print(f"Resultado de la búsqueda: {json.dumps(result)}")
        print(json.dumps(result))
        sys.stdout.flush()
    except Exception as e:
        print(f"Error en main: {str(e)}")
        logging.error(f"Error in main: {str(e)}")
        logging.error(traceback.format_exc())
        print(json.dumps({"error": str(e), "traceback": traceback.format_exc()}))
        sys.stdout.flush()
    finally:
        print("Script finalizado")
        logging.debug("Script finished")