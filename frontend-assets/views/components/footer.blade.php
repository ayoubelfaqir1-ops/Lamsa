<footer class="bg-[#064E3B] text-[#F0FDF4] pt-12 pb-8">
    <div class="max-w-7xl mx-auto px-6">
        <!-- Top Section -->
        <div class="flex flex-col lg:flex-row justify-between items-start gap-12 mb-12">
            <!-- Branding -->
            <div class="space-y-6 max-w-sm">
                <div class="flex items-center gap-3">
                    <img src="{{ \Illuminate\Support\Facades\Storage::url('site/branding/lamsa_logo.png') }}" alt="Lamsa" class="w-10 h-10 object-contain brightness-0 invert">
                </div>
                <p class="text-[10px] leading-relaxed opacity-80 uppercase font-medium tracking-[0.1em] max-w-[280px]">
                    Lamsa embodies precise architecture. Rejecting softness in favor of absolute angles, our geometry frames artisanal mastery in a gallery of high contrast.
                </p>
            </div>

            <!-- Links Grid -->
            <div class="flex flex-wrap gap-10 md:gap-20">
                <!-- Platform -->
                <div class="space-y-4">
                    <h4 class="text-[10px] font-bold text-white uppercase tracking-[0.3em]">Platform</h4>
                    <ul class="space-y-3 text-[10px] font-medium uppercase tracking-[0.15em]">
                        <li><a href="{{ route('mission') }}" class="hover:text-white transition-colors">The Mission</a></li>
                        <li><a href="{{ route('artisan-register') }}" class="hover:text-white transition-colors">Artisan Registry</a></li>
                        <li><a href="{{ route('auctions.index') }}" class="hover:text-white transition-colors">Auctions</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div class="space-y-4">
                    <h4 class="text-[10px] font-bold text-white uppercase tracking-[0.3em]">Support</h4>
                    <ul class="space-y-3 text-[10px] font-medium uppercase tracking-[0.15em]">
                        <li><button type="button" class="hover:text-white transition-colors">Contact</button></li>
                        <li><button type="button" class="hover:text-white transition-colors">Shipping</button></li>
                        <li><button type="button" class="hover:text-white transition-colors">FAQs</button></li>
                    </ul>
                </div>

                <!-- Social -->
                <div class="space-y-4">
                    <h4 class="text-[10px] font-bold text-white uppercase tracking-[0.3em]">Social</h4>
                    <div class="flex gap-3">
                        <button type="button" class="w-8 h-8 border border-[#F0FDF4]/20 flex items-center justify-center hover:bg-white hover:text-[#064E3B] transition-all" aria-label="Facebook">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg>
                        </button>
                        <button type="button" class="w-8 h-8 border border-[#F0FDF4]/20 flex items-center justify-center hover:bg-white hover:text-[#064E3B] transition-all" aria-label="Instagram">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"></line></svg>
                        </button>
                        <button type="button" class="w-8 h-8 border border-[#F0FDF4]/20 flex items-center justify-center hover:bg-white hover:text-[#064E3B] transition-all" aria-label="LinkedIn">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Newsletter -->
            <div class="w-full lg:w-72 space-y-4">
                <h4 class="text-[10px] font-bold text-white uppercase tracking-[0.3em]">Join The Architecture</h4>
                <form class="relative group">
                    <input type="email" placeholder="Email address" 
                        class="w-full bg-transparent border border-[#F0FDF4]/30 px-5 py-4 text-[9px] font-semibold uppercase tracking-[0.2em] focus:border-white focus:ring-0 outline-none transition-colors placeholder:text-[#F0FDF4]/30">
                    <button type="submit" class="absolute right-4 top-1/2 -translate-y-1/2 hover:translate-x-1 transition-transform text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"></path></svg>
                    </button>
                </form>
            </div>
        </div>

        <!-- Divider -->
        <div class="w-full h-px bg-white/10 mb-8"></div>

        <!-- Bottom Section -->
        <div class="flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex flex-col md:flex-row items-center gap-6 md:gap-10">
                <span class="text-[9px] font-bold uppercase tracking-[0.2em] opacity-50">© 2024 LAMSA.</span>
                <div class="flex gap-6 text-[9px] font-bold uppercase tracking-[0.2em] opacity-50">
                    <a href="#" class="hover:text-white transition-colors">Terms</a>
                    <a href="#" class="hover:text-white transition-colors">Privacy</a>
                </div>
            </div>

            <!-- Back to Top -->
            <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="flex items-center gap-3 group">
                <span class="text-[9px] font-bold uppercase tracking-[0.3em] group-hover:translate-y-[-1px] transition-transform opacity-70 group-hover:opacity-100">Back to Top</span>
                <div class="w-8 h-8 border border-white/50 flex items-center justify-center group-hover:bg-white group-hover:text-[#064E3B] group-hover:border-white transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 15l-6-6-6 6"></path></svg>
                </div>
            </button>
        </div>
    </div>
</footer>
