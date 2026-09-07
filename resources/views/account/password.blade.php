<x-account-layout 
    title="Change Password" 
    header-title="Security" 
    header-italic=" & password." 
    header-subtitle="Update your security credentials to keep your account safe.">
    
    <div class="glass rounded-[1.75rem] p-7 sm:p-9 space-y-6">
        
        <form method="POST" action="{{ route('account.password.update') }}" class="space-y-5 text-xs max-w-lg">
            @csrf
            @method('PUT')

            <div class="space-y-1.5">
                <label class="block text-[9.5px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">CURRENT PASSWORD *</label>
                <input type="password" name="current_password" required autofocus placeholder="••••••••" 
                       class="w-full px-5 py-3.5 bg-[#FAF8F5] border @error('current_password') border-red-400 @else border-[#DFD9CE] @enderror rounded-[1.125rem] text-xs text-[#1C1917] placeholder-[#A89F90] focus:outline-none hover:border-[#BCB5A8] focus:border-[#1C1917] focus:ring-0 transition-all duration-300">
                @error('current_password')
                    <p class="text-[10px] text-red-600 pl-2 pt-0.5 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1.5">
                <label class="block text-[9.5px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">NEW PASSWORD *</label>
                <input type="password" name="password" required placeholder="••••••••" 
                       class="w-full px-5 py-3.5 bg-[#FAF8F5] border @error('password') border-red-400 @else border-[#DFD9CE] @enderror rounded-[1.125rem] text-xs text-[#1C1917] placeholder-[#A89F90] focus:outline-none hover:border-[#BCB5A8] focus:border-[#1C1917] focus:ring-0 transition-all duration-300">
                @error('password')
                    <p class="text-[10px] text-red-600 pl-2 pt-0.5 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1.5">
                <label class="block text-[9.5px] uppercase tracking-[0.2em] font-medium text-[#8E877D]">CONFIRM NEW PASSWORD *</label>
                <input type="password" name="password_confirmation" required placeholder="••••••••" 
                       class="w-full px-5 py-3.5 bg-[#FAF8F5] border @error('password_confirmation') border-red-400 @else border-[#DFD9CE] @enderror rounded-[1.125rem] text-xs text-[#1C1917] placeholder-[#A89F90] focus:outline-none hover:border-[#BCB5A8] focus:border-[#1C1917] focus:ring-0 transition-all duration-300">
                @error('password_confirmation')
                    <p class="text-[10px] text-red-600 pl-2 pt-0.5 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-2">
                <button type="submit" class="bg-[#1C1917] hover:bg-[#2C2724] text-white text-[10px] uppercase tracking-[0.25em] font-semibold py-3.5 px-8 rounded-full transition-all duration-300 shadow-xs cursor-pointer">
                    UPDATE PASSWORD
                </button>
            </div>
        </form>
    </div>
</x-account-layout>
