<x-app-layout title="Sign In — Maison Résine">
    <div class="min-h-[75vh] flex items-center justify-center py-10 sm:py-16 px-4 sm:px-6 lg:px-12 xl:px-20 w-full min-w-0">
        <div class="max-w-[1060px] w-full grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-16 items-center">
            
            {{-- Left column welcome text --}}
            <div class="lg:col-span-6 space-y-3 sm:space-y-4 text-left">
                <div class="flex items-center space-x-2 text-[10px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">
                    <span class="w-6 h-[1px] bg-[#8E877D] inline-block"></span>
                    <span>WELCOME BACK</span>
                </div>
                <h1 class="font-editorial text-3xl sm:text-5xl lg:text-6xl xl:text-[72px] text-[#1C1917] font-light leading-[1.08] sm:leading-[1.05] tracking-tight">
                    Sign <em class="italic font-normal">in.</em>
                </h1>
                <p class="text-[13.5px] sm:text-[14px] text-[#78716C] font-light leading-relaxed max-w-[380px] pt-1">
                    Follow your orders, revisit saved pieces and keep your atelier details in one quiet place.
                </p>
            </div>

            {{-- Right column login form card --}}
            <div class="lg:col-span-6 flex justify-center lg:justify-end w-full min-w-0">
                <div class="w-full max-w-[480px] glass rounded-[1.5rem] sm:rounded-[2.25rem] p-5 sm:p-8 md:p-12 space-y-6">
                    
                    @if($errors->any())
                        <x-alert type="error">
                            @if($errors->count() === 1)
                                {{ $errors->first() }}
                            @else
                                <div class="space-y-1">
                                    @foreach($errors->all() as $error)
                                        <div>• {{ $error }}</div>
                                    @endforeach
                                </div>
                            @endif
                        </x-alert>
                    @endif

                    @if(session('status'))
                        <x-alert type="success" :message="session('status')" />
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-4 sm:space-y-5 text-[11px] uppercase tracking-wider font-semibold text-[#1C1917]">
                        @csrf

                        <div class="space-y-1.5">
                            <label class="block text-[9px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">EMAIL</label>
                            <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="you@email.com" 
                                   class="w-full px-4 sm:px-5 py-3 sm:py-3.5 bg-[#FAF8F5] border border-[#DFD9CE] rounded-xl sm:rounded-[1.125rem] text-xs text-[#1C1917] placeholder-[#A89F90] focus:outline-none hover:border-[#BCB5A8] focus:border-[#1C1917] focus:ring-0 transition-all duration-300">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-[9px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">PASSWORD</label>
                            <input type="password" name="password" required 
                                   class="w-full px-4 sm:px-5 py-3 sm:py-3.5 bg-[#FAF8F5] border border-[#DFD9CE] rounded-xl sm:rounded-[1.125rem] text-xs text-[#1C1917] focus:outline-none hover:border-[#BCB5A8] focus:border-[#1C1917] focus:ring-0 transition-all duration-300">
                        </div>

                        <div class="flex items-center space-x-2.5 pt-1 font-normal text-xs text-[#78716C] tracking-normal">
                            <input type="checkbox" name="remember" id="remember" class="accent-[#1C1917] rounded border-[#DFD9CE] w-4 h-4 cursor-pointer">
                            <label for="remember" class="cursor-pointer">Keep me signed in</label>
                        </div>

                        <div class="space-y-2.5 sm:space-y-3 pt-2">
                            <button type="submit" class="w-full bg-[#1A1615] hover:bg-[#2C2724] text-white text-[10px] sm:text-[10.5px] uppercase tracking-[0.25em] font-semibold py-3.5 sm:py-4 rounded-full transition-all shadow-xs cursor-pointer">
                                ENTER THE ATELIER
                            </button>

                            <a href="{{ request('redirect', route('shop.index')) }}" 
                               class="block w-full text-center border border-[#DFD9CE] hover:border-[#1C1917] bg-transparent hover:bg-[#1C1917]/5 text-[#1C1917] text-[10px] sm:text-[10.5px] uppercase tracking-[0.25em] font-semibold py-3.5 sm:py-4 rounded-full transition-all cursor-pointer">
                                CONTINUE AS GUEST
                            </a>
                        </div>
                    </form>

                    <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-2.5 text-[12px] sm:text-[12.5px] text-[#524C46] font-normal text-center sm:text-left">
                        <a href="{{ route('register') }}" class="text-[#1C1917] hover:underline font-medium">Create an account</a>
                        <a href="{{ route('password.request') }}" class="text-[#524C46] hover:text-[#1C1917] hover:underline">Forgotten password?</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
