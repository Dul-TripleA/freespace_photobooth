<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

// Menggunakan form helper dari Inertia
const form = useForm({
    payment_method: "",
    voucher_code: "",
});

// Menangani klik pada kartu pembayaran
const selectMethod = (method) => {
    form.payment_method = method;
    form.clearErrors();
};

// Mengirim data ke SessionController@start
const startSession = () => {
    form.post(route("kiosk.session.start"));
};
</script>

<template>

    <Head title="Pilih Metode Akses" />

    <!-- Container Utama -->
    <div
        class="min-h-screen bg-neutral-950 text-white flex flex-col items-center justify-center px-5 py-8 sm:px-8 select-none relative overflow-hidden"
    >

        <!-- Background Glow -->
        <div
            class="absolute -top-48 -left-48 w-[500px] h-[500px] bg-amber-500/10 rounded-full blur-[120px] pointer-events-none"
        ></div>

        <div
            class="absolute -bottom-48 -right-48 w-[500px] h-[500px] bg-orange-500/10 rounded-full blur-[120px] pointer-events-none"
        ></div>

        <!-- Background Grid -->
        <div
            class="absolute inset-0 opacity-[0.025] pointer-events-none"
            style="
                background-image:
                    linear-gradient(rgba(255,255,255,.6) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255,255,255,.6) 1px, transparent 1px);
                background-size: 45px 45px;
            "
        ></div>


        <!-- Content -->
        <div class="relative z-10 w-full flex flex-col items-center">

            <!-- Header -->
            <div class="text-center mb-10 sm:mb-12">

                <!-- Small Badge -->
                <div
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-amber-400/10 border border-amber-400/20 text-amber-400 text-[10px] sm:text-xs font-bold uppercase tracking-[0.25em] mb-5"
                >
                    <span
                        class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"
                    ></span>

                    SelfBooth
                </div>

                <h1
                    class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight mb-4"
                >
                    Selamat Datang
                    <span class="text-amber-400">!</span>
                </h1>

                <p
                    class="text-sm sm:text-base text-neutral-400 max-w-xl mx-auto leading-relaxed"
                >
                    Pilih metode akses untuk memulai sesi foto Anda.
                </p>

            </div>


            <!-- Admin -->
            <a
                href="/admin/frames"
                class="mb-8 text-[10px] sm:text-xs text-neutral-500 hover:text-amber-400 transition-colors flex items-center gap-2"
            >
                <span>⚙️</span>
                <span class="underline underline-offset-4">
                    Kelola Frame Admin
                </span>
            </a>


            <!-- Grid 3 Pilihan -->
            <div
                class="grid grid-cols-1 md:grid-cols-3 gap-5 lg:gap-6 max-w-6xl w-full px-0 sm:px-4"
            >

                <!-- 1. Card Cashless -->
                <div
                    @click="selectMethod('cashless')"
                    :class="[
                        'group relative bg-neutral-900/80 backdrop-blur-xl rounded-3xl p-6 sm:p-8 cursor-pointer transition-all duration-300 border flex flex-col items-center text-center overflow-hidden',
                        form.payment_method === 'cashless'
                            ? 'border-blue-400/70 shadow-[0_0_45px_rgba(59,130,246,0.18)] -translate-y-1'
                            : 'border-white/10 hover:border-blue-400/40 hover:-translate-y-1 hover:bg-neutral-800/80',
                    ]"
                >

                    <!-- Card Glow -->
                    <div
                        class="absolute -top-20 -right-20 w-40 h-40 bg-blue-500/10 rounded-full blur-3xl opacity-0 group-hover:opacity-100 transition-opacity"
                    ></div>


                    <!-- Selected Indicator -->
                    <div
                        v-if="form.payment_method === 'cashless'"
                        class="absolute top-4 right-4 w-7 h-7 rounded-full bg-blue-500 flex items-center justify-center shadow-lg"
                    >
                        <svg
                            class="w-4 h-4 text-white"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="3"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>
                    </div>


                    <!-- Icon -->
                    <div
                        class="relative w-20 h-20 rounded-2xl bg-blue-500/10 border border-blue-400/10 flex items-center justify-center mb-6 transition-all duration-300 group-hover:scale-105 group-hover:bg-blue-500/15"
                    >
                        <svg
                            class="w-10 h-10 text-blue-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                            >
                            </path>
                        </svg>
                    </div>


                    <h2
                        class="relative text-xl sm:text-2xl font-black mb-3"
                    >
                        Cashless
                    </h2>

                    <p
                        class="text-xs text-neutral-500 mb-5"
                    >
                        Akses langsung tanpa pembayaran
                    </p>

                    <ul
                        class="relative text-neutral-400 text-sm space-y-3 text-left w-full"
                    >
                        <li class="flex items-center gap-3">
                            <span
                                class="w-5 h-5 rounded-full bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0"
                            >
                                ✓
                            </span>
                            Mulai sesi foto langsung
                        </li>

                        <li class="flex items-center gap-3">
                            <span
                                class="w-5 h-5 rounded-full bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0"
                            >
                                ✓
                            </span>
                            Tanpa pembayaran antrean
                        </li>

                        <li class="flex items-center gap-3">
                            <span
                                class="w-5 h-5 rounded-full bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0"
                            >
                                ✓
                            </span>
                            Langsung mulai sesi
                        </li>
                    </ul>

                </div>


                <!-- 2. Card Voucher -->
                <div
                    @click="selectMethod('voucher')"
                    :class="[
                        'group relative bg-neutral-900/80 backdrop-blur-xl rounded-3xl p-6 sm:p-8 cursor-pointer transition-all duration-300 border flex flex-col items-center text-center overflow-hidden',
                        form.payment_method === 'voucher'
                            ? 'border-orange-400/70 shadow-[0_0_45px_rgba(249,115,22,0.18)] -translate-y-1'
                            : 'border-white/10 hover:border-orange-400/40 hover:-translate-y-1 hover:bg-neutral-800/80',
                    ]"
                >

                    <!-- Card Glow -->
                    <div
                        class="absolute -top-20 -right-20 w-40 h-40 bg-orange-500/10 rounded-full blur-3xl opacity-0 group-hover:opacity-100 transition-opacity"
                    ></div>


                    <!-- Selected Indicator -->
                    <div
                        v-if="form.payment_method === 'voucher'"
                        class="absolute top-4 right-4 w-7 h-7 rounded-full bg-orange-500 flex items-center justify-center shadow-lg"
                    >
                        <svg
                            class="w-4 h-4 text-white"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="3"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>
                    </div>


                    <!-- Icon -->
                    <div
                        class="relative w-20 h-20 rounded-2xl bg-orange-500/10 border border-orange-400/10 flex items-center justify-center mb-6 transition-all duration-300 group-hover:scale-105 group-hover:bg-orange-500/15"
                    >
                        <svg
                            class="w-10 h-10 text-orange-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"
                            >
                            </path>
                        </svg>
                    </div>


                    <h2
                        class="relative text-xl sm:text-2xl font-black mb-3"
                    >
                        Voucher Payment
                    </h2>

                    <p
                        class="text-xs text-neutral-500 mb-5"
                    >
                        Gunakan kode voucher yang tersedia
                    </p>


                    <ul
                        class="relative text-neutral-400 text-sm space-y-3 text-left w-full mb-5"
                    >
                        <li class="flex items-center gap-3">
                            <span
                                class="w-5 h-5 rounded-full bg-orange-500/10 text-orange-400 flex items-center justify-center shrink-0"
                            >
                                ✓
                            </span>
                            Gunakan kode voucher aktif
                        </li>

                        <li class="flex items-center gap-3">
                            <span
                                class="w-5 h-5 rounded-full bg-orange-500/10 text-orange-400 flex items-center justify-center shrink-0"
                            >
                                ✓
                            </span>
                            Masukkan kode di bawah
                        </li>

                        <li class="flex items-center gap-3">
                            <span
                                class="w-5 h-5 rounded-full bg-orange-500/10 text-orange-400 flex items-center justify-center shrink-0"
                            >
                                ✓
                            </span>
                            Langsung mulai sesi
                        </li>
                    </ul>


                    <!-- Input Voucher -->
                    <div
                        v-if="form.payment_method === 'voucher'"
                        class="relative w-full mt-auto"
                    >

                        <input
                            type="text"
                            v-model="form.voucher_code"
                            placeholder="MASUKKAN KODE"
                            class="w-full bg-neutral-950 border border-orange-400/30 rounded-xl px-4 py-3.5 text-center text-white placeholder-neutral-600 focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-400/10 uppercase font-black tracking-[0.2em] text-sm transition"
                            @click.stop
                        />

                        <div
                            v-if="form.errors.voucher_code"
                            class="text-red-400 text-xs mt-2"
                        >
                            {{ form.errors.voucher_code }}
                        </div>

                    </div>

                </div>


                <!-- 3. Card Online Payment -->
                <div
                    @click="selectMethod('online')"
                    :class="[
                        'group relative bg-neutral-900/80 backdrop-blur-xl rounded-3xl p-6 sm:p-8 cursor-pointer transition-all duration-300 border flex flex-col items-center text-center overflow-hidden',
                        form.payment_method === 'online'
                            ? 'border-emerald-400/70 shadow-[0_0_45px_rgba(16,185,129,0.18)] -translate-y-1'
                            : 'border-white/10 hover:border-emerald-400/40 hover:-translate-y-1 hover:bg-neutral-800/80',
                    ]"
                >

                    <!-- Card Glow -->
                    <div
                        class="absolute -top-20 -right-20 w-40 h-40 bg-emerald-500/10 rounded-full blur-3xl opacity-0 group-hover:opacity-100 transition-opacity"
                    ></div>


                    <!-- Selected Indicator -->
                    <div
                        v-if="form.payment_method === 'online'"
                        class="absolute top-4 right-4 w-7 h-7 rounded-full bg-emerald-500 flex items-center justify-center shadow-lg"
                    >
                        <svg
                            class="w-4 h-4 text-white"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="3"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>
                    </div>


                    <!-- Icon -->
                    <div
                        class="relative w-20 h-20 rounded-2xl bg-emerald-500/10 border border-emerald-400/10 flex items-center justify-center mb-6 transition-all duration-300 group-hover:scale-105 group-hover:bg-emerald-500/15"
                    >
                        <svg
                            class="w-10 h-10 text-emerald-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"
                            >
                            </path>
                        </svg>
                    </div>


                    <h2
                        class="relative text-xl sm:text-2xl font-black mb-3"
                    >
                        Online Payment
                    </h2>

                    <p
                        class="text-xs text-neutral-500 mb-5"
                    >
                        Pembayaran digital yang cepat dan mudah
                    </p>


                    <ul
                        class="relative text-neutral-400 text-sm space-y-3 text-left w-full"
                    >
                        <li class="flex items-center gap-3">
                            <span
                                class="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0"
                            >
                                ✓
                            </span>
                            Bayar menggunakan QRIS
                        </li>

                        <li class="flex items-center gap-3">
                            <span
                                class="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0"
                            >
                                ✓
                            </span>
                            E-Wallet (OVO, Dana, dll)
                        </li>

                        <li class="flex items-center gap-3">
                            <span
                                class="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0"
                            >
                                ✓
                            </span>
                            Transfer Bank / VA
                        </li>
                    </ul>

                </div>

            </div>


            <!-- Tombol Lanjut -->
            <div class="mt-10 sm:mt-12 h-16">

                <button
                    v-if="form.payment_method"
                    @click="startSession"
                    :disabled="form.processing ||
                        (form.payment_method === 'voucher' && !form.voucher_code)
                    "
                    class="group bg-amber-400 text-neutral-950 px-12 sm:px-16 py-4 rounded-2xl font-black text-base sm:text-lg hover:bg-amber-300 hover:-translate-y-0.5 transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:translate-y-0 shadow-[0_10px_40px_rgba(251,191,36,0.15)] flex items-center justify-center gap-3 min-w-[220px]"
                >

                    <span>
                        {{ form.processing ? "Memproses..." : "Lanjutkan" }}
                    </span>

                    <svg
                        v-if="!form.processing"
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5 transition-transform duration-200 group-hover:translate-x-1"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2.5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M13 7l5 5m0 0l-5 5m5-5H6"
                        />
                    </svg>

                </button>

            </div>

        </div>

    </div>
</template>