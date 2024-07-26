import sys
import json
import os
from pdf2image import convert_from_path
import pytesseract
from PIL import Image, ImageDraw

def pdf_to_text(pdf_path, query):
    # Convert PDF pages to images
    images = convert_from_path(pdf_path, poppler_path=r'C:\xampp\htdocs\guardarDocs\poppler-24.02.0\Library\bin')
    matches = []

    for i, image in enumerate(images):
        # Extract text from image using Tesseract
        data = pytesseract.image_to_data(image, output_type=pytesseract.Output.DICT)
        found = False

        draw = ImageDraw.Draw(image)
        for j, word in enumerate(data['text']):
            if query.lower() in word.lower():
                # Get the bounding box coordinates
                x, y, w, h = data['left'][j], data['top'][j], data['width'][j], data['height'][j]
                # Draw a rectangle around the text
                draw.rectangle([x, y, x + w, y + h], outline="red", width=2)
                found = True
        
        if found:
            # Save the image with highlighted text
            temp_image_path = os.path.join(os.path.dirname(pdf_path), f"highlighted_page_{i+1}.png")
            image.save(temp_image_path)
            matches.append({
                "page": i + 1,
                "image_path": temp_image_path
            })

    return matches

if __name__ == "__main__":
    pdf_path = sys.argv[1]
    query = sys.argv[2]
    matches = pdf_to_text(pdf_path, query)
    print(json.dumps(matches))
