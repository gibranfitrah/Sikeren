<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sikeren</title>
    
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

            @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r" role="alert">
                <p class="font-bold">Oops!</p>
                <p>{{ session('error') }}</p>
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
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="password" name="password" id="password" required class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 py-3 sm:text-sm border-gray-300 rounded-lg bg-gray-50 border text-gray-900" placeholder="password">
                    </div>
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
