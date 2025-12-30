<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Sign In - Factory Dashboard</title>

  <!-- Google Fonts: Inter (modern, clean) -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    body {
      font-family: 'Inter', sans-serif;
    }

    .illustration {
      background-image: url('https://unblast.com/wp-content/uploads/2020/07/Dashboard-Illustration-1.jpg');
      background-size: contain;
      background-position: center;
      background-repeat: no-repeat;
      height: 280px;
      opacity: 0.95;
    }

    .form-container {
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
      border-radius: 16px;
      border: 1px solid #e5e5e5;
    }

    .form-container input:focus {
      border-color: #4f46e5;
      box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
    }

    .btn-primary {
      background: linear-gradient(to right, #4f46e5, #7c3aed);
      color: white;
    }

    .btn-primary:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 14px rgba(79, 70, 229, 0.25);
    }
  </style>
</head>
<body class="bg-gray-50 flex flex-col items-center justify-center min-h-screen px-6 py-12">

  <!-- Sign-In Form -->
  <div class="w-full max-w-md bg-white p-8 form-container transition-all duration-300">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Welcome Back</h2>
    <p class="text-gray-500 text-sm text-center mb-6">Sign in to your account to continue</p>

    <?php
    if (isset($_GET['error'])) {
        echo '<div class="bg-red-50 text-red-600 p-3 mb-6 rounded-lg border border-red-100 text-sm">
                <strong>Error:</strong> ' . htmlspecialchars($_GET['error']) . '
              </div>';
    }
    ?>

    <form action="<?= base_url('/signin') ?>" method="POST" autocomplete="off">
      <!-- Email -->
      <div class="mb-5">
        <label for="email" class="block mb-2 text-sm font-medium text-gray-700">Email Address</label>
        <input type="email" name="email" id="email" required
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 transition"
               placeholder="you@company.com" />
      </div>

      <!-- Password -->
      <div class="mb-6">
        <label for="password" class="block mb-2 text-sm font-medium text-gray-700">Password</label>
        <input type="password" name="password" id="password" required
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 transition"
               placeholder="••••••••" />
      </div>

      <!-- Remember Me & Forgot Link -->
      <div class="flex items-center justify-between mb-6 text-sm">
        <label class="flex items-center text-gray-600">
          <input type="checkbox" name="remember" class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
          Remember me
        </label>
        <a href="<?= base_url('/forgot_password') ?>" class="text-indigo-600 hover:text-indigo-800">Forgot password?</a>
      </div>

      <!-- Submit Button -->
      <button type="submit" class="w-full btn-primary py-3 rounded-lg font-semibold text-lg transition-all duration-300 shadow">
        Sign In
      </button>
    </form>
  </div>

  <!-- Register Link -->
  <p class="mt-6 text-center text-gray-600">
    Don’t have an account?
    <a href="<?= base_url('/register') ?>" class="text-indigo-600 hover:text-indigo-800 font-semibold">Register here</a>
  </p>

</body>
</html>