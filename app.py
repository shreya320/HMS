from flask import Flask, request, jsonify, redirect, url_for# type: ignore
from werkzeug.utils import secure_filename# type: ignore
import os
from PIL import Image# type: ignore
import numpy as np# type: ignore
import tensorflow as tf  # Or your ML framework# type: ignore
from flask_cors import CORS  # Import CORS# type: ignore
from sklearn.preprocessing import LabelEncoder  # type: ignore # Import LabelEncoder

app = Flask(__name__)
CORS(app)  # Enable CORS

# --- Configuration ---
UPLOAD_FOLDER = 'uploads'
app.config['UPLOAD_FOLDER'] = UPLOAD_FOLDER
os.makedirs(UPLOAD_FOLDER, exist_ok=True)  # Create upload directory if it doesn't exist

MODEL_PATH = 'skin_disease_model.h5'  # Path to your trained model
try:
    model = tf.keras.models.load_model(MODEL_PATH)
    print("Model loaded successfully from:", MODEL_PATH)  # Add logging
except Exception as e:
    print(f"Error loading model from {MODEL_PATH}: {e}")
    model = None  # Set model to None to indicate it failed to load

CLASS_NAMES = ['acne', 'hyperpigmentation', 'Nail_psoriasis', 'SJS_TEN', 'Vitiligo']  # Replace with your actual class names
# Or Load from label encoder
LABEL_ENCODER_PATH = 'label_encoder.pkl'  # Path to your saved LabelEncoder (if you have one)
label_encoder = None
if os.path.exists(LABEL_ENCODER_PATH):
    import pickle
    try:
        with open(LABEL_ENCODER_PATH, 'rb') as f:
            label_encoder = pickle.load(f)
        print("Label encoder loaded successfully from:", LABEL_ENCODER_PATH)
    except Exception as e:
        print(f"Error loading label encoder from {LABEL_ENCODER_PATH}: {e}")


# --- Prediction Function ---
def predict_disease(image_path, model, image_size=(224, 224), class_names=None, label_encoder=None):
    """Predicts the skin disease from an image.

    Args:
        image_path (str): Path to the image file.
        model (tensorflow.keras.models.Sequential): The trained CNN model.
        image_size (tuple): Desired image size (width, height).
        class_names (list): List of skin disease class names (optional - for display).
        label_encoder (LabelEncoder): LabelEncoder object used during training.

    Returns:
        str: Predicted disease name.
    """
    try:
        img = Image.open(image_path).convert('RGB')
        img = img.resize(image_size)
        img = np.array(img)
        img = img.astype('float32') / 255.0
        img = np.expand_dims(img, axis=0)

        prediction = model.predict(img)
        predicted_class_index = np.argmax(prediction)

        if class_names:
            predicted_disease = class_names[predicted_class_index]
        elif label_encoder:
            predicted_disease = label_encoder.inverse_transform([predicted_class_index])[0]
        else:
            predicted_disease = f"Class {predicted_class_index}"

        return predicted_disease
    except Exception as e:
        return f"Error during prediction: {e}"

# --- API Endpoint ---
@app.route('/predict', methods=['POST'])
def predict_api():
    if model is None:
        return jsonify({'error': 'Model not loaded'}), 500  # Check if model is loaded

    if 'file' not in request.files:
        return jsonify({'error': 'No file part'}), 400

    file = request.files['file']
    if file.filename == '':
        return jsonify({'error': 'No selected file'}), 400

    try:
        filename = secure_filename(file.filename)
        filepath = os.path.join(app.config['UPLOAD_FOLDER'], filename)
        file.save(filepath)

        predicted_disease = predict_disease(filepath, model, class_names=CLASS_NAMES,
                                              label_encoder=label_encoder)  # Pass class names

        os.remove(filepath)  # Clean up the image after prediction

        return jsonify({'prediction': predicted_disease})

    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/')  # Add this route
def index():
    return "Skin Disease Detector API is running. Access /predict to make predictions."

# --- Run the app ---
if __name__ == '__main__':
    app.run(debug=True, port=5000)