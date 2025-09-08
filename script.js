document.addEventListener("DOMContentLoaded", () => {
  const preloader = document.getElementById("preloader");
  preloader.style.display = "flex"; 
});

window.addEventListener("load", () => {
  const preloader = document.getElementById("preloader");
  preloader.classList.add("fade-out");
  preloader.addEventListener("transitionend", () => {
    preloader.remove();
  });
});

const progressBar = document.getElementById("scroll-progress");
const percentText = document.getElementById("scroll-percent");

gsap.to(progressBar, {
  width: "100%",
  ease: "none",
  scrollTrigger: { scrub: 0.3 },
});

gsap.registerPlugin(ScrollTrigger);

gsap.utils.toArray("section").forEach((el) => {
  gsap.to(el, {
    opacity: 1,
    y: 0,
    duration: 1,
    ease: "power3.out",
    scrollTrigger: {
      trigger: el,
      start: "top 80%",
      toggleActions: "play none none reverse",
    },
  });
});

const lenis = new Lenis({
  duration: 1.2,
  easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
  smooth: true,
});

function raf(time) {
  lenis.raf(time);
  requestAnimationFrame(raf);
}

requestAnimationFrame(raf);

ScrollTrigger.scrollerProxy(document.body.section, {
  scrollTop(value) {
    return arguments.length ? lenis.scrollTo(value) : lenis.scroll;
  },
  getBoundingClientRect() {
    return {
      top: 0,
      left: 0,
      width: window.innerWidth,
      height: window.innerHeight,
    };
  },
});


document.querySelectorAll('.faq-question').forEach(q => {
    q.addEventListener('click', () => {
        q.parentElement.classList.toggle('active');
    });
});

const toTopBtn = document.getElementById("toTopBtn");
window.addEventListener("scroll", () => {
  toTopBtn.style.display = window.scrollY > 300 ? "block" : "none";
});
toTopBtn.addEventListener("click", () => {
  window.scrollTo({ top: 0, behavior: "smooth" });
});

function showToast(message, type = "info") {
  let container = document.getElementById("toast-container");
  if (!container) {
    container = document.createElement("div");
    container.id = "toast-container";
    container.className = "fixed top-6 right-6 flex flex-col gap-4 z-50";
    document.body.appendChild(container);
  }

  const colors = {
    success: "bg-green-600",
    error: "bg-red-600",
    warning: "bg-yellow-500 text-black",
    info: "bg-blue-600",
  };

  const toast = document.createElement("div");
  toast.className = `max-w-xs w-full px-4 py-3 rounded shadow text-white font-medium transition-all duration-300 ${
    colors[type] || colors.info
  }`;
  toast.textContent = message;

  container.appendChild(toast);
  setTimeout(() => toast.classList.add("opacity-0", "translate-x-4"), 2500);
  setTimeout(() => toast.remove(), 3000);
}

function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

const authModal = document.getElementById("auth-modal");
const authBox = document.querySelector(".auth-box");
const loginSection = document.getElementById("login-section");
const signupSection = document.getElementById("signup-section");
const forgotSection = document.getElementById("forgot-section");

function resetModal() {
  loginSection.classList.remove("hidden");
  signupSection.classList.add("hidden");
  forgotSection.classList.add("hidden");

  [
    "login-email",
    "login-password",
    "signup-name",
    "signup-email",
    "signup-password",
    "forgot-email",
  ].forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.value = "";
  });
}

document.getElementById("auth-button")?.addEventListener("click", () => {
  resetModal();
  authModal.style.display = "flex";
  authBox.classList.add("show-modal");
});

function closeAll() {
  authBox.classList.remove("show-modal");
  setTimeout(() => {
    authModal.style.display = "none";
    resetModal();
  }, 300); 
}

["close-modal", "close-modal-signup", "close-modal-forgot"].forEach((id) => {
  document.getElementById(id)?.addEventListener("click", closeAll);
});

document
  .getElementById("show-signup-link")
  ?.addEventListener("click", function (e) {
    e.preventDefault();
    loginSection.classList.remove("visible");
    loginSection.classList.add("hidden");

    signupSection.classList.remove("hidden");
    signupSection.classList.add("visible");
  });

document
  .getElementById("show-login-link")
  ?.addEventListener("click", function (e) {
    e.preventDefault();
    signupSection.classList.remove("visible");
    signupSection.classList.add("hidden");

    loginSection.classList.remove("hidden");
    loginSection.classList.add("visible");
  });

document
  .getElementById("forgot-password-link")
  ?.addEventListener("click", function (e) {
    e.preventDefault();
    loginSection.classList.add("hidden");
    forgotSection.classList.remove("hidden");
  });

document
  .getElementById("back-login-link")
  ?.addEventListener("click", function (e) {
    e.preventDefault();
    forgotSection.classList.add("hidden");
    loginSection.classList.remove("hidden");
  });

window.addEventListener("keydown", function (e) {
  if (e.key === "Escape") closeAll();
});

document.getElementById("login-btn")?.addEventListener("click", function () {
  const email = document.getElementById("login-email").value.trim();
  const password = document.getElementById("login-password").value;

  if (!email || !password)
    return showToast("Please fill all fields", "warning");
  if (!isValidEmail(email)) return showToast("Invalid email format", "error");

  const xhr = new XMLHttpRequest();
  xhr.open("POST", "auth.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4) {
      if (xhr.status === 200 && xhr.responseText.includes("success")) {
        showToast("Login successful!", "success");
        window.location.href = "dashboard.php";
      } else {
        showToast("Sorry, couldn't login. Please try again.", "error");
      }
    }
  };
  xhr.send(
    "action=login&email=" +
      encodeURIComponent(email) +
      "&password=" +
      encodeURIComponent(password)
  );
});

document.getElementById("signup-btn")?.addEventListener("click", function () {
  const name = document.getElementById("signup-name").value.trim();
  const email = document.getElementById("signup-email").value.trim();
  const password = document.getElementById("signup-password").value;

  if (!name || !email || !password)
    return showToast("Please fill all required fields", "warning");
  if (!isValidEmail(email)) return showToast("Invalid email format", "error");
  if (password.length < 6)
    return showToast("Password must be at least 6 characters", "warning");

  const xhr = new XMLHttpRequest();
  xhr.open("POST", "auth.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4) {
      if (xhr.status === 200 && xhr.responseText.includes("success")) {
        showToast("Signup successful! Please login.", "success");
        signupSection.classList.add("hidden");
        loginSection.classList.remove("hidden");
      } else {
        showToast(xhr.responseText || "Signup failed", "error");
      }
    }
  };
  xhr.send(
    "action=signup&name=" +
      encodeURIComponent(name) +
      "&email=" +
      encodeURIComponent(email) +
      "&password=" +
      encodeURIComponent(password)
  );
});

document.getElementById("forgot-btn")?.addEventListener("click", function () {
  const email = document.getElementById("forgot-email").value.trim();
  if (!email) return showToast("Please enter your email", "warning");
  if (!isValidEmail(email)) return showToast("Invalid email format", "error");

  const xhr = new XMLHttpRequest();
  xhr.open("POST", "auth.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4) {
      if (xhr.status === 200 && xhr.responseText.includes("success")) {
        showToast("Reset link sent! Check your email.", "success");
        forgotSection.classList.add("hidden");
        loginSection.classList.remove("hidden");
      } else {
        showToast(xhr.responseText || "Failed to send reset link", "error");
      }
    }
  };
  xhr.send("action=forgot_password&email=" + encodeURIComponent(email));
});

window.handleCredentialResponse = function (response) {
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "auth.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4) {
      if (xhr.status === 200 && xhr.responseText.includes("success")) {
        showToast("Google sign-in successful!", "success");
        window.location.href = "dashboard.php";
      } else {
        showToast("Google sign-in failed.", "error");
      }
    }
  };
  xhr.send("action=google&id_token=" + encodeURIComponent(response.credential));
};
