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

    <div
        class="fixed inset-0 flex h-screen min-h-0 w-screen select-none flex-col overflow-hidden bg-[#070707] px-4 py-3 font-sans text-white sm:px-8 sm:py-4 xl:px-12"
    >
        <!-- Ambient background -->
        <div class="pointer-events-none fixed inset-0 z-0 overflow-hidden">
            <img
                src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=1920&q=80"
                alt="Editorial Background"
                class="h-full w-full scale-105 object-cover object-center opacity-[0.10] grayscale contrast-125"
            />
            <div class="absolute inset-0 bg-gradient-to-b from-[#070707]/85 via-[#070707]/90 to-[#070707]"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_35%,transparent_0%,#070707_78%)]"></div>
            <div
                class="absolute inset-0 opacity-[0.045]"
                style="
                    background-image:
                        linear-gradient(to right, #ffffff 1px, transparent 1px),
                        linear-gradient(to bottom, #ffffff 1px, transparent 1px);
                    background-size: 112px 112px;
                "
            ></div>
        </div>

        <!-- Header -->
        <header
            class="relative z-10 mx-auto flex w-full max-w-[1440px] shrink-0 items-center justify-between border-b border-white/20 pb-3"
        >
            <div class="flex items-center gap-2" aria-label="SelfBooth Studio">
                <span class="h-3 w-3 rounded-full bg-white"></span>
                <span class="h-3 w-3 rounded-full bg-white"></span>
                <span class="h-3 w-3 rounded-full bg-white"></span>
            </div>

            <div class="flex items-center gap-5 sm:gap-8">
                <a
                    href="/admin/frames"
                    class="font-mono text-[10px] uppercase tracking-[0.18em] text-neutral-400 transition-colors duration-300 hover:text-white sm:text-xs"
                >
                    Admin Frames
                </a>

                <span class="hidden h-4 w-px bg-white/20 sm:block"></span>

                <div class="flex items-center gap-4" aria-hidden="true">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full border border-neutral-600">
                        <span class="h-1.5 w-1.5 rounded-full bg-neutral-300"></span>
                    </span>
                    <div class="flex w-6 flex-col gap-[5px]">
                        <span class="h-px w-full bg-white"></span>
                        <span class="h-px w-full bg-white"></span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main editorial content -->
        <main
            class="relative z-10 mx-auto my-2 flex min-h-0 w-full max-w-[1440px] flex-1 flex-col"
        >
            <!-- Hero heading -->
            <section
                class="mb-3 flex shrink-0 flex-col justify-between gap-2 md:flex-row md:items-end xl:mb-4"
            >
                <div>
                    <p class="mb-2 font-mono text-[10px] italic tracking-[0.16em] text-neutral-400 sm:text-xs">
                        Exclusive SelfBooth Studio
                    </p>
                    <div class="flex flex-wrap items-end gap-x-4 gap-y-1">
                        <h1
                            class="text-[clamp(2.8rem,7vw,6.5rem)] font-light uppercase leading-[0.78] tracking-[-0.075em]"
                        >
                            Access<span class="font-medium">.</span>
                        </h1>
                        <span
                            class="mb-1 font-mono text-[9px] uppercase tracking-[0.2em] text-neutral-500 sm:text-[10px]"
                        >
                            Choose your<br />experience / 01—03
                        </span>
                    </div>
                </div>

                <!-- Circular navigation / submit trigger -->
                <div class="flex items-center gap-3 self-end md:self-auto">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-full border border-neutral-700 bg-neutral-900/70 text-neutral-500 sm:h-12 sm:w-12"
                        aria-hidden="true"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"
                            />
                        </svg>
                    </div>

                    <button
                        type="button"
                        @click="form.payment_method && startSession()"
                        :disabled="!form.payment_method || form.processing || (form.payment_method === 'voucher' && !form.voucher_code)"
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-black transition-all duration-300 hover:scale-105 hover:bg-neutral-200 active:scale-95 disabled:cursor-not-allowed disabled:opacity-20 disabled:hover:scale-100 sm:h-12 sm:w-12"
                        title="Lanjutkan"
                        aria-label="Lanjutkan"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3"
                            />
                        </svg>
                    </button>
                </div>
            </section>

            <!-- Asymmetric editorial grid -->
            <section
                class="grid min-h-0 flex-1 grid-cols-1 grid-rows-3 overflow-hidden border-l border-t border-neutral-700 bg-[#080808]/80 backdrop-blur-sm md:grid-cols-2 md:grid-rows-2 lg:grid-cols-12 lg:grid-rows-2"
            >
                <!-- 01. CASHLESS / Featured panel -->
                <div
                    @click="selectMethod('cashless')"
                    :class="[
                        'group relative flex min-h-0 min-w-0 cursor-pointer flex-col overflow-hidden border-b border-r border-neutral-700 transition-colors duration-500 md:col-span-2 lg:col-span-7 lg:row-span-2',
                        form.payment_method === 'cashless'
                            ? 'bg-[#171717] ring-1 ring-inset ring-white'
                            : 'bg-[#090909]/90 hover:bg-[#111111]',
                    ]"
                >
                    <div class="flex h-full min-h-0 min-w-0 flex-1 flex-col md:flex-row">
                        <!-- Featured image -->
                        <div
                            class="relative hidden min-h-0 overflow-hidden border-b border-neutral-700 bg-neutral-950 sm:block sm:min-h-0 md:w-[40%] md:border-b-0 md:border-r"
                        >
                            <img
                                src="https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=1000&q=85"
                                alt="Cashless Studio"
                                :class="[
                                    'h-full min-h-0 w-full object-cover object-center transition-all duration-700 group-hover:scale-[1.04] sm:absolute sm:inset-0',
                                    form.payment_method === 'cashless'
                                        ? 'grayscale-0 opacity-90 contrast-110'
                                        : 'grayscale opacity-60 contrast-125 group-hover:grayscale-0 group-hover:opacity-90',
                                ]"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-black/15 md:bg-gradient-to-r md:from-transparent md:via-transparent md:to-black/35"></div>

                            <div class="absolute left-4 right-4 top-4 flex items-center justify-between sm:left-5 sm:right-5 sm:top-5">
                                <span class="border border-white/30 bg-black/55 px-2.5 py-1 font-mono text-[9px] uppercase tracking-[0.18em] text-neutral-200 backdrop-blur-sm">
                                    Direct Entry
                                </span>
                                <span
                                    :class="[
                                        'border px-2.5 py-1 font-mono text-[9px] uppercase tracking-[0.16em] backdrop-blur-sm',
                                        form.payment_method === 'cashless'
                                            ? 'border-white bg-white text-black'
                                            : 'border-white/30 bg-black/55 text-neutral-300',
                                    ]"
                                >
                                    {{ form.payment_method === 'cashless' ? 'Selected' : '01 / Ready' }}
                                </span>
                            </div>

                            <span class="absolute bottom-4 left-4 font-mono text-[10px] uppercase tracking-[0.2em] text-white/75 sm:bottom-6 sm:left-6">
                                Self portrait / no. 01
                            </span>
                        </div>

                        <!-- Featured content -->
                        <div class="flex min-h-0 min-w-0 flex-1 flex-col justify-between overflow-hidden p-3 sm:p-4 lg:p-5 xl:p-7">
                            <div>
                                <div class="mb-2 flex items-center justify-between gap-3 sm:mb-3">
                                    <p class="font-mono text-[9px] uppercase tracking-[0.2em] text-neutral-500">
                                        Access category / 01
                                    </p>
                                    <span class="h-px flex-1 bg-neutral-800"></span>
                                </div>

                                <h2 class="mb-2 text-2xl font-normal uppercase leading-none tracking-[-0.04em] sm:text-3xl xl:text-4xl">
                                    Cashless
                                </h2>
                                <p class="mb-2 hidden max-w-md font-mono text-[10px] italic leading-relaxed text-neutral-400 sm:mb-3 sm:block sm:text-[11px] xl:text-xs">
                                    Akses langsung tanpa pembayaran antrean. Mulai sesi pemotretan instan dengan satu sentuhan.
                                </p>

                                <ul class="hidden space-y-1 border-t border-neutral-800 pt-2 font-mono text-[9px] text-neutral-300 sm:block sm:text-[10px]">
                                    <li class="flex items-center gap-3">
                                        <span class="text-neutral-500">—</span>
                                        Mulai sesi foto langsung
                                    </li>
                                    <li class="flex items-center gap-3">
                                        <span class="text-neutral-500">—</span>
                                        Tanpa pembayaran antrean
                                    </li>
                                    <li class="flex items-center gap-3">
                                        <span class="text-neutral-500">—</span>
                                        Langsung mulai sesi
                                    </li>
                                </ul>
                            </div>

                            <div class="mt-2 flex shrink-0 items-end justify-between border-t border-neutral-800 pt-2 sm:mt-3 sm:pt-3">
                                <span
                                    :class="[
                                        'inline-flex items-center gap-3 border px-3 py-2 font-mono text-[9px] uppercase tracking-[0.18em] transition-colors duration-300 sm:text-[10px]',
                                        form.payment_method === 'cashless'
                                            ? 'border-white bg-white text-black'
                                            : 'border-white/60 text-white group-hover:bg-white group-hover:text-black',
                                    ]"
                                >
                                    Select access <span class="text-sm">→</span>
                                </span>
                                <span class="font-mono text-4xl leading-none text-neutral-600 sm:text-5xl">
                                    01
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 02. VOUCHER -->
                <div
                    @click="selectMethod('voucher')"
                    :class="[
                        'group relative flex min-h-0 min-w-0 cursor-pointer flex-col overflow-hidden border-b border-r border-neutral-700 transition-colors duration-500 lg:col-span-5',
                        form.payment_method === 'voucher'
                            ? 'bg-[#171717] ring-1 ring-inset ring-white'
                            : 'bg-[#090909]/90 hover:bg-[#111111]',
                    ]"
                >
                    <div class="flex h-full min-h-0 min-w-0 flex-1 flex-col sm:flex-row">
                        <div class="relative hidden min-h-0 overflow-hidden border-b border-neutral-700 bg-neutral-950 sm:block sm:w-[30%] sm:border-b-0 sm:border-r">
                            <img
                                src="https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?auto=format&fit=crop&w=800&q=85"
                                alt="Voucher Ticket"
                                :class="[
                                    'h-full min-h-0 w-full object-cover object-center transition-all duration-700 group-hover:scale-105 sm:absolute sm:inset-0',
                                    form.payment_method === 'voucher'
                                        ? 'grayscale-0 opacity-90 contrast-110'
                                        : 'grayscale opacity-60 contrast-125 group-hover:grayscale-0 group-hover:opacity-90',
                                ]"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/15 sm:bg-gradient-to-r sm:from-transparent sm:to-black/30"></div>
                            <span class="absolute bottom-3 left-3 font-mono text-[9px] uppercase tracking-[0.16em] text-white/75 sm:bottom-4 sm:left-4">
                                Redeem / 02
                            </span>
                        </div>

                        <div class="flex min-h-0 min-w-0 flex-1 flex-col justify-between overflow-hidden p-2.5 sm:p-3 lg:p-4">
                            <div>
                                <div class="mb-1.5 flex items-center justify-between gap-2">
                                    <p class="hidden font-mono text-[9px] uppercase tracking-[0.18em] text-neutral-500 sm:block">
                                        Access category / 02
                                    </p>
                                    <span
                                        :class="[
                                            'border px-2 py-1 font-mono text-[9px] uppercase tracking-[0.14em]',
                                            form.payment_method === 'voucher'
                                                ? 'border-white bg-white text-black'
                                                : 'border-neutral-700 text-neutral-400',
                                        ]"
                                    >
                                        {{ form.payment_method === 'voucher' ? 'Selected' : 'Code' }}
                                    </span>
                                </div>

                                <h2 class="mb-1 text-xl font-normal uppercase leading-none tracking-[-0.035em] sm:text-2xl">
                                    Voucher
                                </h2>
                                <p class="mb-1.5 line-clamp-2 max-w-lg font-mono text-[9px] italic leading-snug text-neutral-400 sm:text-[10px]">
                                    Gunakan kode voucher yang tersedia untuk membuka akses kamera secara instan.
                                </p>

                                <ul
                                    v-if="form.payment_method !== 'voucher'"
                                    class="space-y-0.5 border-t border-neutral-800 pt-1.5 font-mono text-[8px] text-neutral-300 sm:text-[9px]"
                                >
                                    <li class="flex items-center gap-2"><span class="text-neutral-500">—</span> Gunakan kode voucher aktif</li>
                                    <li class="flex items-center gap-2"><span class="text-neutral-500">—</span> Masukkan kode di bawah</li>
                                    <li class="flex items-center gap-2"><span class="text-neutral-500">—</span> Langsung mulai sesi</li>
                                </ul>

                                <!-- Voucher input -->
                                <div
                                    v-if="form.payment_method === 'voucher'"
                                    class="mt-2"
                                    @click.stop
                                >
                                    <label for="voucher-code" class="mb-1 block font-mono text-[8px] uppercase tracking-[0.16em] text-neutral-400">
                                        Voucher code
                                    </label>
                                    <input
                                        id="voucher-code"
                                        type="text"
                                        v-model="form.voucher_code"
                                        placeholder="MASUKKAN KODE"
                                        class="w-full border border-neutral-600 bg-black/70 px-2.5 py-1.5 text-center font-mono text-[10px] uppercase tracking-[0.18em] text-white placeholder:text-neutral-600 focus:border-white focus:outline-none"
                                    />
                                    <div
                                        v-if="form.errors.voucher_code"
                                        class="mt-1.5 font-mono text-[10px] text-red-400"
                                    >
                                        * {{ form.errors.voucher_code }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-1.5 flex shrink-0 items-end justify-between border-t border-neutral-800 pt-1.5">
                                <span
                                    :class="[
                                        'inline-flex items-center gap-2 border px-3 py-1.5 font-mono text-[9px] uppercase tracking-[0.16em] transition-colors duration-300',
                                        form.payment_method === 'voucher'
                                            ? 'border-white bg-white text-black'
                                            : 'border-white/60 text-white group-hover:bg-white group-hover:text-black',
                                    ]"
                                >
                                    Select <span>→</span>
                                </span>
                                <span class="font-mono text-3xl leading-none text-neutral-600">02</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 03. ONLINE PAYMENT -->
                <div
                    @click="selectMethod('online')"
                    :class="[
                        'group relative flex min-h-0 min-w-0 cursor-pointer flex-col overflow-hidden border-b border-r border-neutral-700 transition-colors duration-500 lg:col-span-5',
                        form.payment_method === 'online'
                            ? 'bg-[#171717] ring-1 ring-inset ring-white'
                            : 'bg-[#090909]/90 hover:bg-[#111111]',
                    ]"
                >
                    <div class="flex h-full min-h-0 min-w-0 flex-1 flex-col sm:flex-row">
                        <div class="relative hidden min-h-0 overflow-hidden border-b border-neutral-700 bg-neutral-950 sm:block sm:w-[30%] sm:border-b-0 sm:border-r">
                            <img
                                src="https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=800&q=85"
                                alt="Online Digital Payment"
                                :class="[
                                    'h-full min-h-0 w-full object-cover object-center transition-all duration-700 group-hover:scale-105 sm:absolute sm:inset-0',
                                    form.payment_method === 'online'
                                        ? 'grayscale-0 opacity-90 contrast-110'
                                        : 'grayscale opacity-60 contrast-125 group-hover:grayscale-0 group-hover:opacity-90',
                                ]"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/15 sm:bg-gradient-to-r sm:from-transparent sm:to-black/30"></div>
                            <span class="absolute bottom-3 left-3 font-mono text-[9px] uppercase tracking-[0.16em] text-white/75 sm:bottom-4 sm:left-4">
                                Digital / 03
                            </span>
                        </div>

                        <div class="flex min-h-0 min-w-0 flex-1 flex-col justify-between overflow-hidden p-2.5 sm:p-3 lg:p-4">
                            <div>
                                <div class="mb-1.5 flex items-center justify-between gap-2">
                                    <p class="hidden font-mono text-[9px] uppercase tracking-[0.18em] text-neutral-500 sm:block">
                                        Access category / 03
                                    </p>
                                    <span
                                        :class="[
                                            'border px-2 py-1 font-mono text-[9px] uppercase tracking-[0.14em]',
                                            form.payment_method === 'online'
                                                ? 'border-white bg-white text-black'
                                                : 'border-neutral-700 text-neutral-400',
                                        ]"
                                    >
                                        {{ form.payment_method === 'online' ? 'Selected' : 'QRIS' }}
                                    </span>
                                </div>

                                <h2 class="mb-1 text-xl font-normal uppercase leading-none tracking-[-0.035em] sm:text-2xl">
                                    Online Pay
                                </h2>
                                <p class="mb-1.5 hidden max-w-lg font-mono text-[9px] italic leading-snug text-neutral-400 sm:line-clamp-2 sm:block sm:text-[10px]">
                                    Pembayaran digital cepat dan mudah melalui integrasi gerbang otomatis.
                                </p>

                                <ul class="hidden space-y-0.5 border-t border-neutral-800 pt-1.5 font-mono text-[8px] text-neutral-300 sm:block sm:text-[9px]">
                                    <li class="flex items-center gap-2"><span class="text-neutral-500">—</span> Bayar menggunakan QRIS</li>
                                    <li class="flex items-center gap-2"><span class="text-neutral-500">—</span> E-Wallet (OVO, Dana, dll)</li>
                                    <li class="flex items-center gap-2"><span class="text-neutral-500">—</span> Transfer Bank / VA</li>
                                </ul>
                            </div>

                            <div class="mt-1.5 flex shrink-0 items-end justify-between border-t border-neutral-800 pt-1.5">
                                <span
                                    :class="[
                                        'inline-flex items-center gap-2 border px-3 py-1.5 font-mono text-[9px] uppercase tracking-[0.16em] transition-colors duration-300',
                                        form.payment_method === 'online'
                                            ? 'border-white bg-white text-black'
                                            : 'border-white/60 text-white group-hover:bg-white group-hover:text-black',
                                    ]"
                                >
                                    Select <span>→</span>
                                </span>
                                <span class="font-mono text-3xl leading-none text-neutral-600">03</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Bottom action bar -->
            <footer
                class="mt-2 flex shrink-0 flex-col items-start justify-between gap-2 border border-neutral-700 bg-[#090909]/90 px-3 py-2 backdrop-blur-md sm:flex-row sm:items-center sm:px-4"
            >
                <div class="font-mono text-[10px] leading-relaxed text-neutral-400 sm:text-xs">
                    <span v-if="form.payment_method">
                        Selected access:
                        <strong class="ml-1 font-mono uppercase tracking-[0.16em] text-white">
                            [ {{ form.payment_method }} ]
                        </strong>
                    </span>
                    <span v-else class="italic">
                        Pilih salah satu metode akses di atas untuk memulai sesi foto Anda...
                    </span>
                </div>

                <button
                    v-if="form.payment_method"
                    @click="startSession"
                    :disabled="form.processing || (form.payment_method === 'voucher' && !form.voucher_code)"
                    class="flex w-full items-center justify-between gap-8 bg-white px-5 py-2.5 font-mono text-[10px] font-bold uppercase tracking-[0.2em] text-black transition-colors duration-300 hover:bg-neutral-200 disabled:cursor-not-allowed disabled:opacity-30 sm:w-auto sm:min-w-[190px] sm:justify-center"
                >
                    <span>{{ form.processing ? "MEMPROSES..." : "LANJUTKAN" }}</span>
                    <span class="text-base">→</span>
                </button>
            </footer>
        </main>
    </div>
</template>
