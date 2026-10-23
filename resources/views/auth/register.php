<?php $csrfToken = \App\Core\CSRF::token(); ?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — OTP Doctor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50: '#f5f3ff', 600: '#7c3aed', 700: '#6d28d9' }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-slate-50">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <div class="w-12 h-12 rounded-xl bg-brand-600 text-white font-extrabold text-xl flex items-center justify-center mx-auto shadow-sm">OD</div>
        <h2 class="mt-4 text-2xl font-bold tracking-tight text-slate-900">Create your account</h2>
        <p class="mt-1 text-sm text-slate-500">Sign up for instant SMS verification numbers</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow-sm border border-slate-200 sm:rounded-xl sm:px-10">
            <?php if (!empty($error)): ?>
                <div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-xs font-medium text-rose-700">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="/register" method="POST" class="space-y-4">
                <?= \App\Core\CSRF::field() ?>
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-700">Full Name</label>
                    <input id="name" name="name" type="text" required class="mt-1 block w-full px-3 py-2 border border-slate-300 rounded-lg text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-600 focus:border-brand-600">
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700">Email address</label>
                    <input id="email" name="email" type="email" required class="mt-1 block w-full px-3 py-2 border border-slate-300 rounded-lg text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-600 focus:border-brand-600">
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700">Password</label>
                    <input id="password" name="password" type="password" required minlength="6" class="mt-1 block w-full px-3 py-2 border border-slate-300 rounded-lg text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-600 focus:border-brand-600">
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-colors">
                        Create Account
                    </button>
                </div>
            </form>

            <div class="mt-6 border-t border-slate-200 pt-4 text-center">
                <p class="text-xs text-slate-600">
                    Already have an account? <a href="/login" class="font-medium text-brand-600 hover:text-brand-700">Sign in here</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
