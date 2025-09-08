<?php
session_start();
require 'db.php';

$user = $_SESSION['user'] ?? null;
if (!$user) {
    header('Location: index.html');
    exit;
}

try {
    $stmt = $pdo->query("SELECT * FROM mytbl ORDER BY id ASC");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

foreach ($services as &$service) {
    $service['required_documents'] = json_decode($service['field'] ?? '[]', true) ?: [];
    $service['steps'] = json_decode($service['Steps'] ?? '[]', true) ?: [];
    $service['fee_info'] = json_decode($service['fee'] ?? '""', true) ?: $service['fee'];

    $service['services'] = htmlspecialchars($service['services'], ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <div id="preloader" class="fixed inset-0 bg-gray-50 flex items-center justify-center z-50">
        <div class="flex space-x-4">
            <div class="w-6 h-6 rounded-full bg-green-500 animate-glow-bounce delay-0"></div>
            <div class="w-6 h-6 rounded-full bg-purple-500 animate-glow-bounce delay-150"></div>
            <div class="w-6 h-6 rounded-full bg-pink-500 animate-glow-bounce delay-300"></div>
        </div>
    </div>
    <style>
        @keyframes glow-bounce {

            0%,
            80%,
            100% {
                transform: scale(0.5);
                opacity: 0.6;
                box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
            }

            40% {
                transform: scale(1.2);
                opacity: 1;
                box-shadow: 0 0 20px rgba(59, 130, 246, 0.6),
                    0 0 20px rgba(139, 92, 246, 0.6), 0 0 20px rgba(236, 72, 153, 0.6);
            }
        }

        .animate-glow-bounce {
            animation: glow-bounce 1.2s infinite ease-in-out;
        }

        .delay-0 {
            animation-delay: 0s;
        }

        .delay-150 {
            animation-delay: 0.15s;
        }

        .delay-300 {
            animation-delay: 0.3s;
        }

        #preloader.fade-out {
            opacity: 0;
            transition: opacity 0.5s ease;
            pointer-events: none;
        }

        .checklist-item {
            transition: all 0.3s ease;
        }

        .checklist-item.warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fed7aa 100%);
            border-left: 4px solid #f59e0b;
        }

        .checklist-item.error {
            background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%);
            border-left: 4px solid #ef4444;
        }

        .checklist-item.success {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            border-left: 4px solid #22c55e;
        }

        .checklist-item.info {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border-left: 4px solid #3b82f6;
        }

        .warning-icon {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: .5;
            }
        }
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexus Sewa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ol@v8.2.0/ol.css">
    <script src="https://cdn.jsdelivr.net/npm/ol@v8.2.0/dist/ol.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

        main {
            min-height: 100vh;
        }

        body {
            font-family: 'Poppins', sans-serif;
        }

        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }

        .slide-in {
            animation: slideIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .chat-bubble {
            max-width: 90%;
            animation: slideIn 0.3s ease-out;
            word-wrap: break-word;
        }

        .bot-bubble {
            background: linear-gradient(135deg, #2563eb 0%, #e11d48 100%);
        }

        .user-bubble {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .resize-handle {
            height: 8px;
            cursor: ns-resize;
            background-color: #e5e7eb;
            border-top: 1px solid #d1d5db;
            border-bottom: 1px solid #d1d5db;
        }

        #map-container {
            height: 500px;
            width: 100%;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }

        .ol-popup {
            position: absolute;
            background-color: white;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #ccc;
            bottom: 12px;
            left: -50px;
            min-width: 200px;
        }

        .ol-popup:after,
        .ol-popup:before {
            top: 100%;
            border: solid transparent;
            content: " ";
            height: 0;
            width: 0;
            position: absolute;
            pointer-events: none;
        }

        .ol-popup:after {
            border-top-color: white;
            border-width: 10px;
            left: 48px;
            margin-left: -10px;
        }

        .ol-popup:before {
            border-top-color: #ccc;
            border-width: 11px;
            left: 48px;
            margin-left: -11px;
        }

        .ol-popup-closer {
            text-decoration: none;
            position: absolute;
            top: 5px;
            right: 8px;
        }

        .ol-popup-closer:after {
            content: "✖";
        }
    </style>
</head>

<body class="flex flex-col min-h-screen bg-gradient-to-r from-blue-200 to-pink-100">
    <header class="bg-white shadow-md sticky top-0 z-10">
        <div class="container mx-auto flex justify-between items-center p-4">
            <h1 class="text-2xl font-bold text-gray-800 hover:text-blue-700">SewaNexus</h1>
            <nav class="flex items-center space-x-6">
                <a href="dashboard.php" class="text-gray-700 hover:text-blue-600 font-medium">Home</a>
                <a href="services.php" class="text-gray-700 hover:text-blue-600 font-medium">Services</a>
                <a href="#about" class="text-gray-700 hover:text-blue-600 font-medium">About</a>
                <a href="#contact" class="text-gray-700 hover:text-blue-600 font-medium">Contact</a>
                <div class="flex items-center gap-3">
                    <?php if (!empty($user['avatar'])): ?>
                        <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="" class="w-8 h-8 rounded-full">
                    <?php endif; ?>
                    <span class="text-gray-700"><?php echo htmlspecialchars($user['name'] ?: $user['email']); ?></span>
                    <form method="post" action="auth.php">
                        <input type="hidden" name="action" value="logout">
                        <button class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-900">Logout</button>
                    </form>
                </div>
            </nav>
        </div>
    </header>

    <main class="flex-grow container mx-auto p-8 flex items-center justify-center">
        <div class="text-center fade-in max-w-2xl w-full">
            <h2 class="text-4xl font-bold mb-4 bg-green-600 bg-clip-text text-transparent">
                Your Digital Guide to Government Services
            </h2>
            <p class="text-xl text-gray-600 mb-8">Answer a few simple questions to find the perfect service and its complete guide.</p>
            <button id="start-finder-btn" class="bg-green-400 text-white font-bold py-4 px-8 rounded-full shadow-xl hover:scale-105 transition transform "><img width="50" height="50" src="https://img.icons8.com/papercut/120/search.png" alt="search" /> Start the Guide</button>
            <button id="find-office-btn" class="bg-white border-2 border-gray-300 text-gray-800 font-bold py-4 px-8 rounded-full shadow-lg hover:bg-gray-100 transition transform mt-4"><img width="50" height="50" src="https://img.icons8.com/fluency/50/location.png" alt="location" /> Find Nearby Office</button>
        </div>
    </main>

    <div id="finder-modal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-3xl shadow-2xl w-10/12 max-w-5xl max-h-[90vh] overflow-auto flex flex-col relative">
            <button id="close-finder-modal" class="absolute top-4 right-4 text-gray-500 text-3xl font-bold hover:text-gray-800 z-10">&times;</button>

            <div class="p-6 text-white bg-green-600 flex-shrink-0">
                <h2 class="text-2xl font-bold"><img src="images/icons8-learning.gif" class="rounded-full">Smart Document Guide</h2>
                <p class="text-sm opacity-90">Let's find the right service based on your documents and needs.</p>
            </div>

            <div id="chat-container" class="flex-grow overflow-y-auto p-11 space-y-4 bg-gray-50"></div>

            <div id="resize-handle" class="resize-handle hover:bg-green-800"></div>

            <div id="input-area" class="p-6 bg-white border-t flex-shrink-0 flex flex-col gap-4 overflow-y-auto">
                <div id="options-container" class="space-y-2 flex flex-col"></div>
                <div id="text-input-container" class="hidden flex items-center gap-2">
                    <input type="text" id="text-input" placeholder="Type your answer..." class="flex-grow p-3 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <button id="send-text" class="bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 transition-colors">Send</button>
                </div>
            </div>
        </div>
    </div>

    <div id="service-modal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-3xl shadow-2xl w-11/12 max-w-6xl max-h-[90vh] flex flex-col overflow-hidden relative">
            <button id="close-service-modal" class="absolute top-4 right-4 text-gray-500 hover:text-red-500 text-3xl font-bold z-10">&times;</button>

            <div class="flex items-center gap-4 p-6 border-b flex-shrink-0 bg-red-600 text-white">
                <div id="modal-icon" class="w-12 h-12 flex items-center justify-center bg-white bg-opacity-20 rounded-full text-2xl"><img width="94" height="94" src="https://img.icons8.com/3d-fluency/94/document.png" alt="document" /></div>
                <h3 id="modal-title" class="text-3xl font-bold"></h3>
            </div>

            <div class="p-6 overflow-y-auto flex-grow space-y-6">
                <div id="professional-checklist" class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-xl border border-green-200">
                    <h4 class="text-2xl font-semibold mb-4 text-indigo-700 flex items-center gap-2">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Professional Assessment
                    </h4>
                    <div id="checklist-items" class="space-y-3"></div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div>
                        <h4 class="text-2xl font-semibold mb-4 text-green-600"><img width="30" height="30" src="https://img.icons8.com/ios-glyphs/30/stairs.png" alt="stairs" /> Steps to Complete</h4>
                        <div id="modal-steps" class="space-y-3"></div>
                    </div>
                    <div>
                        <h4 class="text-2xl font-semibold mb-4 text-red-600"><img width="64" height="64" src="https://img.icons8.com/arcade/64/documents.png" alt="documents" /> Required Documents</h4>
                        <ul id="modal-documents" class="list-disc ml-5 space-y-2 text-gray-700 bg-gray-50 p-4 rounded-lg"></ul>
                    </div>
                </div>

                <div class="mt-8 bg-yellow-50 p-6 rounded-lg">
                    <h4 class="text-2xl font-semibold mb-4 text-yellow-600"> <img width="64" height="64" src="https://img.icons8.com/wired/64/money--v1.png" alt="money--v1" />Fee Information</h4>
                    <p id="modal-fee" class="text-gray-700 text-lg"></p>
                </div>
            </div>

            <div class="p-6 flex justify-end border-t flex-shrink-0 space-x-4">
                <button id="back-to-finder" class="bg-gray-400 text-white px-6 py-3 rounded-lg hover:bg-gray-700 font-semibold"><img width="40" height="40" src="https://img.icons8.com/deco-glyph/50/back.png" alt="back"/> Back to Guide</button>
                <form action="generate_pdf.php" method="get" target="_blank" class="inline">
                    <input type="hidden" name="service_id" id="service-id-input" value="">
                    <button type="submit" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 font-semibold"><img width="40" height="40" src="https://img.icons8.com/office/40/pdf.png" alt="pdf"/> Generate PDF Guide</button>
                </form>
            </div>
        </div>
    </div>

    <div id="map-modal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-3xl shadow-2xl w-10/12 max-w-5xl max-h-[90vh] overflow-hidden flex flex-col relative">
            <button id="close-map-modal" class="absolute top-4 right-4 text-gray-500 text-3xl font-bold hover:text-gray-800 z-10">&times;</button>
            <div class="p-6 text-white bg-red-600 flex-shrink-0">
                <h2 class="text-2xl font-bold">📍 Nearby Government Offices</h2>
                <p id="map-message" class="text-sm opacity-90">Please enable location services to see nearby offices.</p>
            </div>
            <div id="map-container" class="flex-grow"></div>
            <div id="ol-popup" class="ol-popup hidden">
                <a href="#" id="ol-popup-closer" class="ol-popup-closer"></a>
                <div id="ol-popup-content"></div>
            </div>
            <div class="p-4 bg-gray-50 text-center text-sm text-gray-600">
                <p>This is a demonstration. Locations are simulated. Please verify with official sources.</p>
            </div>
        </div>
    </div>
    <footer class="bg-gray-900 text-gray-200 py-16 relative">
        <div class="container mx-auto px-6 lg:px-20 grid grid-cols-1 md:grid-cols-3 gap-8 relative z-10">
            <div class="relative group">
                <div class="absolute -inset-2 rounded-3xl bg-purple-600 opacity-20 blur-3xl"></div>
                <div class="relative">
                    <h3 class="text-2xl font-bold text-white mb-4">Sewa Nexus</h3>
                    <p class="text-gray-400">
                        Simplifying access to government services with accurate information and a user-friendly platform for every citizen.
                    </p>
                </div>
            </div>

            <div class="relative group">
                <div class="absolute -inset-2 rounded-3xl bg-green-600 opacity-20 blur-3xl "></div>
                <div class="relative">
                    <h4 class="text-xl font-semibold text-white mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="dashboard.php#home" class="hover:text-purple-400 transition">Home</a></li>
                        <li><a href="dashboard.php#about" class="hover:text-purple-400 transition">About</a></li>
                        <li><a href="services.php" class="hover:text-purple-400 transition">Services</a></li>
                        <li><a href="dashboard.php#contact" class="hover:text-purple-400 transition">Contact</a></li>
                    </ul>
                </div>
            </div>

            <div class="relative group">
                <div class="absolute -inset-2 rounded-3xl bg-pink-600 opacity-20 blur-3xl"></div>
                <div class="relative">
                    <h4 class="text-xl font-semibold text-white mb-4">Follow Us</h4>
                    <div class="flex space-x-4 mb-4">
                        <a href="#" class="hover:text-blue-500 transition"><i class="bi bi-facebook text-2xl"></i></a>
                        <a href="#" class="hover:text-blue-400 transition"><i class="bi bi-twitter text-2xl"></i></a>
                        <a href="#" class="hover:text-pink-500 transition"><i class="bi bi-instagram text-2xl"></i></a>
                        <a href="#" class="hover:text-red-500 transition"><i class="bi bi-youtube text-2xl"></i></a>
                    </div>
                    <p class="text-gray-400"><i class="bi bi-envelope-fill mr-2"></i>support@SewaNexus.com</p>
                    <p class="text-gray-400"><i class="bi bi-telephone-fill mr-2"></i>+977 980xxxxxxx</p>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-800 mt-8 pt-6 text-center text-gray-500 relative z-10">
            &copy; 2025 Sewa Nexus. All Rights Reserved.
        </div>
    </footer>
    <script>
        // Fixed JavaScript code with all issues resolved

        const services = <?= json_encode($services) ?>;
        console.log('Available services:', services);

        let userProfile = {
            age: null,
            neededService: null,
            eligibleServices: []
        };

        // Add missing currentQ variable
        let currentQ = 0;

        // Add missing documentProgress variable
        let documentProgress = {
            completed: [],
            total: 0,
            serviceId: null
        };

        // Professional checklist data (keeping existing structure)
        const checklistRules = {
            age_warnings: {
                'Citizenship Certificate': {
                    minAge: 16,
                    warning: 'Citizenship certificate requires applicant to be at least 16 years old',
                    type: 'error'
                },
                'Passport Issuance': {
                    minAge: 16,
                    warning: 'Independent passport application requires applicant to be at least 16 years old',
                    type: 'warning'
                }
            },
            document_requirements: {
                'Birth Certificate': {
                    critical_docs: ['Hospital Birth Record', 'Parents\' Citizenship'],
                    warning: 'Missing critical documents may cause significant delays',
                    type: 'warning'
                },
                'Passport Issuance': {
                    critical_docs: ['Citizenship Certificate', 'Valid ID'],
                    warning: 'Passport requires valid citizenship certificate as prerequisite',
                    type: 'error'
                }
            },
            processing_time: {
                'Citizenship Certificate': {
                    time: '30-45 days',
                    warning: 'Long processing time - plan ahead for urgent travel needs',
                    type: 'info'
                },
                'Passport Issuance': {
                    time: '15-30 days',
                    warning: 'Processing time varies by location and season',
                    type: 'info'
                }
            },
            special_requirements: {
                'Business Registration': {
                    requirement: 'Tax clearance and location verification required',
                    warning: 'Additional verification steps may extend processing time',
                    type: 'warning'
                },
                'Vehicle Registration': {
                    requirement: 'Insurance and pollution check required',
                    warning: 'Vehicle must pass pollution and safety standards',
                    type: 'warning'
                }
            }
        };

        // Define smart finder questions
        const questions = [{
                id: 'age',
                msg: 'What is your age? (This helps us check eligibility requirements)',
                type: 'text',
                validate: (val) => {
                    const age = parseInt(val);
                    return age >= 0 && age <= 120 ? age : null;
                }
            },
            {
                id: 'purpose',
                msg: 'What do you want to accomplish?',
                type: 'options',
                options: [{
                        text: 'Apply For a business',
                        val: 'business_registration'
                    },
                    {
                        text: 'Make a birth certificate for my child',
                        val: 'birth_certificate'
                    },
                    {
                        text: 'Register a vehicle',
                        val: 'vehicle_registration'
                    },
                    {
                        text: 'Get police clearance',
                        val: 'police_clearance'
                    },
                    {
                        text: 'Apply for citizenship',
                        val: 'citizenship_certificate'
                    },
                    {
                        text: 'Apply for passport',
                        val: 'passport_issuance'
                    },
                    {
                        text: 'Register a death',
                        val: 'death_certificate'
                    },
                    {
                        text: 'Not sure - show me what I can do',
                        val: 'show_all'
                    }
                ]
            }
        ];

        // Add missing updateDocumentProgress function
        function updateDocumentProgress() {
            // Find the document progress item in the checklist
            const progressItem = document.querySelector('[data-document-progress="true"]');
            if (!progressItem) return;

            const completedCount = documentProgress.completed.length;
            const totalCount = documentProgress.total;
            const percentage = totalCount > 0 ? Math.round((completedCount / totalCount) * 100) : 0;

            // Update the progress item
            const progressContent = progressItem.querySelector('.progress-content');
            if (progressContent) {
                progressContent.innerHTML = `
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium">Document Preparation: ${completedCount}/${totalCount}</span>
                <span class="text-sm text-gray-600">${percentage}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="bg-green-600 h-3 rounded-full transition-all duration-300" style="width: ${percentage}%"></div>
            </div>
            <p class="text-xs text-gray-600 mt-1">
                ${percentage === 100 ? 'All documents prepared! You\'re ready to apply.' : 
                  percentage > 50 ? 'Good progress on document preparation.' : 
                  'Start preparing your documents using the checklist below.'}
            </p>
        `;
            }

            // Update the type and styling based on progress
            if (percentage === 100) {
                progressItem.className = progressItem.className.replace(/checklist-item \w+/, 'checklist-item success');
            } else if (percentage > 50) {
                progressItem.className = progressItem.className.replace(/checklist-item \w+/, 'checklist-item info');
            } else {
                progressItem.className = progressItem.className.replace(/checklist-item \w+/, 'checklist-item warning');
            }
        }

        function resetFinder() {
            currentQ = 0;
            userProfile = {
                age: null,
                neededService: null,
                eligibleServices: []
            };
            document.getElementById('chat-container').innerHTML = '';
        }

        function addMessage(msg, isUser = false) {
            const chat = document.getElementById('chat-container');
            const div = document.createElement('div');
            div.className = `flex ${isUser ? 'justify-end' : 'justify-start'} mb-4`;
            div.innerHTML = `<div class="chat-bubble ${isUser ? 'user-bubble' : 'bot-bubble'} text-white p-4 rounded-2xl shadow-lg break-words">${msg}</div>`;
            chat.appendChild(div);
            chat.scrollTop = chat.scrollHeight;
        }

        function showQuestion() {
            if (currentQ >= questions.length) {
                processResults();
                return;
            }

            const q = questions[currentQ];
            addMessage(q.msg);

            const optContainer = document.getElementById('options-container');
            const textContainer = document.getElementById('text-input-container');

            optContainer.innerHTML = '';
            textContainer.classList.add('hidden');

            if (q.type === 'options') {
                q.options.forEach(o => {
                    const btn = document.createElement('button');
                    btn.className = 'w-full text-left p-4 bg-gray-100 rounded-xl mb-2 hover:bg-green-100 transition-colors break-words';
                    btn.textContent = o.text;
                    btn.onclick = () => {
                        addMessage(o.text, true);
                        handleAnswer(q.id, o.val);
                        currentQ++;
                        setTimeout(showQuestion, 500);
                    };
                    optContainer.appendChild(btn);
                });
            } else if (q.type === 'text') {
                textContainer.classList.remove('hidden');
                const input = document.getElementById('text-input');
                input.focus();

                const handleSubmit = () => {
                    const val = input.value.trim();
                    if (!val) return;

                    const validatedVal = q.validate ? q.validate(val) : val;
                    if (validatedVal === null) {
                        addMessage('Please enter a valid value.', false);
                        return;
                    }

                    addMessage(val, true);
                    handleAnswer(q.id, validatedVal);
                    input.value = '';
                    currentQ++;
                    setTimeout(showQuestion, 500);
                };

                document.getElementById('send-text').onclick = handleSubmit;
                input.onkeypress = (e) => {
                    if (e.key === 'Enter') handleSubmit();
                };
            }
        }

        function handleAnswer(questionId, answer) {
            switch (questionId) {
                case 'age':
                    userProfile.age = answer;
                    break;
                case 'purpose':
                    userProfile.neededService = answer;
                    break;
            }
        }

        function processResults() {
            addMessage('Let me analyze your information and find the best services for you...', false);

            setTimeout(() => {
                const eligibleServices = findEligibleServices();

                if (eligibleServices.length === 1) {
                    addMessage(`Great! Based on your information, I found the perfect service for you: ${eligibleServices[0].services}`, false);
                    setTimeout(() => showService(eligibleServices[0]), 1000);
                } else {
                    addMessage(`I found ${eligibleServices.length} services that match your profile. Here are your options:`, false);

                    setTimeout(() => {
                        const optContainer = document.getElementById('options-container');
                        optContainer.innerHTML = '';

                        eligibleServices.forEach(service => {
                            const btn = document.createElement('button');
                            btn.className = 'w-full text-left p-4 bg-purple-100 rounded-xl mb-2 hover:bg-green-200 hover:to-purple-200 transition-all transform hover:scale-105';
                            btn.innerHTML = `<strong>${service.services}</strong><br><small class="text-gray-600">Click to see details</small>`;
                            btn.onclick = () => showService(service);
                            optContainer.appendChild(btn);
                        });
                        document.getElementById('text-input-container').classList.add('hidden');
                        document.getElementById('options-container').classList.remove('hidden');

                    }, 500);
                }
            }, 1500);
        }

        function findEligibleServices() {
            let eligible = [];
            const age = userProfile.age;
            const purpose = userProfile.neededService;

            services.forEach(service => {
                let canApply = true;
                let reasons = [];

                if (service.services === 'Citizenship Certificate' && age < 16) {
                    canApply = false;
                    reasons.push('Must be at least 16 years old');
                }

                if (service.services === 'Passport Issuance' && age < 16) {
                    canApply = false;
                    reasons.push('Must be at least 16 years old for independent passport application');
                }

                if (purpose && purpose !== 'show_all') {
                    if (purpose === 'business_registration' && service.services !== 'Business Registration') {
                        canApply = false;
                    }
                    if (purpose === 'birth_certificate' && service.services !== 'Birth Certificate') {
                        canApply = false;
                    }
                    if (purpose === 'vehicle_registration' && service.services !== 'Vehicle Registration') {
                        canApply = false;
                    }
                    if (purpose === 'police_clearance' && service.services !== 'Police Clearance Certificate') {
                        canApply = false;
                    }
                    if (purpose === 'citizenship_certificate' && service.services !== 'Citizenship Certificate') {
                        canApply = false;
                    }
                    if (purpose === 'passport_issuance' && service.services !== 'Passport Issuance') {
                        canApply = false;
                    }
                    if (purpose === 'death_certificate' && service.services !== 'Death Certificate') {
                        canApply = false;
                    }
                }

                if (canApply || purpose === 'show_all') {
                    eligible.push({
                        ...service,
                        eligibilityReasons: reasons
                    });
                }
            });

            return eligible;
        }

        function generateProfessionalChecklist(service) {
            const checklist = [];
            const serviceName = service.services;
            const userAge = userProfile.age;

            console.log('Generating checklist for:', serviceName, 'User age:', userAge);

            // Document Progress Assessment (will be updated dynamically)
            checklist.push({
                type: 'warning',
                icon: '📋',
                title: 'Document Preparation Status',
                message: 'Track your document preparation progress below. Complete preparation ensures smooth application processing.',
                priority: 'high',
                isDocumentProgress: true
            });

            // Age-based warnings
            if (checklistRules.age_warnings[serviceName]) {
                const rule = checklistRules.age_warnings[serviceName];
                if (userAge && userAge < rule.minAge) {
                    checklist.push({
                        type: rule.type,
                        icon: rule.type === 'error' ? '❌' : '⚠️',
                        title: 'Age Requirement Not Met',
                        message: rule.warning,
                        priority: rule.type === 'error' ? 'high' : 'medium'
                    });
                } else if (userAge && userAge >= rule.minAge) {
                    checklist.push({
                        type: 'success',
                        icon: '✅',
                        title: 'Age Requirement Met',
                        message: `You meet the minimum age requirement of ${rule.minAge} years`,
                        priority: 'low'
                    });
                }
            }

            // Document complexity analysis
            const docCount = service.required_documents.length;
            if (docCount > 7) {
                checklist.push({
                    type: 'warning',
                    icon: '📊',
                    title: 'High Document Requirements',
                    message: `This service requires ${docCount} documents. Consider preparing documents in advance and double-check requirements.`,
                    priority: 'high',
                    details: 'Complex applications may take longer due to extensive documentation'
                });
            } else if (docCount > 4) {
                checklist.push({
                    type: 'info',
                    icon: '📝',
                    title: 'Moderate Document Requirements',
                    message: `This service requires ${docCount} documents. Use the checklist below to track your preparation.`,
                    priority: 'medium'
                });
            } else {
                checklist.push({
                    type: 'success',
                    icon: '📄',
                    title: 'Simple Documentation',
                    message: `This service requires only ${docCount} documents. Relatively straightforward application process.`,
                    priority: 'low'
                });
            }

            // Document requirements
            if (checklistRules.document_requirements[serviceName]) {
                const rule = checklistRules.document_requirements[serviceName];
                checklist.push({
                    type: rule.type,
                    icon: rule.type === 'error' ? '📋' : '📄',
                    title: 'Critical Document Requirements',
                    message: rule.warning,
                    priority: rule.type === 'error' ? 'high' : 'medium',
                    details: `Critical documents: ${rule.critical_docs.join(', ')}`
                });
            }

            // Processing time warnings
            if (checklistRules.processing_time[serviceName]) {
                const rule = checklistRules.processing_time[serviceName];
                checklist.push({
                    type: rule.type,
                    icon: '⏱️',
                    title: 'Processing Time Alert',
                    message: rule.warning,
                    priority: 'medium',
                    details: `Expected processing time: ${rule.time}`
                });
            }

            // Special requirements
            if (checklistRules.special_requirements[serviceName]) {
                const rule = checklistRules.special_requirements[serviceName];
                checklist.push({
                    type: rule.type,
                    icon: '⚡',
                    title: 'Special Requirements',
                    message: rule.warning,
                    priority: 'medium',
                    details: rule.requirement
                });
            }

            // Fee-related warnings
            const feeText = service.fee_info.toString().toLowerCase();
            if (feeText.includes('varies') || feeText.includes('additional')) {
                checklist.push({
                    type: 'info',
                    icon: '💰',
                    title: 'Variable Fees',
                    message: 'Service fees may vary. Confirm current rates before visiting.',
                    priority: 'low'
                });
            }

            // Application readiness check
            checklist.push({
                type: 'info',
                icon: '🎯',
                title: 'Application Readiness',
                message: 'Complete the document checklist and review all requirements before visiting the office.',
                priority: 'medium',
                details: 'Proper preparation reduces processing time and prevents multiple visits'
            });

            // Sort by priority
            const priorityOrder = {
                high: 1,
                medium: 2,
                low: 3
            };
            checklist.sort((a, b) => priorityOrder[a.priority] - priorityOrder[b.priority]);

            console.log('Final checklist:', checklist);
            return checklist;
        }

        function renderProfessionalChecklist(service) {
            const checklist = generateProfessionalChecklist(service);
            const container = document.getElementById('checklist-items');
            container.innerHTML = '';

            if (checklist.length === 0) {
                container.innerHTML = '<p class="text-gray-600 italic">No specific warnings or recommendations for this service.</p>';
                return;
            }

            checklist.forEach((item, index) => {
                const div = document.createElement('div');
                div.className = `checklist-item ${item.type} p-4 rounded-lg shadow-sm transition-all duration-300 hover:shadow-md`;

                // Add data attribute for document progress tracking
                if (item.isDocumentProgress) {
                    div.setAttribute('data-document-progress', 'true');
                }

                const iconClass = item.type === 'error' ? 'warning-icon' : '';

                div.innerHTML = `
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 text-xl ${iconClass}">${item.icon}</div>
                <div class="flex-grow">
                    <h5 class="font-semibold text-gray-800 mb-1">${item.title}</h5>
                    <div class="progress-content">
                        <p class="text-gray-700 text-sm mb-2">${item.message}</p>
                        ${item.details ? `<p class="text-xs text-gray-600 italic">${item.details}</p>` : ''}
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <span class="text-xs px-2 py-1 rounded-full ${getPriorityClass(item.priority)}">${item.priority.toUpperCase()}</span>
                </div>
            </div>
        `;

                // Add staggered animation
                setTimeout(() => {
                    div.style.opacity = '0';
                    div.style.transform = 'translateY(20px)';
                    container.appendChild(div);

                    requestAnimationFrame(() => {
                        div.style.transition = 'all 0.3s ease';
                        div.style.opacity = '1';
                        div.style.transform = 'translateY(0)';
                    });
                }, index * 100);
            });
        }

        function getPriorityClass(priority) {
            switch (priority) {
                case 'high':
                    return 'bg-red-100 text-red-800';
                case 'medium':
                    return 'bg-yellow-100 text-yellow-800';
                case 'low':
                    return 'bg-green-100 text-green-800';
                default:
                    return 'bg-gray-100 text-gray-800';
            }
        }

        function showService(service) {
            document.getElementById('finder-modal').classList.add('hidden');
            document.getElementById('service-modal').classList.remove('hidden');

            document.getElementById('modal-title').textContent = service.services;
            document.getElementById('service-id-input').value = service.id;

            // Reset document progress for new service
            documentProgress = {
                completed: [],
                total: service.required_documents.length,
                serviceId: service.id
            };

            // Generate and render professional checklist
            renderProfessionalChecklist(service);

            const stepsContainer = document.getElementById('modal-steps');
            stepsContainer.innerHTML = '';
            service.steps.forEach((step, i) => {
                const div = document.createElement('div');
                div.className = 'bg-white border-l-4 border-green-500 p-4 mb-3 shadow-sm rounded-lg';
                div.innerHTML = `
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center font-bold text-sm">${i + 1}</div>
                <div class="flex-grow text-gray-800">${step}</div>
            </div>
        `;
                stepsContainer.appendChild(div);
            });

            // Display required documents with interactive checkboxes
            const docsList = document.getElementById('modal-documents');
            docsList.innerHTML = '';
            service.required_documents.forEach((doc, index) => {
                const div = document.createElement('div');
                div.className = 'flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer group';
                div.innerHTML = `
            <div class="flex-shrink-0">
                <input type="checkbox" 
                       id="doc-${index}" 
                       class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 transition-all"
                       ${documentProgress.completed.includes(index) ? 'checked' : ''}>
            </div>
            <label for="doc-${index}" class="flex-grow cursor-pointer text-gray-800 group-hover:text-blue-600 transition-colors">
                <span class="document-text ${documentProgress.completed.includes(index) ? 'line-through text-gray-500' : ''}">${doc}</span>
            </label>
            <div class="flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                <span class="text-xs text-gray-500">Click to mark as prepared</span>
            </div>
        `;

                // Add click event for the entire div
                div.onclick = (e) => {
                    if (e.target.type !== 'checkbox') {
                        const checkbox = div.querySelector('input[type="checkbox"]');
                        checkbox.click();
                    }
                };

                // Add change event for checkbox
                const checkbox = div.querySelector('input[type="checkbox"]');
                checkbox.onchange = () => {
                    const docText = div.querySelector('.document-text');
                    if (checkbox.checked) {
                        if (!documentProgress.completed.includes(index)) {
                            documentProgress.completed.push(index);
                        }
                        docText.className = 'document-text line-through text-gray-500';
                        div.classList.add('bg-green-50');
                    } else {
                        documentProgress.completed = documentProgress.completed.filter(i => i !== index);
                        docText.className = 'document-text';
                        div.classList.remove('bg-green-50');
                    }
                    updateDocumentProgress();
                };

                docsList.appendChild(div);
            });

            // Initialize progress
            updateDocumentProgress();

            document.getElementById('modal-fee').textContent = service.fee_info;
        }

        // Event handlers and initialization
        document.addEventListener('DOMContentLoaded', function() {
            // Start finder button
            document.getElementById('start-finder-btn').addEventListener('click', function() {
                document.getElementById('finder-modal').classList.remove('hidden');
                resetFinder();
                showQuestion();
            });

            // Find office button
            document.getElementById('find-office-btn').addEventListener('click', function() {
                document.getElementById('map-modal').classList.remove('hidden');
                setTimeout(showOfficeMap, 100); // Small delay to ensure modal is visible
            });

            // Close modals
            document.getElementById('close-finder-modal').addEventListener('click', function() {
                document.getElementById('finder-modal').classList.add('hidden');
            });

            document.getElementById('close-service-modal').addEventListener('click', function() {
                document.getElementById('service-modal').classList.add('hidden');
            });

            document.getElementById('close-map-modal').addEventListener('click', function() {
                document.getElementById('map-modal').classList.add('hidden');
            });

            // Back to finder button
            document.getElementById('back-to-finder').addEventListener('click', function() {
                document.getElementById('service-modal').classList.add('hidden');
                document.getElementById('finder-modal').classList.remove('hidden');
            });

            // Text input event handler
            document.getElementById('text-input').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    document.getElementById('send-text').click();
                }
            });

            // Preloader
            window.addEventListener('load', function() {
                setTimeout(function() {
                    const preloader = document.getElementById('preloader');
                    if (preloader) {
                        preloader.classList.add('fade-out');
                        setTimeout(() => preloader.remove(), 500);
                    }
                }, 1000);
            });
        });

        // Resize handle functionality
        const resizeHandle = document.getElementById('resize-handle');
        const inputArea = document.getElementById('input-area');
        const finderModal = document.getElementById('finder-modal');
        let isResizing = false;

        if (resizeHandle && inputArea && finderModal) {
            resizeHandle.addEventListener('mousedown', function(e) {
                isResizing = true;
                e.preventDefault();
                document.addEventListener('mousemove', handleMouseMove);
                document.addEventListener('mouseup', handleMouseUp);
            });

            function handleMouseMove(e) {
                if (!isResizing) return;

                const modalHeight = finderModal.offsetHeight;
                const newHeight = modalHeight - e.clientY + finderModal.offsetTop;
                const minHeight = 150;
                const maxHeight = modalHeight - 100;

                inputArea.style.height = `${Math.max(minHeight, Math.min(maxHeight, newHeight))}px`;
            }

            function handleMouseUp() {
                isResizing = false;
                document.removeEventListener('mousemove', handleMouseMove);
                document.removeEventListener('mouseup', handleMouseUp);
            }
        }

        function showOfficeMap() {
            const mapMessage = document.getElementById('map-message');
            mapMessage.textContent = 'Loading map...';

            const offices = [{
                    name: "Ministry of Home Affairs",
                    location: [85.3188, 27.7019]
                },
                {
                    name: "Department of Transport Management",
                    location: [85.3090, 27.6698]
                },
                {
                    name: "District Administration Office",
                    location: [85.3117, 27.7001]
                },
                {
                    name: "Nepal Police Headquarters",
                    location: [85.3262, 27.7077]
                }
            ];

            const map = new ol.Map({
                target: 'map-container',
                layers: [
                    new ol.layer.Tile({
                        source: new ol.source.OSM()
                    })
                ],
                view: new ol.View({
                    center: ol.proj.fromLonLat([85.324, 27.70]),
                    zoom: 13
                })
            });

            const officeVectorSource = new ol.source.Vector({});
            const officeVectorLayer = new ol.layer.Vector({
                source: officeVectorSource
            });
            map.addLayer(officeVectorLayer);

            offices.forEach(office => {
                const marker = new ol.Feature({
                    geometry: new ol.geom.Point(ol.proj.fromLonLat(office.location)),
                    name: office.name,
                    type: 'office'
                });
                officeVectorSource.addFeature(marker);
            });

            const element = document.getElementById('ol-popup');
            const content = document.getElementById('ol-popup-content');
            const closer = document.getElementById('ol-popup-closer');

            if (element) {
                element.style.display = 'block';

                const overlay = new ol.Overlay({
                    element: element,
                    autoPan: {
                        animation: {
                            duration: 250
                        },
                    },
                });
                map.addOverlay(overlay);

                if (closer) {
                    closer.onclick = function() {
                        overlay.setPosition(undefined);
                        closer.blur();
                        return false;
                    };
                }

                map.on('singleclick', function(evt) {
                    const feature = map.forEachFeatureAtPixel(evt.pixel, function(feature) {
                        return feature;
                    });
                    if (feature) {
                        const coordinates = feature.getGeometry().getCoordinates();
                        overlay.setPosition(coordinates);
                        if (content) {
                            content.innerHTML = `<p class="font-bold">${feature.get('name')}</p>`;
                        }
                    } else {
                        overlay.setPosition(undefined);
                    }
                });
            }

            if (navigator.geolocation) {
                mapMessage.textContent = 'Finding your location...';
                navigator.geolocation.getCurrentPosition(function(position) {
                    const coords = [position.coords.longitude, position.coords.latitude];
                    const userLocation = ol.proj.fromLonLat(coords);

                    const userFeature = new ol.Feature({
                        geometry: new ol.geom.Point(userLocation),
                        name: 'Your Location',
                        type: 'user'
                    });

                    userFeature.setStyle(new ol.style.Style({
                        image: new ol.style.Circle({
                            radius: 8,
                            fill: new ol.style.Fill({
                                color: '#2563eb'
                            }),
                            stroke: new ol.style.Stroke({
                                color: '#fff',
                                width: 2
                            })
                        })
                    }));

                    const userVectorSource = new ol.source.Vector({
                        features: [userFeature]
                    });
                    const userVectorLayer = new ol.layer.Vector({
                        source: userVectorSource
                    });
                    map.addLayer(userVectorLayer);

                    map.getView().setCenter(userLocation);
                    map.getView().setZoom(15);
                    mapMessage.textContent = 'Showing your location and nearby offices.';
                }, function(error) {
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            mapMessage.textContent = "Location access denied. Displaying general map.";
                            break;
                        case error.POSITION_UNAVAILABLE:
                            mapMessage.textContent = "Location information is unavailable. Displaying general map.";
                            break;
                        case error.TIMEOUT:
                            mapMessage.textContent = "The request to get user location timed out. Displaying general map.";
                            break;
                        default:
                            mapMessage.textContent = "An unknown error occurred. Displaying general map.";
                            break;
                    }
                });
            } else {
                mapMessage.textContent = "Geolocation is not supported by your browser.";
            }
        }
    </script>
    <script src="script.js"></script>
</body>

</html>