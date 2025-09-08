<?php
require 'db.php';
session_start();
$user = $_SESSION['user'] ?? null;
if (!$user) {
    header('Location: index.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="shortcut icon" href="images/logo.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/@studio-freight/lenis@latest/bundled/lenis.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://accounts.google.com/gsi/client" async defer></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="styles.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sewa Nexus</title>
    <style>
        #pay-btn:hover {
            box-shadow: 0 0 20px rgba(139, 92, 246, 0.6), 0 0 40px rgba(139, 92, 246, 0.4);
        }
    </style>

    <script src="https://cdn.tailwindcss.com"></script>

    <div id="preloader" class="fixed inset-0 bg-gray-50 flex items-center justify-center z-50">
        <div class="flex space-x-4">
            <div class="w-6 h-6 rounded-full bg-blue-500 animate-glow-bounce delay-0"></div>
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
                    0 0 20px rgba(139, 92, 246, 0.6),
                    0 0 20px rgba(236, 72, 153, 0.6);
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

        #toTopBtn {
            position: fixed;
            width: 4rem;
            height: 4rem;
            bottom: 2rem;
            right: 2rem;
            background: var(--primary);
            color: var(--bg);
            border: none;
            padding: 0.75rem 1rem;
            border-radius: 50%;
            font-size: 1.25rem;
            cursor: pointer;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            display: none;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        #toTopBtn:hover {
            background: var(--primary-hover);
            transform: translateY(-3px);
        }
    </style>

    <script>
        // Ensure preloader shows while the page is loading
        document.addEventListener('DOMContentLoaded', () => {
            const preloader = document.getElementById('preloader');
            preloader.style.display = 'flex'; // Ensure it's visible immediately
        });

        // Hide preloader when all assets are fully loaded
        window.addEventListener('load', () => {
            const preloader = document.getElementById('preloader');
            preloader.classList.add('fade-out');
            preloader.addEventListener('transitionend', () => {
                preloader.remove();
            });
        });
    </script>

</head>

<body class="flex flex-col min-h-screen bg-gray-50">
    <button id="toTopBtn">↑</button>

    <div class="fixed top-0 left-0 w-full z-50">
        <div id="scroll-progress" class="h-1 bg-blue-500 w-0"></div>

    </div>
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

    <main class="flex-grow">
        <section id="home" class="bg-gray-50 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-96 h-96 bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-bounce-slow"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-yellow-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-bounce-slow"></div>

            <div class="container mx-auto px-6 lg:px-20 flex flex-col-reverse lg:flex-row items-center py-20 lg:py-32">

                <div class="lg:w-1/2 text-center lg:text-left space-y-6">
                    <h1 class="text-5xl font-extrabold text-gray-900 leading-tight animate-fade-in">
                        Simplifying Government Services <br class="hidden md:block"> for Everyone
                    </h1>
                    <p class="text-gray-600 text-lg animate-fade-in delay-200">
                        Access all the information you need to get your documents and services done efficiently in Nepal.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start mt-6">
                        <a href="services.php" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition transform hover:scale-105 flex items-center gap-2">
                            <i class="bi bi-card-checklist"></i> View Services
                        </a>
                        <a href="#pricing" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-6 rounded-lg shadow-lg transition transform hover:scale-105 flex items-center gap-2">
                            <i class="bi bi-currency-dollar"></i> View Pricing
                        </a>
                    </div>
                </div>

                <div class="lg:w-1/2 mb-12 lg:mb-0 flex justify-center lg:justify-end animate-fade-in delay-400">
                    <img src="./images/logo.png"
                        alt="Nepal Government Services" class="rounded-xl shadow-lg w-full max-w-md">
                </div>
            </div>
            </div>
        </section>

        </script>
        <section id="about" class="bg-gray-50 py-20">
            <div class="container mx-auto px-6 lg:px-20">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-4xl font-extrabold text-gray-900 mb-4">Our Foundation</h2>
                    <p class="text-gray-700 text-lg leading-relaxed">
                        Sewa Nexus is your all-in-one digital platform to access government services efficiently.
                        Our mission is to simplify document processes, provide clear guidance, and ensure transparency
                        for every citizen in Nepal.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 relative about-card-group">
                    <div class="relative group about-card">
                        <div class="relative bg-white rounded-3xl p-8 shadow-lg transition transform hover:-translate-y-3 hover:scale-105 hover:shadow-2xl border-purple-500">
                            <div class="flex justify-center mb-4">
                                <img width="48" height="48" src="https://img.icons8.com/pulsar-gradient/48/mission-of-a-company.png" alt="mission-of-a-company" />
                            </div>
                            <h3 class="text-2xl font-semibold text-gray-900 mb-3 text-center">Our Mission</h3>
                            <p class="text-gray-700 text-center">
                                Simplify government processes and provide a digital-first approach for citizens to access services easily and quickly.
                            </p>
                        </div>
                    </div>

                    <div class="relative group about-card">
                        <div class="relative bg-white rounded-3xl p-8 shadow-lg transition transform hover:-translate-y-3 hover:scale-105 hover:shadow-2xl border-blue-500">
                            <div class="flex justify-center mb-4">
                                <img width="48" height="48" src="https://img.icons8.com/pulsar-gradient/48/vision.png" alt="vision" />
                            </div>
                            <h3 class="text-2xl font-semibold text-gray-900 mb-3 text-center">Our Vision</h3>
                            <p class="text-gray-700 text-center">
                                Build a transparent and efficient digital ecosystem for all government services across Nepal.
                            </p>
                        </div>
                    </div>

                    <div class="relative group about-card">
                        <div class="relative bg-white rounded-3xl p-8 shadow-lg transition transform hover:-translate-y-3 hover:scale-105 hover:shadow-2xl border-yellow-400">
                            <div class="flex justify-center mb-4">
                                <img width="48" height="48" src="https://img.icons8.com/pulsar-gradient/48/morale.png" alt="morale" />
                            </div>
                            <h3 class="text-2xl font-semibold text-gray-900 mb-3 text-center">Our Values</h3>
                            <p class="text-gray-700 text-center">
                                Transparency, reliability, and accessibility are at the core of everything we do.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>



        <section id="pricing" class="bg-gray-50 py-20">
            <div class="container mx-auto px-6 lg:px-20 text-center">
                <h2 class="text-4xl font-extrabold mb-8 text-gray-900">Pricing Plans</h2>
                <p class="text-gray-600 mb-16">
                    Choose the plan that fits your needs and start accessing government services seamlessly.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                    <div class="bg-white rounded-xl p-10 shadow-lg hover:shadow-2xl transition transform hover:-translate-y-2 relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-blue-500 opacity-10 rounded-xl pointer-events-none"></div>
                        <h3 class="text-3xl font-bold mb-4 text-gray-900">Basic</h3>
                        <p class="mb-6 text-gray-700">Access services with basic features.</p>
                        <span class="text-4xl font-extrabold mb-6 block text-gray-900">$0 <span class="text-lg font-medium">/ month</span></span>
                        <ul class="mb-8 space-y-2 text-left text-gray-700">
                            <li><i class="bi bi-check-circle-fill text-green-400 mr-2"></i>View Services</li>
                            <li><i class="bi bi-x-circle-fill text-red-400 mr-2"></i>Download Documents</li>
                            <li><i class="bi bi-x-circle-fill text-red-400 mr-2"></i>Premium Support</li>
                        </ul>
                        <a href="services.php" class="bg-purple-800 text-white font-semibold py-3 px-6 rounded-lg shadow-md hover:shadow-lg transition">Get Started</a>
                    </div>

                    <div class="bg-white rounded-xl p-10 shadow-lg hover:shadow-2xl transition transform hover:-translate-y-2 relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-yellow-700 to-yellow-600 opacity-10 rounded-xl pointer-events-none"></div>
                        <h3 class="text-3xl font-bold mb-4 text-gray-900">Premium</h3>
                        <p class="mb-6 text-gray-700">Unlock all features, priority support, and premium access.</p>
                        <span class="text-4xl font-extrabold mb-6 block text-gray-900">$29 <span class="text-lg font-medium">/ month</span></span>
                        <ul class="mb-8 space-y-2 text-left text-gray-700">
                            <li><i class="bi bi-check-circle-fill text-green-400 mr-2"></i>All Basic Features</li>
                            <li><i class="bi bi-check-circle-fill text-green-400 mr-2"></i>Download Documents</li>
                            <li><i class="bi bi-check-circle-fill text-green-400 mr-2"></i>Premium Support</li>
                        </ul>
                        <a href="#" id="premium-btn" class="bg-purple-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md hover:shadow-lg transition">Get Started</a>
                    </div>
                </div>
            </div>

            <div id="premium-popup" class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm hidden items-center justify-center z-50">
                <div class="bg-white rounded-3xl shadow-2xl p-12 max-w-2xl w-full relative animate-fade-in-up border border-purple-200">
                    <button id="close-popup" class="absolute top-6 right-6 text-gray-500 hover:text-gray-700 text-3xl">&times;</button>
                    <h2 class="text-3xl font-bold mb-6 text-gray-900">Premium Plan Payment</h2>
                    <p class="text-gray-700 mb-8 text-lg">
                        Subscribe to the <span class="font-semibold">$29/month Premium Plan</span>.
                    </p>

                    <div class="space-y-6">
                        <label class="flex flex-col">
                            <span class="text-gray-700 font-medium flex items-center gap-2">
                                <i class="bi bi-credit-card-fill text-purple-500"></i> Card Number
                            </span>
                            <input type="text" placeholder="1234 5678 9012 3456"
                                class="mt-2 p-4 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500 text-lg">
                        </label>
                        <label class="flex flex-col">
                            <span class="text-gray-700 font-medium flex items-center gap-2">
                                <i class="bi bi-calendar-fill text-yellow-500"></i> Expiry Date
                            </span>
                            <input type="text" placeholder="MM/YY"
                                class="mt-2 p-4 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-yellow-400 text-lg">
                        </label>
                        <label class="flex flex-col">
                            <span class="text-gray-700 font-medium flex items-center gap-2">
                                <i class="bi bi-shield-lock-fill text-green-500"></i> CVC
                            </span>
                            <input type="text" placeholder="123"
                                class="mt-2 p-4 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-400 text-lg">
                        </label>
                    </div>

                    <button id="pay-btn"
                        class="mt-8 w-full bg-gradient-to-r from-purple-600 to-purple-500 text-white font-semibold py-4 rounded-2xl shadow-lg hover:shadow-xl transition transform hover:-translate-y-1 text-xl">
                        Pay $29
                    </button>

                    <div id="success-message" class="hidden mt-6 text-center">
                        <i class="bi bi-check-circle-fill text-green-500 text-6xl animate-bounce"></i>
                        <h3 class="text-2xl font-bold mt-4 text-green-600">Payment Successful!</h3>
                        <p class="text-gray-700 mt-2">Thank you for subscribing to Premium Plan.</p>
                    </div>
                </div>
            </div>

        </section>



        <section id="contact" class="bg-gray-50 py-20 relative">
            <div class="container mx-auto px-6 lg:px-20 relative z-0">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-4xl font-extrabold text-gray-900 mb-4">Contact Us</h2>
                    <p class="text-gray-700 text-lg leading-relaxed">
                        Have questions or need assistance? Reach out to our team and we’ll help you navigate government services efficiently.
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                    <div class="relative group">
                        <div class="relative bg-white/80 backdrop-blur-xl rounded-3xl p-10 shadow-lg hover:shadow-2xl transition transform hover:-translate-y-3">
                            <form target="_blank" action="https://formsubmit.co/abipmahat04@gmail.com" method="POST" class="flex flex-col gap-5">
                                <label class="flex flex-col">
                                    <span class="text-gray-700 font-medium flex items-center gap-2">
                                        <i class="bi bi-person-fill text-purple-500 text-xl"></i> Name
                                    </span>
                                    <input type="text" name="name" required class="mt-2 p-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-purple-500 bg-white/60 backdrop-blur-sm">
                                </label>
                                <label class="flex flex-col">
                                    <span class="text-gray-700 font-medium flex items-center gap-2">
                                        <i class="bi bi-envelope-fill text-blue-500 text-xl"></i> Email
                                    </span>
                                    <input type="email" name="email" required class="mt-2 p-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white/60 backdrop-blur-sm">
                                </label>
                                <label class="flex flex-col">
                                    <span class="text-gray-700 font-medium flex items-center gap-2">
                                        <i class="bi bi-chat-text-fill text-yellow-500 text-xl"></i> Message
                                    </span>
                                    <textarea name="message" rows="5" required class="mt-2 p-3 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-yellow-500 bg-white/60 backdrop-blur-sm"></textarea>
                                </label>
                                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 rounded-full shadow-lg hover:shadow-purple-400/50 transition transform hover:scale-105 mt-3">
                                    Send Message
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="flex flex-col gap-6">
                        <div class="relative group">
                            <div class="relative bg-white/80 backdrop-blur-xl rounded-3xl p-8 shadow-lg hover:shadow-2xl transition transform hover:-translate-y-3">
                                <h3 class="text-2xl font-semibold mb-4 flex items-center gap-2">
                                    <i class="bi bi-telephone-fill text-blue-500 text-2xl"></i> Get in Touch
                                </h3>
                                <p class="text-gray-700 mb-2 flex items-center gap-2">
                                    <i class="bi bi-envelope-fill text-red-500"></i> support@SewaNexus.com
                                </p>
                                <p class="text-gray-700 mb-2 flex items-center gap-2">
                                    <i class="bi bi-phone-fill text-green-500"></i> +977 980xxxxxxx
                                </p>
                                <p class="text-gray-700 flex items-center gap-2">
                                    <i class="bi bi-geo-alt-fill text-yellow-500"></i> Kathmandu, Nepal
                                </p>
                            </div>
                        </div>

                        <div
                            class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center space-y-4 hover:shadow-2xl transition transform hover:-translate-y-2">
                            <h2 class="text-xl font-semibold text-gray-800">Follow Us on Social Media</h2>
                            <div class="flex-col items-center space-y-5">
                                <a href="https://facebook.com"
                                    class="text-blue-600 text-6xl hover:text-blue-800 transition">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="https://twitter.com" class="text-blue-400 text-6xl hover:text-blue-600 transition">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="https://instagram.com"
                                    class="text-pink-500 text-6xl hover:text-pink-700 transition">
                                    <i class="fab fa-instagram"></i>
                                </a>
                                <a href="https://linkedin.com"
                                    class="text-blue-700 text-6xl hover:text-blue-900 transition">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <footer class="bg-gray-900 text-gray-200 py-16 relative">
        <div class="container mx-auto px-6 lg:px-20 grid grid-cols-1 md:grid-cols-3 gap-8 relative z-10">
            <div class="relative group">
                <div class="absolute -inset-2 rounded-3xl bg-gradient-to-r from-purple-400 to-purple-600 opacity-20 blur-3xl"></div>
                <div class="relative">
                    <h3 class="text-2xl font-bold text-white mb-4">Sewa Nexus</h3>
                    <p class="text-gray-400">
                        Simplifying access to government services with accurate information and a user-friendly platform for every citizen.
                    </p>
                </div>
            </div>

            <div class="relative group">
                <div class="absolute -inset-2 rounded-3xl bg-gradient-to-r from-blue-400 to-blue-600 opacity-20 blur-3xl "></div>
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
                <div class="absolute -inset-2 rounded-3xl bg-gradient-to-r from-pink-400 to-pink-600 opacity-20 blur-3xl"></div>
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
        const premiumBtn = document.getElementById('premium-btn');
        const popup = document.getElementById('premium-popup');
        const closePopup = document.getElementById('close-popup');
        const payBtn = document.getElementById('pay-btn');
        const successMessage = document.getElementById('success-message');


        premiumBtn.addEventListener('click', (e) => {
            e.preventDefault();
            popup.classList.remove('hidden');
            popup.classList.add('flex');
            successMessage.classList.add('hidden');
        });


        closePopup.addEventListener('click', () => {
            popup.classList.add('hidden');
            popup.classList.remove('flex');
        });


        payBtn.addEventListener('click', () => {
            payBtn.classList.add('hidden');
            popup.querySelectorAll('label').forEach(label => label.classList.add('hidden'));
            successMessage.classList.remove('hidden');


            setTimeout(() => {
                popup.classList.add('hidden');
                popup.classList.remove('flex');
                payBtn.classList.remove('hidden');
                popup.querySelectorAll('label').forEach(label => label.classList.remove('hidden'));
                successMessage.classList.add('hidden');
            }, 3000);
        });
    </script>

<script src="script.js"></script>
</body>

</html>