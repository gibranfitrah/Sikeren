<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sikeren</title>
    
    <!-- Favicon Resmi BPS -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=2">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicon-32x32.png') }}?v=2">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicon-16x16.png') }}?v=2">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon.png') }}?v=2">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/apple-icon.png') }}?v=2">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Vite for Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    
    <div class="relative w-full max-w-4xl mx-auto flex flex-col md:flex-row bg-white rounded-2xl shadow-2xl overflow-hidden min-h-[500px]">
        
        <!-- Left Side: Login Form -->
        <div class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center">
            
            <div class="text-center mb-8">
                <img src="{{ asset('assets/img/logo-sikeren-square.png') }}" alt="Sikeren Logo" class="w-20 h-20 mx-auto mb-4 drop-shadow-md">
                <h2 class="text-3xl font-bold text-gray-800">Sikeren</h2>
                <p class="text-gray-500 mt-2 font-medium">Sistem Kegiatan Terencana</p>
            </div>

            @if(session('info'))
            <div class="bg-blue-50 border-l-4 border-blue-600 text-blue-800 p-4 mb-6 rounded-r shadow-xs" role="alert">
                <div class="flex items-center gap-2 mb-1">
                    <svg class="w-4 h-4 text-blue-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                    <span class="font-bold text-xs uppercase tracking-wider">Presensi Kehadiran Rapat</span>
                </div>
                <p class="text-xs leading-relaxed">{{ session('info') }}</p>
            </div>
            @endif

            @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r" role="alert">
                <p class="font-bold">Oops!</p>
                <p>{{ session('error') }}</p>
            </div>
            @endif

            @if(isset($db_error))
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r" role="alert">
                <p class="font-bold">Database tidak terhubung!</p>
                <p>{{ $db_error }}</p>
                <p class="mt-2 text-sm">Pastikan MariaDB di PhpWebStudy berjalan (port 3306) dan .env: DB_DATABASE=bps7400_sikeren, DB_USERNAME=root, DB_PASSWORD=root.</p>
            </div>
            @endif

            <form id="loginForm" action="{{ route('/actionlogin') }}" method="post" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email / NIP / Nama Pemimpin</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                            </svg>
                        </div>
                        <input type="text" name="email" id="email" required class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 py-3 sm:text-sm border-gray-300 rounded-lg bg-gray-50 border text-gray-900" placeholder="email">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <span class="text-[11px] text-gray-400 font-mono">huruf kecil + 123</span>
                    </div>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="password" name="password" id="password" required class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 py-3 sm:text-sm border-gray-300 rounded-lg bg-gray-50 border text-gray-900" placeholder="contoh: hadisusanto123">
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">Format password: nama tanpa gelar huruf kecil + 123 (contoh: <strong>hadisusanto123</strong>)</p>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        Masuk ke Sistem
                    </button>
                </div>
            </form>

            <!-- Quick Testing Account Shortcuts -->
        </div>

        <!-- Right Side: Graphic/Image -->
        <div class="hidden md:block md:w-1/2 relative bg-blue-600 bg-gradient-to-br from-blue-700 to-blue-500">
            <!-- Decorative Elements -->
            <div class="absolute inset-0 bg-opacity-20 bg-pattern" style="background-image: url('data:image/svg+xml,%3Csvg width=\'20\' height=\'20\' viewBox=\'0 0 20 20\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.1\' fill-rule=\'evenodd\'%3E%3Ccircle cx=\'3\' cy=\'3\' r=\'3\'/%3E%3Ccircle cx=\'13\' cy=\'13\' r=\'3\'/%3E%3C/g%3E%3C/svg%3E');"></div>
            
            <div class="absolute inset-0 flex items-center justify-center p-12">
                <img src="{{ asset('assets/img/logo-sikeren.png') }}" alt="Logo Panjang" class="w-full max-w-sm drop-shadow-xl opacity-90 transition-transform duration-500 hover:scale-105">
            </div>
            
            <div class="absolute bottom-0 left-0 right-0 p-8 text-center">
                <p class="text-white text-sm font-medium opacity-80">&copy; {{ date('Y') }} BPS Provinsi Sulawesi Tenggara</p>
            </div>
        </div>
        
    </div>

    <script>
        function fillAndSubmit(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
            document.getElementById('loginForm').submit();
        }
    </script>
</body>
</html>
