<x-account-layout 
    title="Change Password" 
    header-title="Security" 
    header-italic=" & password." 
    header-subtitle="Update your security credentials to keep your account safe.">
    
    <div class="glass rounded-[1.75rem] p-7 sm:p-9 space-y-6">
        <form method="POST" action="{{ route('account.password.update') }}" class="space-y-4 text-xs max-w-lg">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D] mb-1.5">CURRENT PASSWORD *</label>
                <input type="password" name="current_password" required class="w-full px-5 py-3 bg-transparent border border-[#DFD9CE] rounded-full text-xs font-medium focus:outline-none focus:border-[#1C1917]">
            </div>

            <div>
                <label class="block text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D] mb-1.5">NEW PASSWORD *</label>
                <input type="password" name="password" required class="w-full px-5 py-3 bg-transparent border border-[#DFD9CE] rounded-full text-xs font-medium focus:outline-none focus:border-[#1C1917]">
            </div>

            <div>
                <label class="block text-[10px] uppercase tracking-[0.2em] font-bold text-[#8E877D] mb-1.5">CONFIRM NEW PASSWORD *</label>
                <input type="password" name="password_confirmation" required class="w-full px-5 py-3 bg-transparent border border-[#DFD9CE] rounded-full text-xs font-medium focus:outline-none focus:border-[#1C1917]">
            </div>

            <div class="pt-2">
                <button type="submit" class="border border-[#DFD9CE] hover:border-[#1C1917] hover:bg-[#1C1917] hover:text-white text-[#1C1917] text-[9.5px] uppercase tracking-[0.25em] font-semibold py-3.5 px-7 rounded-full transition-all duration-300 cursor-pointer">
                    UPDATE PASSWORD
                </button>
            </div>
        </form>
    </div>
</x-account-layout>
