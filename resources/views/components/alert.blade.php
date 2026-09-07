@props([
    'type' => 'success', // 'success', 'info', 'error'
    'title' => null,
    'message' => null,
    'dismissible' => true,
    'autoClose' => 3500, // Disappears automatically after 3.5 seconds
])

<div x-data="{ show: true }" 
     x-init="@if($autoClose) setTimeout(() => { show = false; }, {{ $autoClose }}) @endif"
     x-show="show" 
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 -translate-y-2 scale-[0.98]"
     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 translate-y-0 scale-100 max-h-40 mb-6"
     x-transition:leave-end="opacity-0 -translate-y-2 scale-[0.98] max-h-0 mb-0 py-0 overflow-hidden"
     class="atelier-alert-toast atelier-alert-{{ $type }}"
     role="alert">

    {{-- Left Side: Icon + Text (Always Horizontal Row) --}}
    <div style="display: flex !important; flex-direction: row !important; align-items: {{ $title ? 'flex-start' : 'center' }} !important; gap: 14px !important; min-width: 0 !important; flex: 1 1 auto !important;">
        
        {{-- Icon --}}
        @if($type === 'success')
            <div style="
                width: 26px !important; 
                height: 26px !important; 
                min-width: 26px !important; 
                border-radius: 50% !important; 
                background-color: #10b981 !important; 
                display: flex !important; 
                align-items: center !important; 
                justify-content: center !important; 
                color: #ffffff !important;
                flex-shrink: 0 !important;
                box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3) !important;
            ">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
        @elseif($type === 'error')
            <div style="
                width: 26px !important; 
                height: 26px !important; 
                min-width: 26px !important; 
                border-radius: 50% !important; 
                background-color: #ef4444 !important; 
                display: flex !important; 
                align-items: center !important; 
                justify-content: center !important; 
                color: #ffffff !important;
                flex-shrink: 0 !important;
                box-shadow: 0 2px 6px rgba(239, 68, 68, 0.3) !important;
            ">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </div>
        @elseif($type === 'warning')
            <div style="
                width: 26px !important; 
                height: 26px !important; 
                min-width: 26px !important; 
                border-radius: 50% !important; 
                background-color: #f59e0b !important; 
                display: flex !important; 
                align-items: center !important; 
                justify-content: center !important; 
                color: #ffffff !important;
                flex-shrink: 0 !important;
                box-shadow: 0 2px 6px rgba(245, 158, 11, 0.3) !important;
            ">
                <svg width="13" height="13" viewBox="0 0 20 20" fill="#ffffff">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
        @else
            <div style="
                width: 26px !important; 
                height: 26px !important; 
                min-width: 26px !important; 
                border-radius: 50% !important; 
                background-color: #3b82f6 !important; 
                display: flex !important; 
                align-items: center !important; 
                justify-content: center !important; 
                color: #ffffff !important;
                flex-shrink: 0 !important;
                box-shadow: 0 2px 6px rgba(59, 130, 246, 0.3) !important;
            ">
                <svg width="13" height="13" viewBox="0 0 20 20" fill="#ffffff">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
        @endif

        {{-- Text Content --}}
        <div style="min-width: 0 !important; flex: 1 1 auto !important;">
            @if($title)
                <div style="font-size: 14.5px !important; font-weight: 600 !important; color: #111827 !important; line-height: 1.35 !important;">
                    {{ $title }}
                </div>
            @endif
            @if($message || $slot->isNotEmpty())
                <div style="
                    font-size: {{ $title ? '12.5px' : '14.5px' }} !important; 
                    font-weight: {{ $title ? '400' : '600' }} !important; 
                    color: {{ $title ? '#6b7280' : '#111827' }} !important; 
                    line-height: 1.4 !important;
                    {{ $title ? 'margin-top: 3px !important;' : '' }}
                ">
                    {{ $message ?? $slot }}
                </div>
            @endif
        </div>
    </div>

    {{-- Right Side: Close Button (Always on far right) --}}
    @if($dismissible)
        <button type="button" 
                @click="show = false" 
                style="
                    background: transparent !important;
                    border: none !important;
                    color: #9ca3af !important;
                    cursor: pointer !important;
                    padding: 6px !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    border-radius: 8px !important;
                    transition: color 0.2s, background-color 0.2s !important;
                    flex-shrink: 0 !important;
                    margin-left: auto !important;
                "
                onmouseover="this.style.color='#4b5563'; this.style.backgroundColor='rgba(0,0,0,0.04)';"
                onmouseout="this.style.color='#9ca3af'; this.style.backgroundColor='transparent';"
                aria-label="Close message">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    @endif

</div>

<style>
    .atelier-alert-toast:not([style*="display: none"]):not([style*="display:none"]) {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 16px !important;
        width: 100% !important;
        max-width: 520px !important;
        margin-bottom: 24px !important;
        padding: 14px 20px !important;
        border-radius: 18px !important;
        box-sizing: border-box !important;
        box-shadow: 0 10px 30px -4px rgba(0, 0, 0, 0.05), 0 2px 6px -1px rgba(0, 0, 0, 0.02) !important;
        font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif !important;
    }
    .atelier-alert-toast[style*="display: none"],
    .atelier-alert-toast[style*="display:none"],
    [x-cloak] {
        display: none !important;
    }
    .atelier-alert-success {
        background: linear-gradient(90deg, #ecfdf5 0%, #ffffff 50%) !important;
        border: 1px solid #a7f3d0 !important;
    }
    .atelier-alert-error {
        background: linear-gradient(90deg, #fef2f2 0%, #ffffff 50%) !important;
        border: 1px solid #fecaca !important;
    }
    .atelier-alert-info {
        background: linear-gradient(90deg, #eff6ff 0%, #ffffff 50%) !important;
        border: 1px solid #bfdbfe !important;
    }
    .atelier-alert-warning {
        background: linear-gradient(90deg, #fffbeb 0%, #ffffff 50%) !important;
        border: 1px solid #fde68a !important;
    }
</style>
