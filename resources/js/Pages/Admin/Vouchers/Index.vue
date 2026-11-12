<script setup>
import { Head, useForm, router } from "@inertiajs/vue3";

const props = defineProps({
    vouchers: Array,
    frames: Array,
});

const form = useForm({
    code: "",
    event_name: "",
    quota: null,
    expires_at: "",
    frame_ids: [],
});

const generateRandomCode = () => {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let result = '';
    for (let i = 0; i < 8; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    form.code = result;
};

const toggleFrame = (id) => {
    const index = form.frame_ids.indexOf(id);
    if (index === -1) {
        form.frame_ids.push(id);
    } else {
        form.frame_ids.splice(index, 1);
    }
};

const submitForm = () => {
    form.post(route("admin.vouchers.store"), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const deleteVoucher = (id) => {
    if (confirm("Hapus voucher ini secara permanen?")) {
        router.delete(route("admin.vouchers.destroy", id));
    }
};

const toggleStatus = (id) => {
    router.patch(route("admin.vouchers.toggle", id));
};
</script>

<template>
    <Head title="Kelola Voucher - Admin SelfBooth" />

    <div class="relative min-h-screen overflow-hidden bg-[#050505] px-4 py-6 font-sans text-white sm:px-6 sm:py-8 lg:px-10">
        <!-- Editorial background grid -->
        <div
            class="pointer-events-none fixed inset-0 opacity-[0.035]"
            style="background-image: linear-gradient(to right, #ffffff 1px, transparent 1px), linear-gradient(to bottom, #ffffff 1px, transparent 1px); background-size: 100px 100px;"
        ></div>

        <div class="relative z-10 mx-auto w-full max-w-[1600px] space-y-8 lg:space-y-10">
            <!-- HEADER -->
            <header class="flex flex-col gap-5 border-b border-neutral-800 pb-5 sm:flex-row sm:items-end sm:justify-between sm:pb-6">
                <div class="min-w-0">
                    <div class="mb-3 flex items-center gap-2.5">
                        <span class="h-2 w-2 bg-white"></span>
                        <span class="h-2 w-2 bg-white/60"></span>
                        <span class="h-2 w-2 bg-white/30"></span>
                        <span class="ml-2 font-mono text-[9px] uppercase tracking-[0.24em] text-neutral-500">SelfBooth / Administration</span>
                    </div>
                    <p class="mb-1 font-mono text-[10px] italic tracking-[0.18em] text-neutral-500">02 — Access Control</p>
                    <h1 class="text-3xl font-medium uppercase leading-[0.95] tracking-[-0.055em] sm:text-4xl lg:text-5xl">
                        Manage <span class="text-neutral-500">Vouchers</span>
                    </h1>
                </div>

                <div class="flex gap-3 shrink-0">
                    <a href="/admin/frames" class="border border-neutral-700 px-4 py-3 font-mono text-[10px] uppercase tracking-[0.16em] text-neutral-400 hover:border-white hover:text-white transition">Kelola Frame</a>
                    <a href="/" class="border border-white bg-white px-4 py-3 font-mono text-[10px] uppercase tracking-[0.16em] text-black hover:bg-neutral-200 transition">Buka Kiosk ↗</a>
                </div>
            </header>

            <div class="grid grid-cols-1 items-start gap-5 xl:grid-cols-12 xl:gap-6">
                <!-- FORM GENERATE VOUCHER -->
                <form @submit.prevent="submitForm" class="space-y-6 border border-neutral-800 bg-[#090909] p-4 shadow-[0_20px_70px_rgba(0,0,0,0.3)] sm:p-6 xl:col-span-5">
                    <div class="flex items-end justify-between border-b border-neutral-800 pb-4">
                        <div>
                            <p class="font-mono text-[9px] uppercase tracking-[0.22em] text-neutral-500">NEW ENTRY</p>
                            <h2 class="mt-1 text-xl font-medium uppercase sm:text-2xl">Buat Voucher</h2>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="block font-mono text-[9px] uppercase tracking-[0.18em] text-neutral-400">Kode Unik Voucher</label>
                            <div class="flex gap-2">
                                <input v-model="form.code" type="text" required placeholder="ANDIAYU2026" class="w-full uppercase border border-neutral-800 bg-[#050505] px-4 py-3 text-sm text-white focus:border-white focus:outline-none" />
                                <button type="button" @click="generateRandomCode" class="shrink-0 border border-neutral-700 px-4 py-3 font-mono text-[9px] uppercase tracking-[0.1em] text-neutral-300 hover:bg-white hover:text-black transition">Acak</button>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block font-mono text-[9px] uppercase tracking-[0.18em] text-neutral-400">Nama Event / Penjelasan</label>
                            <input v-model="form.event_name" type="text" placeholder="Wedding Andi & Ayu" class="w-full border border-neutral-800 bg-[#050505] px-4 py-3 text-sm text-white focus:border-white focus:outline-none" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block font-mono text-[9px] uppercase tracking-[0.18em] text-neutral-400">Kuota (Opsional)</label>
                                <input v-model="form.quota" type="number" min="1" placeholder="Kosong = Unlimited" class="w-full border border-neutral-800 bg-[#050505] px-4 py-3 text-sm text-white focus:border-white focus:outline-none" />
                            </div>
                            <div class="space-y-2">
                                <label class="block font-mono text-[9px] uppercase tracking-[0.18em] text-neutral-400">Kedaluwarsa (Opsional)</label>
                                <input v-model="form.expires_at" type="datetime-local" class="w-full border border-neutral-800 bg-[#050505] px-4 py-3 text-sm text-neutral-400 focus:border-white focus:outline-none" />
                            </div>
                        </div>

                        <!-- PILIHAN FRAME EKSKLUSIF -->
                        <div class="space-y-3 pt-2">
                            <label class="block font-mono text-[9px] uppercase tracking-[0.18em] text-neutral-400 border-t border-neutral-800 pt-4">Tautkan Frame Khusus (Eksklusif)</label>
                            <div class="max-h-56 overflow-y-auto pr-1 space-y-2">
                                <div v-for="frame in frames" :key="frame.id" 
                                     @click="toggleFrame(frame.id)"
                                     :class="['flex cursor-pointer items-center justify-between border p-3 transition', form.frame_ids.includes(frame.id) ? 'border-white bg-white/5' : 'border-neutral-800 bg-[#050505] hover:border-neutral-600']">
                                    <div class="flex items-center gap-3">
                                        <div :class="['flex h-4 w-4 items-center justify-center border', form.frame_ids.includes(frame.id) ? 'border-white bg-white' : 'border-neutral-600']">
                                            <span v-if="form.frame_ids.includes(frame.id)" class="text-[10px] font-black text-black">✓</span>
                                        </div>
                                        <span class="text-xs font-medium uppercase tracking-[0.05em]">{{ frame.name }}</span>
                                    </div>
                                    <span class="font-mono text-[9px] text-neutral-500">ID: {{ frame.id }}</span>
                                </div>
                                <div v-if="frames.length === 0" class="p-3 text-center border border-dashed border-neutral-800 text-xs text-neutral-500">
                                    Belum ada frame tersedia.
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" :disabled="form.processing" class="mt-6 flex w-full justify-between items-center border border-white bg-white px-5 py-4 text-black hover:bg-neutral-200 transition disabled:opacity-50">
                        <span class="font-bold uppercase tracking-[0.08em]">{{ form.processing ? "MEMPROSES..." : "GENERATE VOUCHER" }}</span>
                        <span>→</span>
                    </button>
                </form>

                <!-- LIST VOUCHERS -->
                <section class="border border-neutral-800 bg-[#090909] p-4 shadow-[0_20px_70px_rgba(0,0,0,0.3)] sm:p-6 xl:col-span-7">
                    <div class="mb-5 flex justify-between items-end border-b border-neutral-800 pb-4">
                        <div>
                            <p class="font-mono text-[9px] uppercase tracking-[0.22em] text-neutral-500">DATABASE</p>
                            <h2 class="mt-1 text-xl font-medium uppercase sm:text-2xl">Daftar Voucher</h2>
                        </div>
                        <span class="font-mono text-[10px] uppercase tracking-[0.14em] text-neutral-500">{{ vouchers.length }} TOTAL</span>
                    </div>

                    <div class="space-y-3">
                        <article v-for="voucher in vouchers" :key="voucher.id" :class="['flex flex-col sm:flex-row sm:items-center justify-between border p-4 transition', voucher.is_active ? 'border-neutral-700 bg-[#050505]' : 'border-neutral-800 bg-neutral-900/30 opacity-70']">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-1">
                                    <h3 class="text-lg font-bold font-mono tracking-widest text-white">{{ voucher.code }}</h3>
                                    <span :class="['px-2 py-0.5 text-[8px] font-mono uppercase tracking-widest', voucher.is_active ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400']">
                                        {{ voucher.is_active ? 'ACTIVE' : 'DISABLED' }}
                                    </span>
                                </div>
                                <p class="text-xs text-neutral-400 uppercase tracking-wider">{{ voucher.event_name || 'TANPA NAMA EVENT' }}</p>
                                
                                <div class="mt-3 flex flex-wrap gap-4 font-mono text-[9px] uppercase text-neutral-500">
                                    <span>Kuota: <strong class="text-neutral-300">{{ voucher.used_count }} / {{ voucher.quota || '∞' }}</strong></span>
                                    <span>Frame terikat: <strong class="text-neutral-300">{{ voucher.frames.length }}</strong></span>
                                </div>
                            </div>
                            
                            <div class="mt-4 sm:mt-0 flex gap-2 shrink-0 border-t border-neutral-800 pt-3 sm:border-0 sm:pt-0">
                                <button @click="toggleStatus(voucher.id)" class="border border-neutral-700 px-3 py-2 font-mono text-[9px] uppercase tracking-widest text-neutral-400 hover:text-white transition">
                                    {{ voucher.is_active ? 'NONAKTIFKAN' : 'AKTIFKAN' }}
                                </button>
                                <button @click="deleteVoucher(voucher.id)" class="border border-neutral-700 px-3 py-2 font-mono text-[9px] uppercase tracking-widest text-red-400 hover:bg-red-500/10 transition">
                                    HAPUS
                                </button>
                            </div>
                        </article>

                        <div v-if="vouchers.length === 0" class="flex min-h-[200px] items-center justify-center border border-dashed border-neutral-800 text-center text-xs text-neutral-500">
                            Belum ada voucher yang digenerate.
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>