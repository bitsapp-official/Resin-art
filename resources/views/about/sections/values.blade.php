<!-- Editorial Values Grid Section -->
@if(isset($values) && $values->count() > 0)
    <div class="py-16 lg:py-24 border-t border-[#E5DFD3] mt-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
            @foreach($values as $value)
                <div class="value-card bg-[oklch(98.5%_0.008_85)] rounded-[1.75rem] p-8 lg:p-10 border border-[#E5DFD3] flex flex-col justify-between space-y-6 transition-all duration-300 hover:shadow-[0_10px_30px_rgba(0,0,0,0.03)] hover:-translate-y-1 group" style="opacity: 0;">
                    <div class="space-y-4">
                        <span class="text-[11px] font-mono tracking-[0.25em] font-semibold text-[#8E877D] block">
                            {{ $value->number }}
                        </span>

                        <h3 class="font-editorial text-3xl lg:text-4xl text-[#1C1917] font-light leading-tight">
                            {{ $value->title }}
                        </h3>

                        <p class="text-sm text-[#78716C] font-light leading-relaxed pt-1">
                            {{ $value->description }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
