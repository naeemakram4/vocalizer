# Text-to-Speech Web App

This is a simple web application built with Laravel that allows users to enter English text, select a target language, translate the text, and then generate and play the speech for the translated text.

## Goal
The primary goal of this project is to demonstrate a basic understanding of full-stack web development, API integration, and deployment.

## Features
-   Enter text in English.
-   Select a target language from a dropdown (English, Spanish, French, German, Italian, Japanese).
-   Translate the text using the Google Cloud Translation API.
-   Generate speech for the translated text using the Google Cloud Text-to-Speech API.
-   Play the generated audio directly in the browser.
-   Basic error handling for API calls and user input.

## Technologies Used
-   **Backend:** Laravel (PHP Framework)
-   **Frontend:** HTML, TailwindCSS (for basic styling provided by Laravel's default setup), JavaScript (Axios for AJAX)
-   **APIs:**
    -   Google Cloud Translation API
    -   Google Cloud Text-to-Speech API

## Setup Instructions (Local Development)

Follow these steps to get the project running on your local machine.

### 1. Clone the repository
```bash
git clone <your-repo-link>
cd vocalizer
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Environment Configuration
Copy the `.env.example` file to `.env`:
```bash
cp .env.example .env
```

Generate an application key:
```bash
php artisan key:generate
```

### 4. Configure Google Cloud API Key
To use the translation and text-to-speech services, you need a Google Cloud API key. 

1.  Go to the [Google Cloud Console](https://console.cloud.google.com/).
2.  Create a new project or select an existing one.
3.  Navigate to "APIs & Services" > "Dashboard".
4.  Enable the "Cloud Translation API" and "Cloud Text-to-Speech API".
5.  Navigate to "APIs & Services" > "Credentials".
6.  Click "CREATE CREDENTIALS" > "API key".
7.  Copy the generated API key.

Once you have your API key, open your `.env` file and add the following line, replacing `YOUR_API_KEY_HERE` with your actual key:

```
GOOGLE_CLOUD_API_KEY=YOUR_API_KEY_HERE
```

**Security Note:** For production environments, it is highly recommended to restrict your API key to only allow requests from your deployed application's URL (HTTP referrers) and/or by IP address to prevent unauthorized use.

### 5. Run Database Migrations (if applicable)
Although this project doesn't currently use a database for its core functionality, a typical Laravel setup involves migrations. If you add features requiring a database (e.g., translation history), you would run:
```bash
php artisan migrate
```

### 6. Install Node Dependencies & Compile Assets
```bash
npm install
npm run dev
# or for production assets:
npm run build
```

### 7. Start the Laravel Development Server
```bash
php artisan serve
```

Open your browser and visit `http://127.0.0.1:8000` (or the address provided in your terminal).

## How to Use
1.  Enter the English text you wish to translate and convert to speech in the textarea.
2.  Select your desired target language from the dropdown.
3.  Click the "Generate Speech" button.
4.  The translated text will be processed, and the generated audio will play automatically in the audio player.
5.  Any messages or errors will be displayed below the form.

## Deployment
To deploy this application, you will need a hosting environment that supports PHP and Laravel (e.g., a VPS, shared hosting with cPanel, Heroku, DigitalOcean App Platform, Render, etc.). The specific steps will vary based on your chosen platform, but generally involve:
1.  Uploading your project files.
2.  Configuring your web server (Nginx/Apache) to point to the `public` directory.
3.  Setting up environment variables (especially `APP_ENV`, `APP_DEBUG`, `APP_KEY`, and `GOOGLE_CLOUD_API_KEY`).
4.  Running `composer install --no-dev` and `php artisan optimize` (or `php artisan config:cache` and `php artisan route:cache`).
5.  Running `npm run build` to compile frontend assets for production.

## Bonus / Extra Features (Ideas for further development)
-   Option to download the generated audio file.
-   Change voice settings (e.g., male/female, pitch, speed, or different TTS voices).
-   Show a history of past translations/speeches stored in MySQL.
-   Enhance UI with more advanced CSS frameworks or custom designs.
-   More robust error handling and user feedback.
