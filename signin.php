<?php
    // No need to include process_signin.php here
    include __DIR__ . '/connect.php';
    session_start();
 
    // Optional: Handle any session-based error messages
    $error = $_SESSION['error'] ?? '';
    unset($_SESSION['error']); // Clear the error message after displaying
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@tailwindcss/browser@latest"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .signin-container {
            width: 400px;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #4a5568;
            font-weight: 600;
            font-size: 0.875rem;
        }
        input[type="text"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #d2d6dc;
            border-radius: 5px;
            box-sizing: border-box;
            color: #2d3748;
            font-size: 1rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        input[type="text"]:focus,
        input[type="password"]:focus,
        select:focus {
            outline: none;
            border-color: #4299e1;
            box-shadow: 0 0 0 0.2rem rgba(66, 153, 225, 0.25);
        }
        .btn-primary {
            background-color: #4299e1;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
            transition: background-color 0.15s ease-in-out;
        }
        .btn-primary:hover {
            background-color: #3182ce;
        }
        .alert-danger {
            background-color: #fde2e2;
            color: #c53030;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #e53e3e;
        }
        .text-center {
            text-align: center;
            color: #2d3748;
            margin-bottom: 20px;
            font-size: 1.5rem;
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h2 class="text-2xl font-semibold mb-6 text-center text-gray-800">Sign In</h2>
 
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error:</strong>
                <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
 
        <form action="process_signin.php" method="post">
            <div class="mb-4">
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username or Email:</label>
                <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="username" name="username" required>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
                <input type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="password" name="password" required>
            </div>
            <div class="mb-6">
                <label for="role" class="block text-gray-700 text-sm font-bold mb-2">Role:</label>
                <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="role" name="role">
                    <option value="owner">Owner</option>
                    <option value="supplier">Supplier</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">Sign In</button>
        </form>
    </div>
</body>
</html>