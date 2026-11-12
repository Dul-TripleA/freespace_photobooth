<script setup>
import { Head, Link } from "@inertiajs/vue3";

const props = defineProps({
    stats: Object,
});
</script>

<template>
    <Head title="Control Center - Admin SelfBooth" />

    <div class="relative min-h-screen overflow-hidden bg-[#050505] px-4 py-6 font-sans text-white sm:px-6 sm:py-8 lg:px-10">
        <!-- Editorial background grid -->
        <div
            class="pointer-events-none fixed inset-0 opacity-[0.035]"
            style="background-image: linear-gradient(to right, #ffffff 1px, transparent 1px), linear-gradient(to bottom, #ffffff 1px, transparent 1px); background-size: 100px 100px;"
        ></div>

        <div class="relative z-10 mx-auto w-full max-w-[1600px] flex flex-col min-h-[calc(100vh-6rem)]">
            <!-- HEADER -->
            <header class="flex flex-col gap-5 border-b border-neutral-800 pb-5 sm:flex-row sm:items-end sm:justify-between sm:pb-8 shrink-0">
                <div class="min-w-0">
                    <div class="mb-3 flex items-center gap-2.5">
                        <span class="h-2 w-2 bg-white animate-pulse"></span>
                        <span class="h-2 w-2 bg-white/60"></span>
                        <span class="h-2 w-2 bg-white/30"></span>
                        <span class="ml-2 font-mono text-[9px] uppercase tracking-[0.24em] text-neutral-500">SelfBooth / Administration</span>
                    </div>
                    <p class="mb-1 font-mono text-[10px] italic tracking-[0.18em] text-neutral-500">00 — System Overview</p>
                    <h1 class="text-4xl font-medium uppercase leading-[0.95] tracking-[-0.055em] sm:text-5xl lg:text-6xl">
                        Control <span class="text-neutral-500">Center</span>
                    </h1>
                </div>

                <div class="flex gap-3 shrink-0">
                    <a href="/" class="group inline-flex items-center gap-4 border border-white bg-white px-5 py-3 font-mono text-[10px] uppercase tracking-[0.16em] text-black hover:bg-neutral-200 transition">
                        <span>Buka Kiosk</span>
                        <span class="text-lg leading-none transition-transform group-hover:translate-x-1 sm:translate-y-[-2px]">↗</span>
                    </a>
                </div>
            </header>

            <!-- MAIN MENU GRID (BRUTALIST BENTO BOX) -->
            <main class="flex-grow flex flex-col justify-center py-10">
                <div class="grid grid-cols-1 md:grid-cols-2 border-t border-l border-neutral-800 bg-[#090909]/40 backdrop-blur-sm shadow-[0_20px_70px_rgba(0,0,0,0.4)]">
                    
                    <!-- MENU 1: FRAME MANAGEMENT -->
                    <Link href="/admin/frames" class="group relative flex flex-col border-r border-b border-neutral-800 p-8 sm:p-12 min-h-[320px] transition-colors hover:bg-neutral-900/80">
                        <div class="flex justify-between items-start mb-12">
                            <span class="font-mono text-[9px] uppercase tracking-[0.24em] text-neutral-500 group-hover:text-white transition-colors">01 / Studio Config</span>
                            <span class="border border-neutral-700 bg-[#050505] px-3 py-1 font-mono text-[10px] text-neutral-400 group-hover:border-white group-hover:text-white transition-colors">
                                {{ stats.frames_count }} ITEMS
                            </span>
                        </div>
                        
                        <div class="mt-auto">
                            <h2 class="text-3xl sm:text-4xl font-normal uppercase tracking-tight mb-4">Frame Assets</h2>
                            <p class="font-mono text-xs text-neutral-500 leading-relaxed max-w-sm mb-8 group-hover:text-neutral-400 transition-colors">
                                Kelola template PNG transparan, atur tata letak koordinat pose kamera, dan unduh layout panduan (guide).
                            </p>
                            
                            <div class="inline-flex items-center justify-between w-full border-t border-neutral-800 pt-6 group-hover:border-neutral-600 transition-colors">
                                <span class="font-mono text-[10px] uppercase tracking-[0.2em] font-bold">MANAGE FRAMES</span>
                                <span class="text-2xl font-light group-hover:translate-x-2 transition-transform">→</span>
                            </div>
                        </div>
                    </Link>

                    <!-- MENU 2: VOUCHER & ACCESS -->
                    <Link href="/admin/vouchers" class="group relative flex flex-col border-r border-b border-neutral-800 p-8 sm:p-12 min-h-[320px] transition-colors hover:bg-neutral-900/80">
                        <div class="flex justify-between items-start mb-12">
                            <span class="font-mono text-[9px] uppercase tracking-[0.24em] text-neutral-500 group-hover:text-white transition-colors">02 / Access Control</span>
                            <span class="border border-neutral-700 bg-[#050505] px-3 py-1 font-mono text-[10px] text-neutral-400 group-hover:border-white group-hover:text-white transition-colors">
                                {{ stats.vouchers_count }} ACTIVE
                            </span>
                        </div>
                        
                        <div class="mt-auto">
                            <h2 class="text-3xl sm:text-4xl font-normal uppercase tracking-tight mb-4">Event Vouchers</h2>
                            <p class="font-mono text-xs text-neutral-500 leading-relaxed max-w-sm mb-8 group-hover:text-neutral-400 transition-colors">
                                Generate kode unik untuk event private, atur kuota pemakaian, dan tautkan ke frame eksklusif.
                            </p>
                            
                            <div class="inline-flex items-center justify-between w-full border-t border-neutral-800 pt-6 group-hover:border-neutral-600 transition-colors">
                                <span class="font-mono text-[10px] uppercase tracking-[0.2em] font-bold">MANAGE VOUCHERS</span>
                                <span class="text-2xl font-light group-hover:translate-x-2 transition-transform">→</span>
                            </div>
                        </div>
                    </Link>

                </div>
            </main>

            <!-- FOOTER -->
            <footer class="flex flex-col gap-2 border-t border-neutral-800 pt-5 sm:flex-row sm:items-center sm:justify-between shrink-0">
                <span class="font-mono text-[9px] uppercase tracking-[0.2em] text-neutral-600">System Status: <span class="text-green-500">ONLINE</span></span>
                <span class="font-mono text-[9px] uppercase tracking-[0.2em] text-neutral-600">SelfBooth Engine v1.0.0</span>
            </footer>
        </div>
    </div>
</template>