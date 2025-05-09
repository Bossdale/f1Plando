<?php
    include __DIR__ . '/connect.php';
    session_start();
    $error = $_SESSION['error'] ?? '';
    unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StoreStock - Sign In</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
    <body class="bg-white-100 font-[Inter]">
        <div class="relative w-full h-screen overflow-hidden">
            <!-- Video Background -->
            <video autoplay muted loop playsinline
                class="absolute top-0 left-0 w-full max-h-[850px] h-[450px]  object-cover brightness-75 z-0">
                <source src="sari-sari.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>

            <!-- Sign-in Form (Centered Over Video) -->
            <div class="absolute inset-0 flex items-center justify-center z-10">
            <div class="bg-white border border-gray-200 rounded-2xl shadow-xl p-8 w-full max-w-md">
                <h2 class="text-2xl font-semibold text-red text-center mb-6">Sign In</h2>

                    <?php if (!empty($error)): ?>
                        <div class="bg-red-200 border border-red-400 text-white-800 px-4 py-3 rounded relative mb-4 text-sm">
                            <strong class="font-bold">Error:</strong>
                            <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                        </div>
                    <?php endif; ?>

                    <form action="process_signin.php" method="post" class="space-y-4">
                        <div>
                            <label for="username" class="block text-red text-sm font-medium">Username or Email</label>
                            <input type="text" name="username" id="username"
                                class="w-full px-4 py-2 rounded-md bg-red/70 border border-red/30 text-gray-800 focus:outline-none focus:ring-2 focus:ring-red-400"
                                required>
                        </div>

                        <div>
                            <label for="password" class="block text-red text-sm font-medium">Password</label>
                            <input type="password" name="password" id="password"
                                class="w-full px-4 py-2 rounded-md bg-red/70 border border-red/30 text-gray-800 focus:outline-none focus:ring-2 focus:ring-red-400"
                                required>
                        </div>

                        <div>
                            <label for="role" class="block text-red text-sm font-medium">Role</label>
                            <select name="role" id="role"
                                class="w-full px-4 py-2 rounded-md bg-red/70 border border-red/30 text-gray-800 focus:outline-none focus:ring-2 focus:ring-red-400">
                                <option value="owner">Owner</option>
                                <option value="supplier">Supplier</option>
                            </select>
                        </div>

                        <button type="submit"
                            class="w-full py-2 px-4 text-white font-semibold rounded-md shadow-md transition duration-300 hover:opacity-90"
                            style="background: linear-gradient(to right, #800000, #6a0000, #4b0000);">
                            Sign In
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <footer class="absolute bottom-0 w-full text-center py-4 bg-white/10 backdrop-blur-md text-red text-sm z-10">
            © 2025 StoreStock. All rights reserved.
        </footer>
    </body>

</html>
