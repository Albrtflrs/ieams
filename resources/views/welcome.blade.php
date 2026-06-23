<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IEAMS – IT Expert Accounting Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wdth,wght@0,75..100,400..700;1,75..100,400..700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased">

    <!-- Full screen split layout -->
    <div class="min-h-screen flex flex-col lg:flex-row">

        <!-- Left Panel - Branding -->
        <div class="lg:w-1/2 bg-slate-900 flex flex-col justify-between p-10 lg:p-16 relative overflow-hidden">

            <!-- Background decoration -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-32 -left-32 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-teal-500/10 rounded-full blur-3xl"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-emerald-400/5 rounded-full blur-2xl"></div>
            </div>

            <!-- Logo -->
            <div class="relative flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-white font-bold text-xl tracking-tight">IEAMS</p>
                    <p class="text-slate-400 text-xs">IT Expert Accounting</p>
                </div>
            </div>

            <!-- Main content -->
            <div class="relative my-12 lg:my-0">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-400 text-xs font-semibold uppercase tracking-wider mb-6">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    Authorized Access Only
                </div>
                <h1 class="text-3xl lg:text-4xl font-extrabold text-white leading-tight mb-4">
                    IT Expert Accounting<br/>
                    <span class="text-emerald-400">Management System</span>
                </h1>
                <p class="text-slate-400 text-base leading-relaxed max-w-md">
                    A centralized financial management platform for authorized personnel. Track income, manage expenses, and maintain a complete audit trail.
                </p>

                <!-- Feature list -->
                <ul class="mt-8 space-y-3">
                    <li class="flex items-center gap-3 text-slate-300 text-sm">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        Income & Expense Tracking
                    </li>
                    <li class="flex items-center gap-3 text-slate-300 text-sm">
                        <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        Smart Dashboards & Reports
                    </li>
                    <li class="flex items-center gap-3 text-slate-300 text-sm">
                        <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        Full Audit Trail & Security
                    </li>
                    <li class="flex items-center gap-3 text-slate-300 text-sm">
                        <div class="w-8 h-8 rounded-lg bg-orange-500/20 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        Role-Based Access Control
                    </li>
                </ul>
            </div>

            <!-- Footer note -->
            <div class="relative">
                <p class="text-slate-600 text-xs">
                    © {{ date('Y') }} IEAMS. All rights reserved. Unauthorized access is prohibited.
                </p>
            </div>
        </div>

        <!-- Right Panel - Access -->
        <div class="lg:w-1/2 bg-slate-50 flex flex-col items-center justify-center p-10 lg:p-16">
            <div class="w-full max-w-sm">

                <!-- Lock icon -->
                <div class="w-16 h-16 rounded-2xl bg-slate-900 flex items-center justify-center mx-auto mb-8 shadow-lg">
                    <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>

                <h2 class="text-2xl font-bold text-slate-900 text-center">Restricted Access</h2>
                <p class="text-slate-500 text-sm text-center mt-2 mb-8">
                    This system is intended for authorized personnel only. Please sign in with your credentials to continue.
                </p>

                <!-- Notice box -->
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <p class="text-amber-800 text-xs font-semibold">Notice</p>
                            <p class="text-amber-700 text-xs mt-0.5 leading-relaxed">
                                If you do not have an account, please contact your system administrator to request access.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Login button -->
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}"
                            class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-semibold rounded-xl shadow transition text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-xl shadow transition text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            Sign In to Your Account
                        </a>
                    @endauth
                @endif

                <!-- Divider -->
                <div class="flex items-center gap-3 my-6">
                    <div class="flex-1 h-px bg-slate-200"></div>
                    <span class="text-slate-400 text-xs">system info</span>
                    <div class="flex-1 h-px bg-slate-200"></div>
                </div>

                <!-- System info -->
                <div class="space-y-2">
                    <div class="flex justify-between items-center py-2 border-b border-slate-100">
                        <span class="text-xs text-slate-500">System</span>
                        <span class="text-xs font-medium text-slate-700">IEAMS v1.0.0</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-slate-100">
                        <span class="text-xs text-slate-500">Organization</span>
                        <span class="text-xs font-medium text-slate-700">IT Expert</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-slate-100">
                        <span class="text-xs text-slate-500">Access</span>
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Authorized Personnel
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-xs text-slate-500">Support</span>
                        <span class="text-xs font-medium text-slate-700">admin@itexpert.com</span>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- Automatic redirect to login page -->
    <script>
        window.location.href = "{{ route('login') }}";
    </script>

</body>
</html>