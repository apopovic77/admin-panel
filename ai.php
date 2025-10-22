<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - AI Tools</title>
    <style>
        :root {
            --text: #1e293b; --muted: #475569; --brand: #1f2937; --brand-2: #8B9DC3;
            --ring: rgba(148, 163, 184, .3); --surface: rgba(255, 255, 255, 0.98);
            --background-gradient: linear-gradient(to bottom, #ffffff, #f8fafc, #e2e8f0);
            --radius-lg: 16px; --radius-md: 12px; --radius-sm: 8px;
            --shadow-primary: 0 10px 30px rgba(0, 0, 0, .07); --gap: 24px;
            --font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Inter, Roboto, system-ui, sans-serif;
            --h2-size: clamp(22px, 3.6vw, 34px); --kicker-size: 12px;
            --success-color: #d4edda; --error-color: #f8d7da;
        }
        body { 
            font-family: var(--font-family); margin: 0; background: var(--background-gradient);
            color: var(--text); padding: var(--gap); min-height: 100vh;
        }
        .wrapper { max-width: 1200px; margin: 0 auto; }
        h1 {
            font-size: clamp(34px, 6.2vw, 76px); font-weight: 700;
            padding-top: 32px; margin-bottom: 64px; text-align: center;
        }
        h2 {
            font-size: var(--h2-size); font-weight: 600; margin-bottom: var(--gap);
            border-bottom: 1px solid var(--ring); padding-bottom: 16px;
        }
        .container { 
            display: grid; grid-template-columns: 1fr 1fr; 
            gap: var(--gap);
        }
        .card { 
            background: var(--surface); border: 1px solid var(--ring);
            border-radius: var(--radius-lg); padding: var(--gap);
            box-shadow: var(--shadow-primary);
        }
        textarea, input[type="file"] { 
            width: 100%; padding: 12px; margin-bottom: 16px; border: 1px solid var(--ring); 
            border-radius: var(--radius-sm); box-sizing: border-box; background-color: #f8fafc;
            font-family: var(--font-family); transition: border-color 0.2s ease;
        }
        textarea:focus, input[type="file"]:focus {
            border-color: var(--brand-2); outline: none;
            box-shadow: 0 0 0 3px rgba(139, 157, 195, 0.1);
        }
        label {
            display: block; margin-bottom: 8px; font-weight: 600;
            color: var(--text); font-size: var(--kicker-size);
            text-transform: uppercase; letter-spacing: .08em;
        }
        button { 
            padding: 12px 20px; border: none; border-radius: var(--radius-sm); 
            cursor: pointer; background-color: var(--brand-2); color: white; 
            margin-right: 8px; margin-bottom: 8px; font-weight: 600;
            transition: all 0.2s ease;
        }
        button:hover { background-color: var(--brand); transform: translateY(-1px); }
        button:disabled { background-color: #a0aec0; cursor: not-allowed; transform: none; }
        .response-area { 
            margin-top: var(--gap); padding: var(--gap); background: #f8fafc; 
            border: 1px solid var(--ring); border-radius: var(--radius-sm); 
            white-space: pre-wrap; font-family: 'SF Mono', monospace; 
            min-height: 100px; max-height: 400px; overflow-y: auto;
        }
        #depth-map-result-image { 
            max-width: 100%; margin-top: var(--gap); 
            border: 1px solid var(--ring); border-radius: var(--radius-sm);
        }
        .btn-group { 
            display: flex; flex-wrap: wrap; gap: 8px; 
            margin-top: 16px; margin-bottom: 16px;
        }
        
        /* Mobile Responsive Design */
        @media (max-width: 768px) {
            body { 
                padding: 16px; 
            }
            
            .wrapper { 
                max-width: 100%; 
                padding: 0; 
            }
            
            h1 { 
                font-size: clamp(24px, 8vw, 32px); 
                padding-top: 16px; 
                margin-bottom: 32px; 
            }
            
            .container { 
                grid-template-columns: 1fr; 
                gap: 20px; 
            }
            
            .card { 
                padding: 20px; 
                margin-bottom: 20px; 
            }
            
            h2 { 
                font-size: clamp(18px, 5vw, 24px); 
                margin-bottom: 16px; 
            }
            
            textarea { 
                height: 100px; 
                font-size: 14px; 
                padding: 10px; 
            }
            
            input[type="file"] { 
                padding: 10px; 
                font-size: 14px; 
            }
            
            label { 
                font-size: 11px; 
            }
            
            .btn-group { 
                flex-direction: column; 
                gap: 12px; 
            }
            
            button { 
                padding: 14px 20px; 
                font-size: 16px; 
                width: 100%; 
                min-height: 44px; 
            }
            
            .response-area { 
                font-size: 12px; 
                padding: 16px; 
                min-height: 80px; 
                max-height: 300px; 
            }
            
            #depth-map-result-image { 
                width: 100%; 
                height: auto; 
            }
        }
        
        @media (max-width: 480px) {
            body { 
                padding: 12px; 
            }
            
            h1 { 
                font-size: clamp(20px, 10vw, 28px); 
            }
            
            .card { 
                padding: 16px; 
            }
            
            h2 { 
                font-size: clamp(16px, 6vw, 20px); 
                margin-bottom: 12px; 
            }
            
            textarea { 
                height: 80px; 
                font-size: 13px; 
                padding: 8px; 
            }
            
            input[type="file"] { 
                padding: 8px; 
                font-size: 13px; 
            }
            
            button { 
                padding: 12px 16px; 
                font-size: 14px; 
            }
            
            .response-area { 
                font-size: 11px; 
                padding: 12px; 
                min-height: 60px; 
                max-height: 250px; 
            }
        }
        
        /* Touch-friendly improvements */
        @media (hover: none) and (pointer: coarse) {
            button { 
                min-height: 44px; 
                padding: 14px 20px; 
            }
            
            button:hover { 
                transform: none; 
            }
            
            textarea:focus, input[type="file"]:focus { 
                font-size: 16px; /* Prevent zoom on iOS */ 
            }
        }
        
        /* Landscape orientation adjustments */
        @media (max-width: 768px) and (orientation: landscape) {
            .container { 
                grid-template-columns: 1fr 1fr; 
            }
            
            textarea { 
                height: 60px; 
            }
            
            .response-area { 
                max-height: 200px; 
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'menu.php'; ?>

        <h1>AI Tools</h1>

        <div class="container">
        <div class="card">
            <h2>General AI Models</h2>
            <textarea id="prompt-text" placeholder="Enter your text prompt here..."></textarea>
            <label for="prompt-image">Optional Image:</label>
            <input type="file" id="prompt-image" accept="image/*">

            <label for="general-provider">Provider</label>
            <select id="general-provider" style="width: 100%; padding: 12px; margin-bottom: 16px; border: 1px solid var(--ring); border-radius: var(--radius-sm);">
                <option value="chatgpt" selected>ChatGPT</option>
                <option value="claude">Claude</option>
                <option value="gemini">Gemini</option>
            </select>

            <div id="openai-model-container" style="display:block;">
                <label for="openai-model">OpenAI Model</label>
                <select id="openai-model" style="width: 100%; padding: 12px; margin-bottom: 16px; border: 1px solid var(--ring); border-radius: var(--radius-sm);">
                    <option value="gpt-4o" selected>gpt-4o</option>
                    <option value="gpt-4.1">gpt-4.1</option>
                    <option value="gpt-4.1-mini">gpt-4.1-mini</option>
                    <option value="o4-mini">o4-mini</option>
                    <option value="gpt-4o-mini">gpt-4o-mini</option>
                    <option value="gpt-3.5-turbo">gpt-3.5-turbo</option>
                </select>
            </div>

            <div id="gemini-model-container" style="display:none;">
                <label for="gemini-model">Gemini Model</label>
                <select id="gemini-model" style="width: 100%; padding: 12px; margin-bottom: 16px; border: 1px solid var(--ring); border-radius: var(--radius-sm);">
                    <option value="gemini-1.5-flash">gemini-1.5-flash</option>
                    <option value="gemini-1.5-pro">gemini-1.5-pro</option>
                    <option value="gemini-2.0-flash">gemini-2.0-flash</option>
                    <option value="gemini-2.5-pro" selected>gemini-2.5-pro</option>
                    <option value="gemini-2.5-flash">gemini-2.5-flash</option>
                </select>
            </div>

            <div class="btn-group">
                <button onclick="sendGeneralAi()">Send</button>
            </div>

            <div id="ai-response" class="response-area"></div>
        </div>

        <div class="card">
            <h2>Depth Map Generation</h2>
            <label for="depth-image">Image for Depth Map:</label>
            <input type="file" id="depth-image" accept="image/*" required>
            <button id="start-depth-job-btn" onclick="startDepthJob()">Start Generation</button>

            <div id="depth-job-status" class="response-area"></div>
            
            <div>
                <img id="depth-map-result-image" src="" alt="Depth Map Result" style="display:none;">
            </div>
        </div>
        </div>

        <div class="card">
            <h2>Text-to-Speech (TTS) Generation</h2>
            <form id="tts-form">
                <label for="tts-text">Text to Synthesize:</label>
                <textarea id="tts-text" rows="4" placeholder="Enter the text you want to convert to speech..."></textarea>

                <label for="tts-provider">Provider:</label>
                <select id="tts-provider" style="width: 100%; padding: 12px; margin-bottom: 16px; border: 1px solid var(--ring); border-radius: var(--radius-sm);">
                    <option value="openai" selected>OpenAI</option>
                    <option value="gemini">Gemini</option>
                    <option value="elevenlabs">ElevenLabs</option>
                </select>

                <div id="openai-voice-container">
                    <label for="tts-voice-openai">OpenAI Voice:</label>
                    <select id="tts-voice-openai" style="width: 100%; padding: 12px; margin-bottom: 16px; border: 1px solid var(--ring); border-radius: var(--radius-sm);">
                        <option value="alloy">Alloy</option>
                        <option value="echo">Echo</option>
                        <option value="fable">Fable</option>
                        <option value="onyx">Onyx</option>
                        <option value="nova" selected>Nova</option>
                        <option value="shimmer">Shimmer</option>
                    </select>
                </div>

                <div id="gemini-voice-container" style="display: none;">
                    <label for="tts-voice-gemini">Google WaveNet Voice:</label>
                    <select id="tts-voice-gemini" style="width: 100%; padding: 12px; margin-bottom: 16px; border: 1px solid var(--ring); border-radius: var(--radius-sm);">
                        <option value="de-DE-Wavenet-F" selected>German - Female F (WaveNet)</option>
                        <option value="de-DE-Wavenet-A">German - Male A (WaveNet)</option>
                        <option value="de-DE-Wavenet-B">German - Male B (WaveNet)</option>
                        <option value="de-DE-Wavenet-C">German - Female C (WaveNet)</option>
                        <option value="de-DE-Wavenet-D">German - Male D (WaveNet)</option>
                        <option value="de-DE-Wavenet-E">German - Female E (WaveNet)</option>
                        <option value="de-DE-Standard-F">German - Female F (Standard)</option>
                         <option value="de-DE-Standard-A">German - Male A (Standard)</option>
                    </select>
                    <label for="tts-pitch">Pitch:</label>
                    <input type="range" id="tts-pitch" min="-20.0" max="20.0" value="0.0" step="0.5" style="width: 100%; margin-bottom: 16px;">
                </div>

                <div id="elevenlabs-voice-container" style="display: none;">
                    <label for="tts-voice-elevenlabs">ElevenLabs Voice ID:</label>
                    <input type="text" id="tts-voice-elevenlabs" value="JBFqnCBsd6RMkjVDRZzb" style="width: 100%; padding: 12px; margin-bottom: 16px; border: 1px solid var(--ring); border-radius: var(--radius-sm);">
                    <label for="tts-stability">Stability:</label>
                    <input type="range" id="tts-stability" min="0.0" max="1.0" value="0.5" step="0.05" style="width: 100%; margin-bottom: 16px;">
                    <label for="tts-clarity">Clarity + Similarity Boost:</label>
                    <input type="range" id="tts-clarity" min="0.0" max="1.0" value="0.75" step="0.05" style="width: 100%; margin-bottom: 16px;">
                </div>
                <label for="tts-collection-name">Collection Name (optional):</label>
                <input type="text" id="tts-collection-name" placeholder="Default: ai_hörbuch" style="width: 100%; padding: 12px; margin-bottom: 16px; border: 1px solid var(--ring); border-radius: var(--radius-sm);">
                
                <label for="tts-speed">Speed:</label>
                <input type="range" id="tts-speed" min="0.25" max="4.0" value="1.0" step="0.05" style="width: 100%; margin-bottom: 16px;">

                <button type="button" id="generate-speech-btn" onclick="generateSpeech()">Generate Audio</button>
            </form>

            <div id="tts-response" class="response-area"></div>
            <audio id="tts-audio-player" controls style="display:none; width: 100%; margin-top: 16px;"></audio>
        </div>

        

        <div class="card">
            <h2>Image Generation</h2>
            <form id="image-gen-form">
                <label for="image-gen-prompt">Image Prompt:</label>
                <textarea id="image-gen-prompt" rows="4" placeholder="Enter a descriptive prompt for the image..."></textarea>
                <button type="button" id="generate-image-btn" onclick="generateImage()">Generate Image</button>
            </form>
            <div id="image-gen-response" class="response-area"></div>
            <img id="image-gen-result" src="" alt="Generated Image" style="display:none; max-width: 100%; margin-top: 16px; border-radius: var(--radius-sm);">
        </div>

        <div class="card">
            <h2>Image Upscale (Stability)</h2>
            <form id="upscale-form">
                <label for="upscale-image">Image to Upscale:</label>
                <input type="file" id="upscale-image" accept="image/*" required>
                <label for="upscale-format">Output Format:</label>
                <select id="upscale-format" style="width: 100%; padding: 12px; margin-bottom: 16px; border: 1px solid var(--ring); border-radius: var(--radius-sm);">
                    <option value="webp" selected>WEBP</option>
                    <option value="png">PNG</option>
                    <option value="jpeg">JPEG</option>
                </select>
                <button type="button" id="upscale-btn" onclick="upscaleImage()">Upscale</button>
            </form>
            <div id="upscale-response" class="response-area"></div>
            <img id="upscale-result" src="" alt="Upscaled Image" style="display:none; max-width: 100%; margin-top: 16px; border-radius: var(--radius-sm);">
        </div>

        <div class="card">
            <h2>Sound Effect Generation (ElevenLabs)</h2>
            <form id="sfx-gen-form">
                <label for="sfx-gen-prompt">SFX Prompt:</label>
                <textarea id="sfx-gen-prompt" rows="2" placeholder="e.g., a car driving by, wind howling"></textarea>
                <button type="button" id="generate-sfx-btn" onclick="generateSfx()">Generate SFX</button>
            </form>
            <div id="sfx-gen-response" class="response-area"></div>
            <audio id="sfx-audio-player" controls style="display:none; width: 100%; margin-top: 16px;"></audio>
        </div>

        <div class="card">
            <h2>Music Generation (Pixabay)</h2>
            <form id="music-gen-form">
                <label for="music-gen-prompt">Music Prompt:</label>
                <textarea id="music-gen-prompt" rows="2" placeholder="e.g., mysterious ambient, dramatic rising score"></textarea>
                <button type="button" id="generate-music-btn" onclick="generateMusic()">Generate Music</button>
            </form>
            <div id="music-gen-response" class="response-area"></div>
            <audio id="music-audio-player" controls style="display:none; width: 100%; margin-top: 16px;"></audio>
        </div>
        </div>
    </div>

<script>
const API_BASE_URL = 'https://api.arkturian.com';
const API_KEY = 'Inetpass1'; // Hardcoded API key

// --- General AI Functions ---
const promptText = document.getElementById('prompt-text');
const promptImage = document.getElementById('prompt-image');
const aiResponseArea = document.getElementById('ai-response');

function fileToBase64(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = () => resolve(reader.result);
        reader.onerror = error => reject(error);
    });
}

// Backward-compatible function; will read from selects if no param
async function callAi(model) {
    aiResponseArea.textContent = `Calling ${model}...`;
    let imageB64 = null;
    if (promptImage.files.length > 0) {
        imageB64 = await fileToBase64(promptImage.files[0]);
    }

    try {
        const url = `${API_BASE_URL}/ai/${model}`;
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-API-KEY': API_KEY
            },
            body: JSON.stringify(
                model === 'gemini'
                  ? { prompt: { text: promptText.value, images: imageB64 ? [imageB64] : [] } }
                  : { text: promptText.value, images: imageB64 ? [imageB64] : [] }
            )
        });

        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        const data = await response.json();
        aiResponseArea.textContent = JSON.stringify(data, null, 2);

    } catch (error) {
        console.error(`Error calling ${model}:`, error);
        aiResponseArea.textContent = `Error: ${error.message}`;
    }
}

// New: Send based on selection lists
document.getElementById('general-provider').addEventListener('change', () => {
    const provider = document.getElementById('general-provider').value;
    document.getElementById('gemini-model-container').style.display = provider === 'gemini' ? 'block' : 'none';
    document.getElementById('openai-model-container').style.display = provider === 'chatgpt' ? 'block' : 'none';
});

async function sendGeneralAi() {
    const provider = document.getElementById('general-provider').value;
    const geminiAlias = document.getElementById('gemini-model').value;
    const openaiAlias = document.getElementById('openai-model').value;
    const chosenAlias = provider === 'gemini' ? geminiAlias : (provider === 'chatgpt' ? openaiAlias : null);
    aiResponseArea.textContent = `Calling ${provider}${chosenAlias ? ' ('+chosenAlias+')' : ''}...`;

    let imageB64 = null;
    if (promptImage.files.length > 0) {
        imageB64 = await fileToBase64(promptImage.files[0]);
    }

    try {
        let url = `${API_BASE_URL}/ai/${provider}`;
        if (chosenAlias && (provider === 'gemini' || provider === 'chatgpt')) {
            url += `?model=${encodeURIComponent(chosenAlias)}`;
        }

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-API-KEY': API_KEY
            },
            body: JSON.stringify(
                provider === 'gemini'
                  ? { prompt: { text: promptText.value, images: imageB64 ? [imageB64] : [] } }
                  : { text: promptText.value, images: imageB64 ? [imageB64] : [] }
            )
        });

        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        const data = await response.json();
        aiResponseArea.textContent = JSON.stringify(data, null, 2);
    } catch (error) {
        console.error(`Error calling ${provider}:`, error);
        aiResponseArea.textContent = `Error: ${error.message}`;
    }
}


// --- Depth Map Functions ---
const depthImage = document.getElementById('depth-image');
const depthJobStatus = document.getElementById('depth-job-status');
const startDepthJobBtn = document.getElementById('start-depth-job-btn');
const resultImage = document.getElementById('depth-map-result-image');
let pollingInterval = null;

async function startDepthJob() {
    if (depthImage.files.length === 0) {
        depthJobStatus.textContent = 'Please select an image first.';
        return;
    }
    
    startDepthJobBtn.disabled = true;
    depthJobStatus.textContent = 'Uploading and starting job...';
    resultImage.style.display = 'none';
    if (pollingInterval) clearInterval(pollingInterval);

    const imageB64 = await fileToBase64(depthImage.files[0]);

    try {
        const response = await fetch(`${API_BASE_URL}/ai_gendepth`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-API-KEY': API_KEY
            },
            body: JSON.stringify({ image: imageB64 })
        });

        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        const data = await response.json();
        
        depthJobStatus.innerHTML = `Job started with ID: <strong>${data.job_id}</strong><br>Polling for result... <button onclick="pollForResult('${data.job_id}')">Check Now</button>`;
        
        // Start polling automatically
        pollingInterval = setInterval(() => pollForResult(data.job_id), 5000);

    } catch (error) {
        console.error('Error starting depth job:', error);
        depthJobStatus.textContent = `Error: ${error.message}`;
    } finally {
        startDepthJobBtn.disabled = false;
    }
}

async function pollForResult(jobId) {
    depthJobStatus.innerHTML = `Job started with ID: <strong>${jobId}</strong><br>Polling for result... <button onclick="pollForResult('${jobId}')">Check Now</button>`;

    try {
        const response = await fetch(`${API_BASE_URL}/ai_gendepth_result/${jobId}`, {
            headers: { 'X-API-KEY': API_KEY }
        });

        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        const data = await response.json();

        depthJobStatus.innerHTML = `Job ID: <strong>${jobId}</strong><br>Status: <strong>${data.status}</strong> <button onclick="pollForResult('${jobId}')">Check Now</button>`;

        if (data.status === 'completed') {
            if (pollingInterval) clearInterval(pollingInterval);
            depthJobStatus.innerHTML += '<br><strong>Result received!</strong>';
            // Assuming the result structure contains the image URL or base64 data
            if (data.result && data.result.output && data.result.output.image) {
                resultImage.src = data.result.output.image;
                resultImage.style.display = 'block';
            } else {
                 depthJobStatus.innerHTML += '<br>Could not find image in result.';
                 console.log('Full result:', data.result);
            }
        }
    } catch (error) {
        console.error('Error polling for result:', error);
        depthJobStatus.innerHTML += `<br>Error polling: ${error.message}`;
    }
}

// --- TTS Generation Functions ---
const ttsText = document.getElementById('tts-text');
const ttsProvider = document.getElementById('tts-provider');
const ttsVoiceOpenAI = document.getElementById('tts-voice-openai');
const ttsSpeed = document.getElementById('tts-speed');
const generateSpeechBtn = document.getElementById('generate-speech-btn');
const ttsResponseArea = document.getElementById('tts-response');
const ttsAudioPlayer = document.getElementById('tts-audio-player');
const openAIVoiceContainer = document.getElementById('openai-voice-container');
const geminiVoiceContainer = document.getElementById('gemini-voice-container');
const ttsVoiceGemini = document.getElementById('tts-voice-gemini');
const ttsPitch = document.getElementById('tts-pitch');
const elevenlabsVoiceContainer = document.getElementById('elevenlabs-voice-container');
const ttsVoiceElevenLabs = document.getElementById('tts-voice-elevenlabs');
const ttsStability = document.getElementById('tts-stability');
const ttsClarity = document.getElementById('tts-clarity');

// Event listener to toggle UI elements based on provider
ttsProvider.addEventListener('change', () => {
    openAIVoiceContainer.style.display = 'none';
    geminiVoiceContainer.style.display = 'none';
    elevenlabsVoiceContainer.style.display = 'none';

    if (ttsProvider.value === 'openai') {
        openAIVoiceContainer.style.display = 'block';
    } else if (ttsProvider.value === 'gemini') {
        geminiVoiceContainer.style.display = 'block';
    } else if (ttsProvider.value === 'elevenlabs') {
        elevenlabsVoiceContainer.style.display = 'block';
    }
});

async function generateSpeech() {
    if (!ttsText.value.trim()) {
        ttsResponseArea.textContent = 'Please enter some text to synthesize.';
        return;
    }

    generateSpeechBtn.disabled = true;
    ttsResponseArea.textContent = 'Generating audio...';
    ttsAudioPlayer.style.display = 'none';

    const provider = ttsProvider.value;
    let voice = '';
    if (provider === 'openai') {
        voice = ttsVoiceOpenAI.value;
    } else if (provider === 'gemini') {
        voice = ttsVoiceGemini.value;
    } else if (provider === 'elevenlabs') {
        voice = ttsVoiceElevenLabs.value;
    }
    
    const pitch = provider === 'gemini' ? parseFloat(ttsPitch.value) : 0.0;
    const stability = provider === 'elevenlabs' ? parseFloat(ttsStability.value) : null;
    const clarity = provider === 'elevenlabs' ? parseFloat(ttsClarity.value) : null;

    const collectionNameTts = (document.getElementById('tts-collection-name')?.value || '').trim();
    const payload = {
        id: `tts-test-${Date.now()}`,
        timestamp: new Date().toISOString(),
        content: {
            text: ttsText.value,
            language: "de-DE", // Note: ElevenLabs auto-detects language
            voice: voice,
            pitch: pitch,
            speed: parseFloat(ttsSpeed.value), // Note: ElevenLabs does not have a speed parameter
            stability: stability,
            clarity: clarity
        },
        config: {
            provider: provider,
            output_format: "mp3"
        },
        save_options: {
            is_public: true,
            collection_id: collectionNameTts || "ai_hörbuch",
            owner_email: "apopovic.aut@gmail.com"
        }
    };

    try {
        const response = await fetch(`${API_BASE_URL}/ai/generate_speech`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-API-KEY': API_KEY
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.detail || `HTTP error! status: ${response.status}`);
        }
        
        ttsResponseArea.textContent = JSON.stringify(data, null, 2);
        
        if (data.file_url) {
            ttsAudioPlayer.src = data.file_url;
            ttsAudioPlayer.style.display = 'block';
            ttsAudioPlayer.play();
        }

        // Additionally upload the entered text to storage with the same collection and link_id
        try {
            const collectionId = payload.save_options && payload.save_options.collection_id ? payload.save_options.collection_id : "ai_hörbuch";
            await uploadTextToStorage(collectionId, payload.id, ttsText.value, `tts_text_${payload.id}.txt`);
        } catch (e) {
            console.warn('Uploading TTS source text failed:', e);
        }

    } catch (error) {
        console.error('Error generating speech:', error);
        ttsResponseArea.textContent = `Error: ${error.message}`;
    } finally {
        generateSpeechBtn.disabled = false;
    }
}

async function uploadTextToStorage(collectionId, linkId, text, filename) {
    const form = new FormData();
    const blob = new Blob([text || ''], { type: 'text/plain' });
    const file = new File([blob], filename || `text_${Date.now()}.txt`, { type: 'text/plain' });
    form.append('file', file);
    form.append('is_public', true);
    form.append('owner_email', 'apopovic.aut@gmail.com');
    if (collectionId) form.append('collection_id', collectionId);
    if (linkId) form.append('link_id', linkId);

    const res = await fetch(`${API_BASE_URL}/storage/upload`, {
        method: 'POST',
        headers: { 'X-API-KEY': API_KEY },
        body: form
    });
    if (!res.ok) {
        const txt = await res.text();
        throw new Error(`Upload text failed (${res.status}): ${txt}`);
    }
}

// --- Dialog Generation Functions ---
const dialogText = document.getElementById('dialog-text');
const dialogVoiceMapping = document.getElementById('dialog-voice-mapping');
const generateDialogBtn = document.getElementById('generate-dialog-btn');
const dialogResponseArea = document.getElementById('dialog-response');
const dialogAudioPlayer = document.getElementById('dialog-audio-player');
const dialogPromptDisplay = document.getElementById('dialog-prompt-display');
const dialogAnalysisResult = document.getElementById('dialog-analysis-result');
const dialogSfxResult = document.getElementById('dialog-sfx-result');
const dialogAiMaleVoice = document.getElementById('dialog-ai-male-voice');
const dialogGenerateImage = document.getElementById('dialog-generate-image');
const dialogAddSfx = document.getElementById('dialog-add-sfx');
const dialogAddMusic = document.getElementById('dialog-add-music');
// Inject clean music controls: None / Generate / Use existing (with ID)
if (!document.getElementById('dialog-music-controls')) {
    const anchor = document.getElementById('dialog-add-music');
    if (anchor && anchor.parentElement) {
        // Hide the legacy checkbox label to avoid UX confusion
        anchor.parentElement.style.display = 'none';

        const wrap = document.createElement('div');
        wrap.id = 'dialog-music-controls';
        wrap.style.marginTop = '8px';
        wrap.style.padding = '8px 10px';
        wrap.style.border = '1px solid #e0e0e0';
        wrap.style.borderRadius = '6px';

        const legend = document.createElement('div');
        legend.textContent = 'Music';
        legend.style.fontWeight = '600';
        legend.style.marginBottom = '6px';
        wrap.appendChild(legend);

        const group = document.createElement('div');
        group.style.display = 'flex';
        group.style.gap = '16px';

        const modes = [
            { id: 'dialog-music-none', label: 'None', value: 'none' },
            { id: 'dialog-music-generate', label: 'Generate', value: 'generate' },
            { id: 'dialog-music-existing', label: 'Use existing', value: 'existing' }
        ];
        modes.forEach(m => {
            const lbl = document.createElement('label');
            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = 'dialog-music-mode';
            radio.id = m.id;
            radio.value = m.value;
            // Default selection mirrors legacy checkbox: if it was checked, choose Generate, else None
            radio.checked = (m.value === (anchor.checked ? 'generate' : 'none'));
            lbl.appendChild(radio);
            lbl.appendChild(document.createTextNode(' ' + m.label));
            group.appendChild(lbl);
        });
        wrap.appendChild(group);

        const idLabel = document.createElement('label');
        idLabel.htmlFor = 'dialog-music-storage-id';
        idLabel.textContent = 'Music Storage ID:';
        idLabel.style.display = 'block';
        idLabel.style.marginTop = '8px';
        const idInput = document.createElement('input');
        idInput.type = 'text';
        idInput.id = 'dialog-music-storage-id';
        idInput.placeholder = 'Enter existing storage object id for music';
        idInput.style.width = '100%';
        idInput.disabled = true;
        wrap.appendChild(idLabel);
        wrap.appendChild(idInput);

        // Interactions
        const radios = wrap.querySelectorAll('input[name="dialog-music-mode"]');
        radios.forEach(r => r.addEventListener('change', () => {
            const mode = (wrap.querySelector('input[name="dialog-music-mode"]:checked')?.value) || 'none';
            idInput.disabled = mode !== 'existing';
        }));

        anchor.parentElement.parentElement.insertBefore(wrap, anchor.parentElement.nextSibling);
    }
}


async function generateDialog() {
       if (!dialogText.value.trim()) {
           dialogResponseArea.textContent = 'Please enter some dialog text.';
           return;
       }

       let voiceMapping = {};
       try {
           if (dialogVoiceMapping.value.trim()) {
               voiceMapping = JSON.parse(dialogVoiceMapping.value);
           }
       } catch (e) {
           dialogResponseArea.textContent = 'Invalid JSON in Voice Mapping.';
           return;
       }

       generateDialogBtn.disabled = true;
       dialogResponseArea.textContent = 'Analyzing dialog and generating audio...';
       dialogAudioPlayer.style.display = 'none';
       dialogAnalysisResult.value = '';
       dialogSfxResult.value = '';
       if (document.getElementById('dialog-image-result')) {
           document.getElementById('dialog-image-result').style.display = 'none';
       }

       const payload = {
           id: `dialog-test-${Date.now()}`,
           timestamp: new Date().toISOString(),
           content: {
               text: dialogText.value,
               language: document.getElementById('dialog-language').value,
               speed: 1.0
           },
           config: {
               provider: "openai", // This is a placeholder, the backend logic is class-based
               output_format: "mp3",
               dialog_mode: true,
               voice_mapping: voiceMapping,
               ai_gender: dialogAiMaleVoice.checked ? 'male' : 'female',
               generate_title_image: dialogGenerateImage.checked,
               add_sfx: dialogAddSfx.checked,
               add_music: (() => { const m = document.querySelector('input[name="dialog-music-mode"]:checked'); return m && m.value !== 'none'; })(),
               manual_music_storage_id: (() => { const m = document.querySelector('input[name="dialog-music-mode"]:checked'); const v = document.getElementById('dialog-music-storage-id')?.value.trim(); return (m && m.value === 'existing' && v) ? v : null; })(),
               analyze_only: true
           },
           // Optional manual music storage id
           manual_music_storage_id: (document.getElementById('dialog-music-storage-id') && document.getElementById('dialog-music-storage-id').value.trim()) ? document.getElementById('dialog-music-storage-id').value.trim() : null,
           collection_id: document.getElementById('dialog-collection-name').value || null,
           save_options: {
                is_public: true,
                owner_email: "apopovic.aut@gmail.com"
           }
       };

       try {
           const response = await fetch(`${API_BASE_URL}/ai/generate_speech`, {
               method: 'POST',
               headers: {
                   'Content-Type': 'application/json',
                   'X-API-KEY': API_KEY
               },
               body: JSON.stringify(payload)
           });

           const responseText = await response.text();

           if (!response.ok) {
               dialogResponseArea.textContent = `Server Error (Status: ${response.status}):\n\n${responseText}`;
               throw new Error(`HTTP error! status: ${response.status}`);
           }

           const data = JSON.parse(responseText);

           dialogResponseArea.textContent = JSON.stringify(data, null, 2);

           // KORRIGIERTER TEIL
           if (data.analysis_result) {
               dialogAnalysisResult.value = JSON.stringify(data.analysis_result, null, 2);

               // Greife auf das korrekte Array zu: data.analysis_result.production_cues
               if (data.analysis_result.production_cues) {
                   const newVoiceMapping = {};
                   data.analysis_result.production_cues.forEach(segment => {
                       if (segment.type === 'dialog' && segment.speaker && !newVoiceMapping[segment.speaker]) {
                           newVoiceMapping[segment.speaker] = {
                               "default": "",
                               "secondary": ""
                           };
                       }
                   });
                   dialogVoiceMapping.value = JSON.stringify(newVoiceMapping, null, 2);
               }

               // Zeige die gefundenen SFX an
               if (data.analysis_result.sourced_sfx) {
                   dialogSfxResult.value = JSON.stringify(data.analysis_result.sourced_sfx, null, 2);
               }
           }

           if (data.generated_image_prompt) {
               dialogPromptDisplay.value = data.generated_image_prompt;
           }

           if (data.file_url) {
               dialogAudioPlayer.src = data.file_url;
               dialogAudioPlayer.style.display = 'block';
               dialogAudioPlayer.play();
           }

           if (data.generated_image && data.generated_image.file_url) {
               const dialogImageResult = document.getElementById('dialog-image-result');
               if (dialogImageResult) {
                   dialogImageResult.src = data.generated_image.file_url;
                   dialogImageResult.style.display = 'block';
               }
           }

       } catch (error) {
           console.error('Error generating dialog:', error);
           if (!dialogResponseArea.textContent.startsWith('Server Error')) {
               dialogResponseArea.textContent = `Error: ${error.message}`;
           }
       } finally {
           generateDialogBtn.disabled = false;
       }
   }




// --- Image Generation Functions ---
const imageGenPrompt = document.getElementById('image-gen-prompt');
const generateImageBtn = document.getElementById('generate-image-btn');
const imageGenResponseArea = document.getElementById('image-gen-response');
const imageGenResult = document.getElementById('image-gen-result');

async function generateImage() {
    if (!imageGenPrompt.value.trim()) {
        imageGenResponseArea.textContent = 'Please enter a prompt.';
        return;
    }

    generateImageBtn.disabled = true;
    imageGenResponseArea.textContent = 'Generating image...';
    imageGenResult.style.display = 'none';

    const payload = {
        prompt: imageGenPrompt.value,
        link_id: null,
        owner_user_id: null
    };

    try {
        const response = await fetch(`${API_BASE_URL}/ai/genimage`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-API-KEY': API_KEY
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.detail || `HTTP error! status: ${response.status}`);
        }
        
        imageGenResponseArea.textContent = JSON.stringify(data, null, 2);
        
        if (data.file_url) {
            imageGenResult.src = data.file_url;
            imageGenResult.style.display = 'block';
        }

    } catch (error) {
        console.error('Error generating image:', error);
        imageGenResponseArea.textContent = `Error: ${error.message}`;
    } finally {
        generateImageBtn.disabled = false;
    }
}

// --- Upscale (Stability) ---
async function upscaleImage() {
    const fileInput = document.getElementById('upscale-image');
    const formatSel = document.getElementById('upscale-format');
    const upscaleBtn = document.getElementById('upscale-btn');
    const upscaleResponse = document.getElementById('upscale-response');
    const upscaleResult = document.getElementById('upscale-result');

    if (fileInput.files.length === 0) {
        upscaleResponse.textContent = 'Please select an image.';
        return;
    }

    upscaleBtn.disabled = true;
    upscaleResponse.textContent = 'Upscaling image...';
    upscaleResult.style.display = 'none';

    try {
        const imageB64 = await fileToBase64(fileInput.files[0]);
        const payload = {
            image_b64: imageB64,
            output_format: formatSel.value,
            link_id: null,
            owner_user_id: null
        };

        const response = await fetch(`${API_BASE_URL}/ai/upscale`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-API-KEY': API_KEY
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.detail || `HTTP error! status: ${response.status}`);
        }

        upscaleResponse.textContent = JSON.stringify(data, null, 2);
        if (data.file_url) {
            upscaleResult.src = data.file_url;
            upscaleResult.style.display = 'block';
        }
    } catch (err) {
        console.error('Error upscaling image:', err);
        upscaleResponse.textContent = `Error: ${err.message}`;
    } finally {
        upscaleBtn.disabled = false;
    }
}
// --- SFX and Music Generation Functions ---
const sfxGenPrompt = document.getElementById('sfx-gen-prompt');
const generateSfxBtn = document.getElementById('generate-sfx-btn');
const sfxGenResponseArea = document.getElementById('sfx-gen-response');
const sfxAudioPlayer = document.getElementById('sfx-audio-player');

const musicGenPrompt = document.getElementById('music-gen-prompt');
const generateMusicBtn = document.getElementById('generate-music-btn');
const musicGenResponseArea = document.getElementById('music-gen-response');
const musicAudioPlayer = document.getElementById('music-audio-player');
// Force ElevenLabs only for music generation (removed provider selector)

async function generateSfx() {
    if (!sfxGenPrompt.value.trim()) {
        sfxGenResponseArea.textContent = 'Please enter a prompt.';
        return;
    }
    generateSfxBtn.disabled = true;
    sfxGenResponseArea.textContent = 'Generating SFX...';
    sfxAudioPlayer.style.display = 'none';

    try {
        const response = await fetch(`${API_BASE_URL}/ai/gensfx`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-API-KEY': API_KEY},
            body: JSON.stringify({ prompt: sfxGenPrompt.value })
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.detail || `HTTP error! status: ${response.status}`);
        
        sfxGenResponseArea.textContent = JSON.stringify(data, null, 2);
        if (data.file_url) {
            sfxAudioPlayer.src = data.file_url;
            sfxAudioPlayer.style.display = 'block';
            sfxAudioPlayer.play();
        }
    } catch (error) {
        sfxGenResponseArea.textContent = `Error: ${error.message}`;
    } finally {
        generateSfxBtn.disabled = false;
    }
}

async function generateMusic() {
    if (!musicGenPrompt.value.trim()) {
        musicGenResponseArea.textContent = 'Please enter a prompt.';
        return;
    }
    generateMusicBtn.disabled = true;
    musicGenResponseArea.textContent = 'Generating Music...';
    musicAudioPlayer.style.display = 'none';

    try {
        const response = await fetch(`${API_BASE_URL}/ai/genmusic_eleven`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-API-KEY': API_KEY},
            body: JSON.stringify({ prompt: musicGenPrompt.value })
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.detail || `HTTP error! status: ${response.status}`);
        
        musicGenResponseArea.textContent = JSON.stringify(data, null, 2);
        if (data.file_url) {
            musicAudioPlayer.src = data.file_url;
            musicAudioPlayer.style.display = 'block';
            musicAudioPlayer.play();
        }
    } catch (error) {
        musicGenResponseArea.textContent = `Error: ${error.message}`;
    } finally {
        generateMusicBtn.disabled = false;
    }
}

</script>

</body>
</html>
