<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            {{-- For production, run `npm run build`. For development, run `npm run dev`. --}}
            {{-- Or, if a CDN is strictly preferred, replace @vite with the CDN link for TailwindCSS. --}}
        @endif
    </head>
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">
        <header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6 not-has-[nav]:hidden">
            @if (Route::has('login'))
                <nav class="flex items-center justify-end gap-4">
                    @auth
                        <a
                            href="{{ url('/dashboard') }}"
                            class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal"
                        >
                            Dashboard
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] text-[#1b1b18] border border-transparent hover:border-[#19140035] dark:hover:border-[#3E3E3A] rounded-sm text-sm leading-normal"
                        >
                            Log in
                        </a>

                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal">
                                Register
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>
        <div class="flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">
            <main class="flex max-w-[335px] w-full flex-col-reverse lg:max-w-4xl lg:flex-row">
                <div class="text-[13px] leading-[20px] flex-1 p-6 pb-12 lg:p-20 bg-white dark:bg-[#161615] dark:text-[#EDEDEC] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-bl-lg rounded-br-lg lg:rounded-tl-lg lg:rounded-br-none">
                    <h1 class="mb-4 text-2xl font-medium">Text-to-Speach App</h1>

                    <form id="text-to-speech-form" class="space-y-4">
                        <div class="">
                            <label for="text_input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Enter Text (English)</label>
                            <textarea id="text_input" name="text" rows="4" class="mt-1 p-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-[#1C1C1A] dark:border-[#3E3E3A] dark:text-[#EDEDEC]" placeholder="Type your text here..."></textarea>
                        </div>

                        <div class="">
                            <label for="language_select" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Language</label>
                            <select id="language_select" name="language" class="mt-1 p-3 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-[#1C1C1A] dark:border-[#3E3E3A] dark:text-[#EDEDEC]">
                                <option value="en">English (Default)</option>
                                <option value="es">Spanish</option>
                                <option value="fr">French</option>
                                <option value="de">German</option>
                                <option value="it">Italian</option>
                                <option value="ja">Japanese</option>
                                <option value="ar">Arabic</option>
                                <option value="hi">Hindi</option>
                            </select>
                        </div>

                        <button type="submit" class="w-full flex justify-center cursor-pointer py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Generate Speech
                        </button>
                    </form>

                    <div id="message" class="mt-4 text-sm font-medium text-red-600 dark:text-red-400"></div>

                    <div class="mt-6">
                        <h2 class="text-xl font-medium mb-2">Listen to Translation</h2>
                        <audio id="audio_player" controls class="w-full">
                            Your browser does not support the audio element.
                        </audio>
                    </div>
                </div>
                <div class="bg-[#fff2f2] dark:bg-[#1D0002] relative lg:-ml-px -mb-px lg:mb-0 rounded-t-lg lg:rounded-t-none lg:rounded-r-lg aspect-[335/376] lg:aspect-auto w-full lg:w-[438px] shrink-0 overflow-hidden">

                </div>
            </main>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
        <script>
            const form = document.getElementById('text-to-speech-form');
            const textInput = document.getElementById('text_input');
            const languageSelect = document.getElementById('language_select');
            const audioPlayer = document.getElementById('audio_player');
            const messageDiv = document.getElementById('message');

            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                messageDiv.textContent = ''; // Clear previous messages
                audioPlayer.src = ''; // Clear previous audio
                audioPlayer.pause();

                const text = textInput.value;
                const language = languageSelect.value;

                if (!text.trim()) {
                    messageDiv.textContent = 'Please enter some text.';
                    return;
                }

                try {
                    const response = await axios.post('/generate-speech', {
                        text: text,
                        language: language,
                    }, {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    if (response.data.audio) {
                        audioPlayer.src = 'data:audio/mp3;base64,' + response.data.audio.trim(); // Trim whitespace
                        audioPlayer.play();
                        messageDiv.textContent = 'Translated and speech generated successfully!';
                        messageDiv.className = 'mt-4 text-sm font-medium text-green-600 dark:text-green-400';
                    } else {
                        messageDiv.textContent = 'No audio content received.';
                        messageDiv.className = 'mt-4 text-sm font-medium text-red-600 dark:text-red-400';
                    }

                } catch (error) {
                    console.error('Error:', error);
                    if (error.response && error.response.data && error.response.data.error) {
                        messageDiv.textContent = 'Error: ' + error.response.data.error;
                    } else {
                        messageDiv.textContent = 'An unexpected error occurred. Please try again.';
                    }
                    messageDiv.className = 'mt-4 text-sm font-medium text-red-600 dark:text-red-400';
                }
            });
        </script>
    </body>
</html>
