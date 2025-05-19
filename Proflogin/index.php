<?php
session_start();
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Roomify | Register & Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="style.css" />
    <style>
    * {
      box-sizing: border-box;
      font-family: "Segoe UI", sans-serif;
    }
    html, body {
      margin: 0;
      padding: 0;
      height: 100%;
    }
    body {
      display: flex;
    }
    .sidebar {
      width: 200px;
      background: #9438d1;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 20px;
    }
    .sidebar img {
      width: 90px;
      margin-bottom: 30px;
      border-radius: 50px;
    }
    .sidebar button {
      background: transparent;
      color: white;
      border: solid 1px white;
      margin: 10px 0;
      padding: 8px 16px;
      border-radius: 50px;
      cursor: pointer;
      font-weight: bold;
      font-size: 15px;
      width: 150px;
    }
    .sidebar button:hover {
      background: #5d1786;
    }
    .main-content {
      flex: 1;
      padding: 30px;
      background: #e4c7f7;
      overflow-y: auto;
    }
    .schedule-table-container {
      width: 100%;
      overflow-x: auto;
      overflow-y: hidden;
      display: block;
    }
    .schedule-table {
      min-width: 1200px;
      width: max-content;
      border-collapse: collapse;
      font-size: 0.9em;
      text-align: center;
      background-color: white; 
    }
    .schedule-table th, .schedule-table td {
      border: 1px solid #ddd;
      padding: 8px;
    }
    .schedule-table th {
      background-color: #9438d1;
      color: white;
    }
    .btn-book {
      background-color: #5d1786;
      color: white;
      padding: 5px 15px;
      border-radius: 10px;
      cursor: pointer;
    }
    .btn-book:hover {
      background-color: #7c2db0;
    }
    .booking-form {
      display: none;
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      width: 300px;
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
    }
    .booking-form input, .booking-form select {
      width: 100%;
      padding: 8px;
      margin: 8px 0;
      border-radius: 4px;
      border: 1px solid #ccc;
    }
    .booking-form button {
      width: 100%;
      background-color: #5d1786;
      color: white;
      border: none;
      padding: 10px;
      border-radius: 5px;
      cursor: pointer;
    }
    .booking-form button:hover {
      background-color: #7c2db0;
    }
    .delete-btn {
      background-color: red;
      color: white;
      border: none;
      padding: 5px 15px;
      border-radius: 10px;
      cursor: pointer;
    }
    .delete-btn:hover {
      background-color: darkred;
    }
    .success-message {
      background-color: #4CAF50;
      color: white;
      padding: 10px;
      margin-bottom: 20px;
      text-align: center;
    }
    .main-content {
      flex: 1;
      padding: 30px;
      background: #e4c7f7;
      overflow-y: auto;
    }
   
.schedule-table-container {
    width: 100%;
    height: 400px; 
    overflow: auto;  
     flex: 1;
      padding: 30px;
      background: #e4c7f7;
      overflow-y: auto;
}

.schedule-table {
    min-width: 1200px;  
    width: max-content;
    border-collapse: collapse;
    font-size: 0.9em;
    text-align: center;
    background-color: white;
}

.schedule-table th, .schedule-table td {
    border: 1px solid #ddd;
    padding: 8px;
}

.schedule-table th {
    background-color: #9438d1;
    color: white;
}

   .dark-toggle {
    position: fixed;
    bottom: 30px;
    left: 30px;
    background-color: #9438d1;
    border: none;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    font-size: 24px;
    color: white;
    cursor: pointer;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    transition: background-color 0.3s, transform 0.3s;
    z-index: 1000;
}

.dark-toggle:hover {
    background-color: #7b2bb9;
    transform: scale(1.1);
}

 
  .dark-mode {
    background-color: #2c2c2c;
    color: #eee;
  }

  .dark-mode .main-content {
    background-color: #3c3c3c;
  }

  .dark-mode .card {
    border-color: #aaa;
  }

  .dark-mode input,
  .dark-mode select {
    background-color: #555;
    color: #eee;
    border-color: #888;
  }

  .dark-mode .btn-submit {
    background-color: #5d1786;
  }

 
  .btn-submit:hover {
    background-color: #7c2db0;
  }
  
.back-btn {
    align-self: flex-start;
    margin-bottom: 10px;
    padding: 10px 20px;
    background: #f4f4f6;
    color: #333;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 15px;
    font-family: 'Segoe UI', sans-serif;
    font-weight: 500;
    letter-spacing: 0.3px;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    transition: background-color 0.2s ease, box-shadow 0.2s ease;
}

.back-btn:hover {
    background-color: #e8e8ec;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
}

.back-btn:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(100, 100, 255, 0.3);
}

 
@media (max-width: 600px) {
  .container {
    flex-direction: column;
    padding: 1.5rem 1rem;
    height: auto;
    max-width: 95%;
    gap: 1.5rem;
    align-items: center;
  }

  .form-container,
  .logo-container {
    width: 100%;
  }

  .logo {
    width: 140px;
  }

  .btn {
    width: 100%;
    font-size: 1rem;
    margin-left: 0;
  }

  input {
    font-size: 0.9rem;
    padding: 0.5rem;
  }

  h1 {
    font-size: 1.4rem;
  }

  .dark-toggle {
    bottom: 20px;
    left: 20px;
    font-size: 1rem;
    padding: 0.5rem 0.8rem;
  }
}
  </style>
    
</head>
<body>
    <div style="text-align: left; margin: 20px;">
    <button onclick="window.location.href='http://localhost/Project/choices/'"
        style="position: absolute; top: 20px; left: 20px; padding: 10px 20px; border: none; background: #9d4edd; color: white; border-radius: 8px; cursor: pointer;">
      ← Back
    </button>
</div> 
    <button class="dark-toggle" onclick="toggleTheme()">🌙</button>
    <div class="bubbles" id="bubbles"></div>

    <div class="container" id="signup" style="display:none;">
        <div class="logo-container">
            <img src="logo.PNG" alt="Roomify Logo" class="logo" />
        </div>
        <div class="form-container">
            <h1>Register</h1>
            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="post" action="register.php">
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="fName" placeholder="First Name" required />
                </div>
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="lName" placeholder="Last Name" required />
                </div>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email" required />
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required />
                </div>
                <input type="submit" class="btn" value="Sign Up" name="signUp"/>
            </form>
            <div class="links">
                <p>Already have an account?</p>
                <button id="signInButton">Sign In</button>
            </div>
        </div>
    </div>

    <div class="container" id="signIn">
        <div class="logo-container">
            <img src="logo.PNG" alt="Roomify Logo" class="logo" />
        </div>
        <div class="form-container">
            <h1>Sign In</h1>
            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="post" action="register.php">
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email" required />
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required />
                </div>
                <input type="submit" class="btn" value="Sign In" name="signIn"/>
            </form>
            <div class="links">
                <p>Don't have an account?</p>
                <button id="signUpButton">Sign Up</button>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
    
    <script>
        function createBubbles() {
            const bubbleContainer = document.getElementById("bubbles");
            for (let i = 0; i < 40; i++) {
                const bubble = document.createElement("div");
                bubble.classList.add("bubble");
                const size = 30 + Math.random() * 40;
                bubble.style.width = `${size}px`;
                bubble.style.height = `${size}px`;
                bubble.style.left = `${Math.random() * 100}%`;
                bubble.style.animationDuration = `${6 + Math.random() * 6}s`;
                bubble.style.animationDelay = `${Math.random() * 6}s`;
                bubbleContainer.appendChild(bubble);
            }
        }
        createBubbles();

         window.onload = function () {
      const container = document.querySelector(".schedule-table-container");
      if (container) {
        container.scrollLeft = 0;
      }
    };
    </script>
    <script>
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
</script>

</body>
</html>
