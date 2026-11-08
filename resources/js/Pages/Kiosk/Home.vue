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

    <!-- Container Utama (Kiosk Mode: Fullscreen, Dark Theme) -->
    <div
        class="min-h-screen bg-gray-900 text-white flex flex-col items-center justify-center p-8 select-none"
    >
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold tracking-tight mb-4">
                Selamat Datang di SelfBooth
            </h1>
            <p class="text-lg text-gray-400">
                Silakan pilih metode akses untuk memulai sesi foto Anda.
            </p>
        </div>

        <!-- Grid 3 Pilihan -->
        <div
            class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl w-full px-4"
        >
            <!-- 1. Card Cashless (Gratis/Event) -->
            <div
                @click="selectMethod('cashless')"
                :class="[
                    'relative bg-gray-800 rounded-2xl p-8 cursor-pointer transition-all duration-300 border-2 flex flex-col items-center text-center',
                    form.payment_method === 'cashless'
                        ? 'border-blue-500 shadow-[0_0_30px_rgba(59,130,246,0.3)] scale-105'
                        : 'border-gray-700 hover:border-gray-500',
                ]"
            >
                <div
                    class="w-20 h-20 bg-gray-700 rounded-full flex items-center justify-center mb-6"
                >
                    <svg
                        class="w-10 h-10 text-gray-300"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                        ></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold mb-3">Cashless</h2>
                <ul
                    class="text-gray-400 text-sm space-y-2 text-left w-full list-disc list-inside"
                >
                    <li>Mulai sesi foto langsung</li>
                    <li>Tanpa pembayaran antrean</li>
                    <li>Langsung mulai sesi</li>
                </ul>
            </div>

            <!-- 2. Card Voucher -->
            <div
                @click="selectMethod('voucher')"
                :class="[
                    'relative bg-gray-800 rounded-2xl p-8 cursor-pointer transition-all duration-300 border-2 flex flex-col items-center text-center',
                    form.payment_method === 'voucher'
                        ? 'border-orange-500 shadow-[0_0_30px_rgba(249,115,22,0.3)] scale-105'
                        : 'border-gray-700 hover:border-gray-500',
                ]"
            >
                <div
                    class="w-20 h-20 bg-gray-700 rounded-full flex items-center justify-center mb-6"
                >
                    <svg
                        class="w-10 h-10 text-gray-300"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"
                        ></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold mb-3">Voucher Payment</h2>
                <ul
                    class="text-gray-400 text-sm space-y-2 text-left w-full list-disc list-inside mb-4"
                >
                    <li>Gunakan kode voucher aktif</li>
                    <li>Masukkan kode di bawah</li>
                    <li>Langsung mulai sesi</li>
                </ul>

                <!-- Input Voucher (Muncul hanya jika voucher dipilih) -->
                <div
                    v-if="form.payment_method === 'voucher'"
                    class="w-full mt-auto"
                >
                    <input
                        type="text"
                        v-model="form.voucher_code"
                        placeholder="Masukkan Kode"
                        class="w-full bg-gray-900 border border-gray-600 rounded-lg px-4 py-3 text-center text-white focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 uppercase font-bold tracking-widest"
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

            <!-- 3. Card Online Payment (QRIS) -->
            <div
                @click="selectMethod('online')"
                :class="[
                    'relative bg-gray-800 rounded-2xl p-8 cursor-pointer transition-all duration-300 border-2 flex flex-col items-center text-center',
                    form.payment_method === 'online'
                        ? 'border-emerald-500 shadow-[0_0_30px_rgba(16,185,129,0.3)] scale-105'
                        : 'border-gray-700 hover:border-gray-500',
                ]"
            >
                <div
                    class="w-20 h-20 bg-gray-700 rounded-full flex items-center justify-center mb-6"
                >
                    <svg
                        class="w-10 h-10 text-gray-300"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"
                        ></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold mb-3">Online Payment</h2>
                <ul
                    class="text-gray-400 text-sm space-y-2 text-left w-full list-disc list-inside"
                >
                    <li>Bayar menggunakan QRIS</li>
                    <li>E-Wallet (OVO, Dana, dll)</li>
                    <li>Transfer Bank / VA</li>
                </ul>
            </div>
        </div>

        <!-- Tombol Lanjut -->
        <div class="mt-12 h-16">
            <button
                v-if="form.payment_method"
                @click="startSession"
                :disabled="
                    form.processing ||
                    (form.payment_method === 'voucher' && !form.voucher_code)
                "
                class="bg-white text-gray-900 px-12 py-4 rounded-full font-bold text-xl hover:bg-gray-200 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
                {{ form.processing ? "Memproses..." : "Lanjutkan" }}
            </button>
        </div>
    </div>
</template>
