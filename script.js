const items = document.querySelectorAll(".faq-item");

items.forEach(item => {
    item.querySelector(".faq-question").addEventListener("click", () => {
        item.classList.toggle("active");
    });
});

const toTopBtn = document.getElementById("toTopBtn");

window.addEventListener("scroll", () => {
    if (window.scrollY > 300) {
        toTopBtn.style.display = "block";
    } else {
        toTopBtn.style.display = "none";
    }
});

toTopBtn.addEventListener("click", () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
});


const authButton = document.getElementById("auth-button");
const authModal = document.getElementById("auth-modal");
const closeModal = document.getElementById("close-modal");
const closeModalSignup = document.getElementById("close-modal-signup");

const loginSection = document.getElementById("login-section");
const signupSection = document.getElementById("signup-section");
const authIllustration = document.getElementById("auth-illustration");

const showSignupLink = document.getElementById("show-signup-link");
const showLoginLink = document.getElementById("show-login-link");

const loginBtn = document.getElementById("login-btn");
const signupBtn = document.getElementById("signup-btn");

// ------------------- Toast System (Tailwind slide-in top-right) -------------------
function showToast(message, type = "success") {
    // Create container if not exists
    let container = document.getElementById("toast-container");
    if (!container) {
        container = document.createElement("div");
        container.id = "toast-container";
        container.className = "fixed top-6 right-6 flex flex-col gap-4 z-50";
        document.body.appendChild(container);
    }

    // Create toast
    const toast = document.createElement("div");
    toast.className = `max-w-xs w-full px-6 py-3 rounded-lg shadow-lg text-white font-semibold transform transition-all duration-300 ${type === "success" ? "bg-green-600" : "bg-red-600"
        } translate-x-24 opacity-0`;
    toast.innerText = message;

    container.appendChild(toast);

    // Slide in
    setTimeout(() => {
        toast.classList.remove("translate-x-24", "opacity-0");
        toast.classList.add("translate-x-0", "opacity-100");
    }, 50);

    // Auto remove
    setTimeout(() => {
        toast.classList.add("translate-x-24", "opacity-0");
        setTimeout(() => toast.remove(), 500);
    }, 3000);
}

// ------------------- Modal Functions -------------------
const resetModal = () => {
    loginSection.classList.remove("hidden");
    signupSection.classList.add("hidden");

    document.getElementById("login-email").value = "";
    document.getElementById("login-password").value = "";
    document.getElementById("signup-name").value = "";
    document.getElementById("signup-email").value = "";
    document.getElementById("signup-password").value = "";
};

authButton?.addEventListener("click", () => {
    resetModal();
    authModal.style.display = "flex";
});

const closeAll = () => {
    authModal.style.display = "none";
    resetModal();
};
closeModal?.addEventListener("click", closeAll);
closeModalSignup?.addEventListener("click", closeAll);

showSignupLink?.addEventListener("click", (e) => {
    e.preventDefault();
    loginSection.classList.add("hidden");
    signupSection.classList.remove("hidden");
});

showLoginLink?.addEventListener("click", (e) => {
    e.preventDefault();
    signupSection.classList.add("hidden");
    loginSection.classList.remove("hidden");
});

function changeIllustration(newSrc) {
    authIllustration.style.opacity = "0";
    setTimeout(() => {
        authIllustration.src = newSrc;
        authIllustration.style.opacity = "1";
    }, 300);
}

// ------------------- Login -------------------
loginBtn?.addEventListener("click", async () => {
    const email = document.getElementById("login-email").value.trim();
    const password = document.getElementById("login-password").value;
    if (!email || !password) return showToast("Please fill all fields", "danger");

    try {
        const res = await fetch("auth.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({ action: "login", email, password }),
        });
        const text = await res.text();
        if (text.includes("success")) {
            showToast("Login successful!", "success");
            window.location.href = "dashboard.php";
        } else {
            showToast(text, "danger");
        }
    } catch (err) {
        showToast("Login failed. Try again.", "danger");
    }
});

// ------------------- Signup -------------------
signupBtn?.addEventListener("click", async () => {
    const name = document.getElementById("signup-name").value.trim();
    const email = document.getElementById("signup-email").value.trim();
    const password = document.getElementById("signup-password").value;
    if (!email || !password)
        return showToast("Please fill all required fields", "danger");

    try {
        const res = await fetch("auth.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({ action: "signup", name, email, password }),
        });
        const text = await res.text();
        if (text.includes("success")) {
            showToast("Signup successful! Please login.", "success");
            signupSection.classList.add("hidden");
            loginSection.classList.remove("hidden");
        } else {
            showToast(text, "danger");
        }
    } catch (err) {
        showToast("Signup failed. Try again.", "danger");
    }
});

// ------------------- Google One Tap -------------------
window.handleCredentialResponse = async (response) => {
    try {
        const id_token = response.credential;
        const res = await fetch("auth.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({ action: "google", id_token }),
        });
        const txt = await res.text();
        if (txt.includes("success")) {
            showToast("Google sign-in successful!", "success");
            window.location.href = "dashboard.php";
        } else {
            showToast(txt, "danger");
        }
    } catch (e) {
        showToast("Google sign-in failed.", "danger");
    }
};

// ESC key to close modal
window.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeAll();
});

//Forgot Password


const forgotSection = document.getElementById("forgot-section");
const forgotBtn = document.getElementById("forgot-btn");
const forgotLink = document.getElementById("forgot-password-link");
const backLoginLink = document.getElementById("back-login-link");
const closeForgot = document.getElementById("close-modal-forgot");

// Show forgot section
forgotLink?.addEventListener("click", (e) => {
    e.preventDefault();
    loginSection.classList.add("hidden");
    signupSection.classList.add("hidden");
    forgotSection.classList.remove("hidden");
});

// Back to login
backLoginLink?.addEventListener("click", (e) => {
    e.preventDefault();
    forgotSection.classList.add("hidden");
    loginSection.classList.remove("hidden");
});

// Close forgot modal
closeForgot?.addEventListener("click", () => {
    forgotSection.classList.add("hidden");
    authModal.style.display = "none";
    resetModal(); // optional
});

// Forgot password button click
forgotBtn?.addEventListener("click", async () => {
    const email = forgotEmail.value.trim();
    if (!email) return showToast("Please enter your email", "danger");

    const res = await fetch("auth.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ action: "forgot_password", email }),
    });
    const text = await res.text();
    showToast(
        text.includes("success") ? "Reset link sent! Check your email." : text,
        text.includes("success") ? "success" : "danger"
    );

    if (text.includes("success")) forgotEmail.value = "";
});

// Handle sending forgot password request
forgotBtn?.addEventListener("click", async () => {
    const email = document.getElementById("forgot-email").value.trim();
    if (!email) return showToast("Email is required", "danger");

    try {
        const res = await fetch("auth.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({ action: "forgot_password", email }),
        });
        const text = await res.text();
        if (text.includes("success")) {
            showToast("Password reset link sent to your email!", "success");
            forgotSection.classList.add("hidden");
            loginSection.classList.remove("hidden");
        } else {
            showToast(text, "danger");
        }
    } catch (e) {
        showToast("Failed to send reset link. Try again.", "danger");
    }
});

