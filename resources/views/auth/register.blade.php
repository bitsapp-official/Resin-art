<x-app-layout title="Create Account — Maison Résine">
    <div class="min-h-[75vh] flex items-center justify-center py-16 px-6 lg:px-12 xl:px-20">
        <div class="max-w-[1060px] w-full grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
            
            {{-- Left column welcome text --}}
            <div class="lg:col-span-6 space-y-4 text-left">
                <div class="flex items-center space-x-2 text-[10px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">
                    <span class="w-6 h-[1px] bg-[#8E877D] inline-block"></span>
                    <span>NEW HERE</span>
                </div>
                <h1 class="font-editorial text-5xl sm:text-6xl lg:text-[72px] text-[#1C1917] font-light leading-[1.05] tracking-tight">
                    Create an <em class="italic font-normal">account.</em>
                </h1>
                <p class="text-[14px] text-[#78716C] font-light leading-relaxed max-w-[380px] pt-1">
                    A membership to the atelier — order history, saved addresses, private previews of new pours.
                </p>
            </div>

            {{-- Right column registration form card --}}
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

                    @if(session('status'))
                        <div class="p-4 bg-[#F2F7F4] border border-[#D5E3DC] text-[#3D6A54] rounded-[1.125rem] text-[11px] font-normal leading-relaxed tracking-normal">
                            <div class="font-semibold uppercase tracking-wider text-[10px] text-[#29503C] mb-1">Atelier System</div>
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}" class="space-y-5 text-[11px] uppercase tracking-wider font-semibold text-[#1C1917]">
                        @csrf

                        <div class="space-y-1.5">
                            <label class="block text-[9px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">FULL NAME</label>
                            <input type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Your full name" 
                                    class="w-full px-5 py-3.5 bg-[#FAF8F5] border border-[#DFD9CE] rounded-[1.125rem] text-xs text-[#1C1917] placeholder-[#A89F90] focus:outline-none hover:border-[#BCB5A8] focus:border-[#1C1917] focus:ring-0 transition-all duration-300">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-[9px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">EMAIL</label>
                            <input type="email" name="email" value="{{ old('email') }}" required placeholder="you@email.com" 
                                    class="w-full px-5 py-3.5 bg-[#FAF8F5] border border-[#DFD9CE] rounded-[1.125rem] text-xs text-[#1C1917] placeholder-[#A89F90] focus:outline-none hover:border-[#BCB5A8] focus:border-[#1C1917] focus:ring-0 transition-all duration-300">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-[9px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">PASSWORD</label>
                            <input type="password" name="password" required 
                                   class="w-full px-5 py-3.5 bg-[#FAF8F5] border border-[#DFD9CE] rounded-[1.125rem] text-xs text-[#1C1917] focus:outline-none hover:border-[#BCB5A8] focus:border-[#1C1917] focus:ring-0 transition-all duration-300">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-[9px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">CONFIRM PASSWORD</label>
                            <input type="password" name="password_confirmation" required 
                                   class="w-full px-5 py-3.5 bg-[#FAF8F5] border border-[#DFD9CE] rounded-[1.125rem] text-xs text-[#1C1917] focus:outline-none hover:border-[#BCB5A8] focus:border-[#1C1917] focus:ring-0 transition-all duration-300">
                        </div>

                        <div class="flex items-center space-x-2.5 pt-1">
                            <input type="checkbox" name="terms" id="terms" required class="accent-[#1C1917] rounded border-[#DFD9CE] w-4 h-4 cursor-pointer">
                            <label for="terms" class="cursor-pointer leading-none text-[9px] uppercase tracking-[0.22em] font-medium text-[#8E877D]">
                                I agree to the <a href="{{ route('legal.terms') }}" target="_blank" class="font-bold text-[#1C1917] hover:underline">terms</a> and <a href="{{ route('legal.privacy') }}" target="_blank" class="font-bold text-[#1C1917] hover:underline">privacy policy.</a>
                            </label>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full bg-[#1A1615] hover:bg-[#2C2724] text-white text-[10.5px] uppercase tracking-[0.25em] font-semibold py-4 rounded-full transition-all shadow-xs cursor-pointer">
                                CREATE ACCOUNT
                            </button>
                        </div>
                    </form>

                    <div class="pt-2 flex items-center justify-start text-[12.5px] text-[#524C46] font-normal">
                        <span>Already with us?</span>
                        <a href="{{ route('login') }}" class="text-[#1C1917] hover:underline font-semibold ml-1.5">Sign in</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
