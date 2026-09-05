<x-app-layout title="Verify Email — Maison Résine">
    <div class="min-h-[75vh] flex items-center justify-center py-16 px-6 lg:px-12 xl:px-20">
        <div class="max-w-[1060px] w-full grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-16 items-center">
            
            {{-- Left column welcome text --}}
            <div class="lg:col-span-5 space-y-5 text-left relative z-10">
                <div class="flex items-center space-x-3 text-[10px] uppercase tracking-[0.25em] font-medium text-[#8E877D]">
                    <span class="w-8 h-[1px] bg-[#D9D2C5] inline-block"></span>
                    <span>VERIFICATION</span>
                </div>
                <h1 class="font-editorial text-5xl sm:text-6xl lg:text-[68px] text-[#1C1917] font-light leading-[1.25] tracking-tight">
                    Verify your <span class="block mt-2 sm:mt-3 italic font-normal text-[#1C1917]">email.</span>
                </h1>
                <p class="text-[14px] text-[#78716C] font-light leading-relaxed max-w-sm pt-1">
                    Please check your inbox for a secure activation link to complete your atelier registration.
                </p>
            </div>

            {{-- Right column verification info container --}}
            <div class="lg:col-span-7 flex justify-center lg:justify-end relative z-10">
                <div class="w-full max-w-[540px] glass rounded-[2.25rem] p-8 sm:p-12 space-y-7 relative overflow-hidden shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                    
                    {{-- Subtle decorative gradient blob behind the card content --}}
                    <div class="absolute -top-24 -right-24 w-64 h-64 bg-[#F2EDE4]/60 rounded-full blur-3xl -z-10 pointer-events-none"></div>

                    @if (session('message'))
                        <div class="p-4 bg-[#F2F7F4] border border-[#C8DDD4] text-[#2D5A45] rounded-2xl text-[12px] font-medium flex items-center space-x-3">
                            <svg class="w-5 h-5 text-[#3D7A5E] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>A new secure verification link has been sent to your inbox.</span>
                        </div>
                    @endif

                    @if (session('smtp_error'))
                        <div class="p-4 bg-[#FEF8F0] border border-[#F5D9B0] text-[#8B4E10] rounded-2xl text-[12px] font-medium flex items-center space-x-3">
                            <svg class="w-5 h-5 text-[#C97B35] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                            <span>Mail server is temporarily busy. Please wait a moment and try again.</span>
                        </div>
                    @endif

                    <div class="space-y-5 text-[14px] text-[#524C46] leading-relaxed font-light">
                        <p>
                            Before getting started, please verify your email address by clicking on the secure, cryptographically signed link we just emailed to you.
                        </p>
                        <div class="bg-white/70 border border-[#EBE6DD] rounded-2xl p-4 sm:p-5 flex items-center space-x-3.5 shadow-xs">
                            <div class="w-9 h-9 rounded-full bg-[#FAF7F2] border border-[#E8E2D8] flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-[#A89F91]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] uppercase tracking-[0.18em] text-[#8E877D] font-semibold">Awaiting Verification For</p>
                                <p class="font-medium text-[#1C1917] text-[13.5px] truncate tracking-normal mt-0.5">{{ auth()->user()->email }}</p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('verification.send') }}" class="pt-2">
                        @csrf
                        <button type="submit" class="w-full sm:w-auto bg-[#1A1615] hover:bg-[#2C2724] text-white text-[11px] uppercase tracking-[0.22em] font-semibold px-8 py-4 rounded-full transition-all cursor-pointer shadow-sm hover:shadow-md active:scale-[0.99]">
                            Resend Secure Link
                        </button>
                    </form>

                    <div class="pt-6 border-t border-[#EBE6DD]/80 flex flex-wrap items-center justify-between gap-3 text-xs text-[#78716C] font-normal">
                        <span>Need to switch accounts?</span>
                        <a href="/logout" class="text-[#1C1917] hover:text-[#A89F91] font-medium transition-colors cursor-pointer underline underline-offset-4">
                            Securely Sign Out
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
