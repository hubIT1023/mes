<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Register - Factory Dashboard</title>

  <!-- Google Fonts: Inter (clean modern font) -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    body {
      font-family: 'Inter', sans-serif;
    }

    .illustration {
      background-image: url('https://unblast.com/wp-content/uploads/2020/08/Artificial-Intelligence-Illustration-1.jpg'); /* Tech-themed image */
      background-size: contain;
      background-position: center;
      background-repeat: no-repeat;
      height: 300px;
      opacity: 0.9;
      margin-bottom: 2rem;
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



  <!-- Form Container -->
  <div class="w-full max-w-md bg-white p-8 form-container transition-all duration-300">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Create Your Account</h2>

    <?php
    if (isset($_GET['error'])) {
        echo '<div class="bg-red-50 text-red-600 p-3 mb-6 rounded-lg border border-red-100 text-sm">
                <strong>Error:</strong> ' . htmlspecialchars($_GET['error']) . '
              </div>';
    }
    if (isset($_GET['success'])) {
        echo '<div class="bg-green-50 text-green-600 p-3 mb-6 rounded-lg border border-green-100 text-sm">
                <strong>Success:</strong> ' . htmlspecialchars($_GET['success']) . '
              </div>';
    }
    ?>

    <form action="/mes/register" method="POST" autocomplete="off">
      <!-- Organization Name -->
      <div class="mb-5">
        <label for="org_name" class="block mb-2 text-sm font-medium text-gray-700">Organization Name</label>
        <input type="text" name="org_name" id="org_name" required
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 transition"
               placeholder="e.g. NovaTech Industries" />
      </div>

      <!-- Alias -->
      <div class="mb-5">
        <label for="org_alias" class="block mb-2 text-sm font-medium text-gray-500">Organization Alias (Optional)</label>
        <input type="text" name="org_alias" id="org_alias"
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 transition"
               placeholder="e.g. NVT" />
      </div>

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
        <input type="password" name="password" id="password" required minlength="8"
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-200 transition"
               placeholder="••••••••" />
        <small class="text-gray-500 mt-2 block">Minimum 8 characters</small>
      </div>

      <!-- Submit Button -->
      <button type="submit" class="w-full btn-primary py-3 rounded-lg font-semibold text-lg transition-all duration-300 shadow">
        Register
      </button>
    </form>
  </div>

  <!-- Sign In Link -->
  <p class="mt-6 text-center text-gray-600">
    Already have an account?
    <a href="signin" class="text-indigo-600 hover:text-indigo-800 font-semibold">Sign In</a>
  </p>

</body>
</html>
