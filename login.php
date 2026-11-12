<?php
session_start();
require 'functions.php';

// Cek Cookie (Remember Me)
if (isset($_COOKIE['id']) && isset($_COOKIE['key'])) {
    $id = $_COOKIE['id'];
    $key = $_COOKIE['key'];

    $result = mysqli_query($conn, "SELECT username FROM tb_user WHERE id = $id");
    $row = mysqli_fetch_assoc($result);

    if ($row && $key === hash('sha256', $row['username'])) {
        $_SESSION['login'] = true;
    }
}

if (isset($_SESSION["login"])) {
    header("Location: index.php");
    exit;
}

if (isset($_POST["login"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $result = mysqli_query($conn, "SELECT * FROM tb_user WHERE username = '$username'");

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row["password"])) {
            $_SESSION["login"] = true;

            if (isset($_POST['remember'])) {
                setcookie('id', $row['id'], time() + 3600, "/");
                setcookie('key', hash('sha256', $row['username']), time() + 3600, "/");
            }

            header("Location: index.php");
            exit;
        }
    }

    $error = true;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Informasi Mahasiswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --blue: #2563eb;
            --indigo: #4f46e5;
            --sky: #0ea5e9;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(120deg, #dbeafe, #eff6ff, #e0e7ff, #cffafe);
            background-size: 300% 300%;
            animation: gradient-flow 16s ease infinite;
            isolation: isolate;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            z-index: -3;
            background-image:
                linear-gradient(rgba(37, 99, 235, 0.055) 1px, transparent 1px),
                linear-gradient(90deg, rgba(37, 99, 235, 0.055) 1px, transparent 1px);
            background-size: 42px 42px;
            mask-image: linear-gradient(to bottom, black, transparent 88%);
            -webkit-mask-image: linear-gradient(to bottom, black, transparent 88%);
            animation: grid-drift 24s linear infinite;
        }

        .background-scene {
            position: fixed;
            inset: 0;
            z-index: -2;
            overflow: hidden;
            pointer-events: none;
        }

        .orb {
            position: absolute;
            border-radius: 9999px;
            filter: blur(4px);
            opacity: 0.55;
            animation: orb-float 12s ease-in-out infinite;
        }

        .orb-one {
            width: 360px;
            height: 360px;
            top: -130px;
            left: -100px;
            background: radial-gradient(circle at 35% 35%, #93c5fd, #60a5fa55 65%, transparent 70%);
        }

        .orb-two {
            width: 420px;
            height: 420px;
            right: -150px;
            bottom: -160px;
            background: radial-gradient(circle at 35% 35%, #a5b4fc, #67e8f955 65%, transparent 70%);
            animation-delay: -5s;
        }

        .floating-item {
            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(37, 99, 235, 0.52);
            background: rgba(255, 255, 255, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.65);
            box-shadow: 0 16px 45px rgba(59, 130, 246, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            animation: academic-float 7s ease-in-out infinite;
        }

        .floating-cap {
            width: 88px;
            height: 88px;
            top: 17%;
            left: 10%;
            border-radius: 28px;
            transform: rotate(-10deg);
        }

        .floating-book {
            width: 76px;
            height: 76px;
            right: 11%;
            top: 20%;
            border-radius: 24px;
            animation-delay: -2.5s;
        }

        .floating-card {
            width: 168px;
            height: 96px;
            left: 8%;
            bottom: 15%;
            border-radius: 22px;
            animation-delay: -4s;
        }

        .floating-chart {
            width: 104px;
            height: 104px;
            right: 9%;
            bottom: 14%;
            border-radius: 30px;
            animation-delay: -1s;
        }

        .student-card-lines {
            width: 100%;
            padding: 18px;
        }

        .student-card-line {
            height: 5px;
            margin-top: 8px;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.16);
        }

        .student-card-line:last-child {
            width: 60%;
        }

        .login-wrapper {
            animation: card-enter 0.85s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        .glass-card {
            position: relative;
            background: rgba(255, 255, 255, 0.76);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.85);
            box-shadow:
                0 28px 70px rgba(30, 64, 175, 0.14),
                inset 0 1px 0 rgba(255, 255, 255, 0.9);
        }

        .glass-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 12%;
            width: 76%;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 1), transparent);
        }

        .glass-card::after {
            content: "";
            position: absolute;
            width: 150px;
            height: 150px;
            top: -90px;
            right: -70px;
            border-radius: 9999px;
            background: rgba(96, 165, 250, 0.12);
            pointer-events: none;
        }

        .portal-badge {
            animation: fade-up 0.6s 0.2s both;
        }

        .status-dot {
            position: relative;
            width: 7px;
            height: 7px;
            border-radius: 9999px;
            background: #10b981;
        }

        .status-dot::after {
            content: "";
            position: absolute;
            inset: -3px;
            border: 1px solid #10b981;
            border-radius: inherit;
            animation: status-pulse 2s ease-out infinite;
        }

        .header-icon {
            background: linear-gradient(145deg, #3b82f6, #4f46e5);
            box-shadow: 0 12px 25px rgba(79, 70, 229, 0.25);
            animation: icon-enter 0.7s 0.15s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        .header-icon svg {
            animation: cap-sway 4s 1s ease-in-out infinite;
            transform-origin: 50% 70%;
        }

        .form-animate {
            opacity: 0;
            animation: fade-up 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .delay-1 { animation-delay: 0.3s; }
        .delay-2 { animation-delay: 0.38s; }
        .delay-3 { animation-delay: 0.46s; }
        .delay-4 { animation-delay: 0.54s; }
        .delay-5 { animation-delay: 0.62s; }

        .glass-input {
            background: rgba(255, 255, 255, 0.58);
            border: 1px solid rgba(203, 213, 225, 0.85);
            backdrop-filter: blur(5px);
            transition: border-color 0.25s, background 0.25s, box-shadow 0.25s, transform 0.25s;
        }

        .glass-input:focus {
            background: rgba(255, 255, 255, 0.96);
            border-color: #60a5fa;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .input-wrap:focus-within .input-icon {
            color: #3b82f6;
            transform: scale(1.08);
        }

        .input-icon {
            transition: color 0.25s, transform 0.25s;
        }

        .login-button {
            position: relative;
            overflow: hidden;
            background: linear-gradient(110deg, #1d4ed8, #4f46e5, #2563eb);
            background-size: 200% 100%;
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.24);
            transition: transform 0.2s, box-shadow 0.2s, background-position 0.5s;
        }

        .login-button::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 65%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.25), transparent);
            transform: skewX(-20deg);
            animation: button-shine 4s 1.5s ease-in-out infinite;
        }

        .login-button:hover {
            background-position: 100% 0;
            box-shadow: 0 16px 30px rgba(37, 99, 235, 0.3);
            transform: translateY(-2px);
        }

        .login-button:active {
            transform: translateY(0) scale(0.99);
        }

        .login-button:disabled {
            cursor: wait;
            opacity: 0.85;
            transform: none;
        }

        .button-content,
        .loading-content {
            position: relative;
            z-index: 1;
        }

        .loading-content {
            display: none;
        }

        .login-button.is-loading .button-content {
            display: none;
        }

        .login-button.is-loading .loading-content {
            display: inline-flex;
        }

        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-top-color: #fff;
            border-radius: 9999px;
            animation: spin 0.7s linear infinite;
        }

        .error-message {
            animation: error-shake 0.45s ease both;
        }

        .registration-link {
            animation: fade-up 0.6s 0.72s both;
        }

        .registration-link a {
            position: relative;
        }

        .registration-link a::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -2px;
            width: 100%;
            height: 1px;
            background: currentColor;
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 0.25s;
        }

        .registration-link a:hover::after {
            transform: scaleX(1);
            transform-origin: left;
        }

        @keyframes gradient-flow {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        @keyframes grid-drift {
            to { background-position: 42px 42px; }
        }

        @keyframes orb-float {
            0%, 100% { transform: translate3d(0, 0, 0) scale(1); }
            50% { transform: translate3d(20px, 25px, 0) scale(1.08); }
        }

        @keyframes academic-float {
            0%, 100% { translate: 0 0; rotate: -3deg; }
            50% { translate: 0 -16px; rotate: 3deg; }
        }

        @keyframes card-enter {
            from { opacity: 0; transform: translateY(28px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes fade-up {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes icon-enter {
            from { opacity: 0; transform: translateY(-12px) rotate(-12deg) scale(0.8); }
            to { opacity: 1; transform: translateY(0) rotate(0) scale(1); }
        }

        @keyframes cap-sway {
            0%, 100% { transform: rotate(0); }
            50% { transform: rotate(6deg); }
        }

        @keyframes status-pulse {
            0% { opacity: 0.8; transform: scale(0.7); }
            80%, 100% { opacity: 0; transform: scale(1.6); }
        }

        @keyframes button-shine {
            0%, 60% { left: -100%; }
            85%, 100% { left: 150%; }
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @keyframes error-shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            50% { transform: translateX(5px); }
            75% { transform: translateX(-3px); }
        }

        @media (max-width: 1023px) {
            .floating-item {
                opacity: 0.45;
                scale: 0.8;
            }

            .floating-cap { left: 3%; top: 8%; }
            .floating-book { right: 3%; top: 10%; }
            .floating-card { left: -30px; bottom: 8%; }
            .floating-chart { right: -15px; bottom: 7%; }
        }

        @media (max-width: 639px) {
            .floating-card,
            .floating-chart {
                display: none;
            }

            .floating-cap,
            .floating-book {
                scale: 0.62;
                opacity: 0.35;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                scroll-behavior: auto !important;
            }
        }
    </style>
</head>
<body class="flex min-h-screen flex-col items-center justify-center overflow-x-hidden p-4 antialiased sm:p-6">

    <div class="background-scene" aria-hidden="true">
        <div class="orb orb-one"></div>
        <div class="orb orb-two"></div>

        <div class="floating-item floating-cap">
            <svg class="h-11 w-11" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
            </svg>
        </div>

        <div class="floating-item floating-book">
            <svg class="h-10 w-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
            </svg>
        </div>

        <div class="floating-item floating-card">
            <div class="student-card-lines">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-full bg-blue-500/15"></div>
                    <div class="flex-1">
                        <div class="h-1.5 w-4/5 rounded-full bg-blue-500/20"></div>
                        <div class="mt-2 h-1.5 w-3/5 rounded-full bg-blue-500/10"></div>
                    </div>
                </div>
                <div class="student-card-line"></div>
                <div class="student-card-line"></div>
            </div>
        </div>

        <div class="floating-item floating-chart">
            <svg class="h-12 w-12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
            </svg>
        </div>
    </div>

    <main class="login-wrapper w-full max-w-[440px]">
        <section class="glass-card rounded-[32px] p-7 text-center sm:p-10" aria-labelledby="login-title">
            <div class="portal-badge mb-5 inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50/70 px-3 py-1.5 text-[11px] font-semibold tracking-wide text-blue-700">
                <span class="status-dot"></span>
                PORTAL AKADEMIK
            </div>

            <div class="header-icon mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl text-white">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                </svg>
            </div>

            <div class="form-animate delay-1">
                <h1 id="login-title" class="mb-1.5 text-2xl font-bold tracking-tight text-slate-900">Sistem Informasi Mahasiswa</h1>
                <p class="mx-auto mb-7 max-w-[340px] text-xs leading-relaxed text-slate-500 sm:text-sm">
                    Selamat datang! Masuk untuk mengelola data mahasiswa dan informasi akademik.
                </p>
            </div>

            <?php if (isset($error)) : ?>
                <div class="error-message mb-6 rounded-xl border border-rose-200 bg-rose-500/10 p-3 text-xs font-medium text-rose-700" role="alert">
                    Username atau password salah!
                </div>
            <?php endif; ?>

            <form action="" method="post" id="login-form" class="space-y-4 text-left">
                <input type="hidden" name="login" value="1">

                <div class="input-wrap form-animate delay-2 relative">
                    <div class="input-icon pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <label for="username" class="sr-only">Username</label>
                    <input type="text" name="username" id="username" placeholder="Username" autocomplete="username" required
                           class="glass-input w-full rounded-xl py-3 pl-10 pr-4 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none">
                </div>

                <div class="input-wrap form-animate delay-3 relative">
                    <div class="input-icon pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <label for="password" class="sr-only">Password</label>
                    <input type="password" name="password" id="password" placeholder="Password" autocomplete="current-password" required
                           class="glass-input w-full rounded-xl py-3 pl-10 pr-4 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none">
                </div>

                <div class="form-animate delay-4 flex items-center justify-between px-0.5 pt-1 text-xs">
                    <label class="flex cursor-pointer select-none items-center gap-2 text-slate-600">
                        <input type="checkbox" name="remember" id="remember" class="h-4 w-4 cursor-pointer rounded border-slate-300 text-blue-600 focus:ring-blue-500/20">
                        <span>Remember me</span>
                    </label>
                </div>

                <button type="submit" id="login-button"
                        class="login-button form-animate delay-5 mt-2 w-full rounded-xl px-4 py-3 text-sm font-medium text-white">
                    <span class="button-content inline-flex items-center justify-center gap-2">
                        Login ke Portal
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </span>
                    <span class="loading-content items-center justify-center gap-2">
                        <span class="spinner" aria-hidden="true"></span>
                        Memverifikasi...
                    </span>
                </button>
            </form>
        </section>
    </main>

    <p class="registration-link relative z-10 mt-6 text-center text-xs font-medium text-slate-600 sm:text-sm">
        Belum punya akun? <a href="registrasi.php" class="font-bold text-blue-700">Registrasi di sini</a>
    </p>

    <script>
        const loginForm = document.getElementById('login-form');
        const loginButton = document.getElementById('login-button');

        loginForm.addEventListener('submit', function () {
            loginButton.classList.add('is-loading');
            loginButton.disabled = true;
            loginButton.setAttribute('aria-label', 'Sedang memverifikasi akun');
        });
    </script>
</body>
</html>