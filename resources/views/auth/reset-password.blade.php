<x-app-layout title="Set New Password — Maison Résine">
    <div class="min-h-[75vh] flex items-center justify-center py-16 px-6 lg:px-12 xl:px-20">
        <div class="max-w-[1060px] w-full grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
            
            {{-- Left column welcome text --}}
            <div class="lg:col-span-6 space-y-4 text-left">
                <div class="flex items-center space-x-2 text-[10px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">
                    <span class="w-6 h-[1px] bg-[#8E877D] inline-block"></span>
                    <span>RECOVERY</span>
                </div>
                <h1 class="font-editorial text-5xl sm:text-6xl lg:text-[64px] text-[#1C1917] font-light leading-[1.05] tracking-tight">
                    Set a new <em class="italic font-normal">password.</em>
                </h1>
                <p class="text-[13.5px] text-[#78716C] font-light leading-relaxed max-w-md pt-1">
                    Choose something long and calm. Twelve characters or more.
                </p>
            </div>

            {{-- Right column reset password form card --}}
            <div class="lg:col-span-6 flex justify-center lg:justify-end">
                <div class="w-full max-w-[480px] glass rounded-[2.25rem] p-8 sm:p-12 space-y-6">
                    <!-- Validation Errors -->
                    @if($errors->any())
                        <div class="p-4 bg-[#FAF5F2] border border-[#EADED9] text-[#8C5E50] rounded-[1.125rem] text-[11px] font-normal leading-relaxed space-y-1 tracking-normal">
                            <div class="font-semibold uppercase tracking-wider text-[10px] text-[#703D30] mb-1">Atelier Notice</div>
                            @foreach($errors->all() as $error)
                                <div class="text-[#8C5E50]">• {{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}" class="space-y-5 text-[11px] uppercase tracking-wider font-semibold text-[#1C1917]">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <input type="hidden" name="email" value="{{ $email }}">

                        <div class="space-y-1.5">
                            <label class="block text-[9px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">NEW PASSWORD</label>
                            <input type="password" name="password" required autofocus
                                   class="w-full px-5 py-3.5 bg-[#FAF8F5] border border-[#DFD9CE] rounded-[1.125rem] text-xs text-[#1C1917] focus:outline-none hover:border-[#BCB5A8] focus:border-[#1C1917] focus:ring-0 transition-all duration-300">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-[9px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">CONFIRM NEW PASSWORD</label>
                            <input type="password" name="password_confirmation" required 
                                   class="w-full px-5 py-3.5 bg-[#FAF8F5] border border-[#DFD9CE] rounded-[1.125rem] text-xs text-[#1C1917] focus:outline-none hover:border-[#BCB5A8] focus:border-[#1C1917] focus:ring-0 transition-all duration-300">
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full bg-[#1C1917] hover:bg-[#2D2825] text-white text-[10px] uppercase tracking-[0.25em] font-semibold py-4 rounded-full transition-all shadow-xs cursor-pointer">
                                SAVE PASSWORD
                            </button>
                        </div>
                    </form>

                    <div class="pt-2 flex items-center justify-start text-xs text-[#78716C] font-normal">
                        <a href="{{ route('login') }}" class="text-[#1C1917] hover:underline font-medium">Back to sign in</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
