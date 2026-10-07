<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Studio Chat</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #fafafa;
            color: #111827;
        }
        h1, h2, h3 {
            font-family: 'Instrument Serif', serif;
        }
        .btn-primary {
            background-color: #111827;
            color: #ffffff;
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            background-color: #374151;
            transform: translateY(-1px);
        }
        .input-border {
            border: 1px solid #e5e7eb;
            transition: all 0.2s ease;
        }
        .input-border:focus {
            border-color: #111827;
            box-shadow: 0 0 0 1px #111827;
            outline: none;
        }
        .demo-btn {
            border: 1px solid #e5e7eb;
            background: #ffffff;
            transition: all 0.2s ease;
        }
        .demo-btn:hover {
            border-color: #9ca3af;
            background: #f9fafb;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-sm">
        <div class="mb-10 text-center">
            <h1 class="text-5xl font-normal tracking-tight mb-2">Studio Chat.</h1>
            <p class="text-slate-500 text-sm">End-to-end encrypted messaging.</p>
        </div>

        <form action="{{ route('login') }}" method="POST" class="space-y-5 bg-white p-8 rounded-none border border-slate-200 shadow-sm">
            @csrf
            
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 p-3 text-sm mb-4">
                    {{ $errors->first() }}
                </div>
            @endif

            <div>
                <label for="email" class="block text-xs font-medium text-slate-700 mb-1 uppercase tracking-wider">Email Address</label>
                <input type="email" name="email" id="email" required value="{{ old('email') }}"
                    class="input-border w-full px-4 py-2.5 text-slate-900 placeholder-slate-400 bg-slate-50" 
                    placeholder="you@example.com">
            </div>

            <div>
                <label for="password" class="block text-xs font-medium text-slate-700 mb-1 uppercase tracking-wider">Password</label>
                <input type="password" name="password" id="password" required 
                    class="input-border w-full px-4 py-2.5 text-slate-900 placeholder-slate-400 bg-slate-50" 
                    placeholder="••••••••">
            </div>

            <div class="pt-4">
                <button type="submit" class="btn-primary w-full py-3 text-sm font-medium tracking-wide">
                    Sign In
                </button>
            </div>
        </form>

        <div class="mt-8">
            <p class="text-xs text-slate-500 uppercase tracking-wider text-center mb-4">Demo Accounts</p>
            <div class="flex flex-col gap-3">
                <button type="button" class="demo-btn w-full py-2.5 px-4 text-sm font-medium text-slate-700 flex justify-between items-center" onclick="document.getElementById('email').value='budi@chat.com'; document.getElementById('password').value='password123';">
                    <span>Budi Santoso</span>
                    <span class="text-xs text-slate-400">budi@chat.com</span>
                </button>
                <button type="button" class="demo-btn w-full py-2.5 px-4 text-sm font-medium text-slate-700 flex justify-between items-center" onclick="document.getElementById('email').value='siti@chat.com'; document.getElementById('password').value='password456';">
                    <span>Siti Aminah</span>
                    <span class="text-xs text-slate-400">siti@chat.com</span>
                </button>
            </div>
        </div>
    </div>

</body>
</html>
