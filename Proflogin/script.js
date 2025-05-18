const signUpButton = document.getElementById("signUpButton");
const signInButton = document.getElementById("signInButton");
const signInForm = document.getElementById("signIn");
const signUpForm = document.getElementById("signup");

signUpButton.addEventListener("click", function () {
  signInForm.style.display = "none";
  signUpForm.style.display =
    "flex";
});
signInButton.addEventListener("click", function () {
  signInForm.style.display =
    "flex";
  signUpForm.style.display = "none";
});

signUpForm.style.display = "none";
signInForm.style.display =
  "flex";

    function showBookingForm() {
        document.getElementById("bookingForm").style.display = "block";
    }

    function hideBookingForm() {
        document.getElementById("bookingForm").style.display = "none";
    }

    function confirmDelete(bookingId) {
        if (confirm("Are you sure you want to delete this booking?")) {
            document.getElementById("deleteForm-" + bookingId).submit();
        }
    }

    function signOut() {
        alert("You have signed out.");
        window.location.href = "logout.php";
    }

    setTimeout(function () {
        const msg = document.getElementById("successMessage");
        if (msg) msg.style.display = "none";
    }, 5000);

  function toggleTheme() {
    const body = document.body;
    const container = document.getElementById("main-container");
    
    body.classList.toggle("dark-mode");
    container.classList.toggle("dark-mode");

    const isDark = body.classList.contains("dark-mode");
    localStorage.setItem("darkMode", isDark ? "enabled" : "disabled");
  }

  function applySavedTheme() {
    const darkModeSetting = localStorage.getItem("darkMode");

    if (darkModeSetting === "enabled") {
      document.body.classList.add("dark-mode");
      document.getElementById("main-container").classList.add("dark-mode");
    }
  }

  applySavedTheme();
