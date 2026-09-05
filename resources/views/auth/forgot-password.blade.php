<x-app-layout title="Recover Password — Maison Résine">
    <div class="min-h-[75vh] flex items-center justify-center py-16 px-6 lg:px-12 xl:px-20">
        <div class="max-w-[1060px] w-full grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
            
            {{-- Left column welcome text --}}
            <div class="lg:col-span-6 space-y-4 text-left">
                <div class="flex items-center space-x-2 text-[10px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">
                    <span class="w-6 h-[1px] bg-[#8E877D] inline-block"></span>
                    <span>RECOVERY</span>
                </div>
                <h1 class="font-editorial text-5xl sm:text-6xl lg:text-[64px] text-[#1C1917] font-light leading-[1.05] tracking-tight">
                    Forgotten <em class="italic font-normal">password.</em>
                </h1>
                <p class="text-[13.5px] text-[#78716C] font-light leading-relaxed max-w-sm pt-1">
                    Tell us the email on your account and we will send a quiet link to set a new password.
                </p>
            </div>

            {{-- Right column forgot password form card --}}
            <div class="lg:col-span-6 flex justify-center lg:justify-end">
                <div class="w-full max-w-[480px] glass rounded-[2.25rem] p-8 sm:p-12 space-y-6">
                    @if(session('status'))
                        {{-- ✅ Success State: Email sent --}}
                        <div class="py-4 flex flex-col items-center justify-center text-center space-y-2">
                            {{-- Envelope icon --}}
                            <div class="w-14 h-14 rounded-full bg-[#F2F7F4] flex items-center justify-center mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-[#3D6A54]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                </svg>
                            </div>
                            <h2 class="font-editorial text-3xl lg:text-[32px] text-[#1C1917] font-light">Check your inbox.</h2>
                            <p class="text-[13px] text-[#78716C] font-normal leading-relaxed pb-6">
                                A password reset link has been sent to your email.<br>
                                <span class="text-[11px] text-[#A89F91]">The link expires in 30 minutes. Check your spam folder if you don't see it.</span>
                            </p>
                            {{-- This opens the user's default email app --}}
                            <a href="mailto:" class="w-full block bg-[#1A1615] hover:bg-[#2C2724] text-white text-center text-[10px] uppercase tracking-[0.25em] font-semibold py-4 rounded-full shadow-md transition-all">
                                OPEN EMAIL APP
                            </a>
                        </div>
                        <div class="border-t border-[#E6E1D7]/60 pt-4 flex items-center justify-start text-xs text-[#78716C] font-normal">
                            <a href="{{ route('login') }}" class="text-[#1C1917] hover:text-[#A89F91] transition-colors font-medium">Back to sign in</a>
                        </div>
                    @else
                        {{-- 📧 Form State: Enter email --}}
                        <form method="POST" action="{{ route('password.email') }}" class="space-y-5 text-[11px] uppercase tracking-wider font-semibold text-[#1C1917]">
                            @csrf

                            @error('email')
                                <div class="text-[11px] text-red-600 font-normal normal-case tracking-normal -mt-1">{{ $message }}</div>
                            @enderror

                            <div class="space-y-2">
                                <label class="block text-[9px] uppercase tracking-[0.2em] font-bold text-[#8E877D]">EMAIL</label>
                                <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder=""
                                       class="w-full px-5 py-3.5 bg-transparent border border-[#DFD9CE] rounded-full text-xs text-[#1C1917] focus:outline-none hover:border-[#BCB5A8] focus:border-[#1C1917] focus:ring-0 transition-all duration-300">
                            </div>

                            <div class="pt-2">
                                <button type="submit" class="w-full bg-[#1A1615] hover:bg-[#2C2724] text-white text-[10px] uppercase tracking-[0.25em] font-semibold py-4 rounded-full transition-all shadow-md cursor-pointer">
                                    SEND RESET LINK
                                </button>
                            </div>
                        </form>
                        <div class="border-t border-[#E6E1D7]/60 pt-4 flex items-center justify-start text-xs text-[#78716C] font-normal">
                            <a href="{{ route('login') }}" class="text-[#1C1917] hover:text-[#A89F91] transition-colors font-medium">Back to sign in</a>
                        </div>
                    @endif


                </div>
            </div>

        </div>
    </div>
</x-app-layout>
