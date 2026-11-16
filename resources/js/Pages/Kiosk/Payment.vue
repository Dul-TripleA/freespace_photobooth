<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, computed, onMounted, onUnmounted } from "vue";

const props = defineProps({
    session: Object,
    dynamicQrisString: String,
});

const qrImageSrc = computed(() => {
    if (!props.dynamicQrisString) return "";
    return `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(props.dynamicQrisString)}`;
});

const status = ref(props.session.payment_status);
let pollInterval = null;

// Pisahkan nominal utama dan 3 digit kode unik agar mudah dibaca
const formattedParts = computed(() => {
    const formatted = new Intl.NumberFormat("id-ID").format(props.session.amount || 0);
    return {
        prefix: formatted.slice(0, -3),
        unique: formatted.slice(-3),
    };
});

const checkPaymentStatus = async () => {
    try {
        const res = await fetch(`/kiosk/payment/${props.session.id}/status`);
        const data = await res.json();
        status.value = data.payment_status;

        if (data.payment_status === "paid" && data.redirect_url) {
            clearInterval(pollInterval);
            router.visit(data.redirect_url);
        }
    } catch (err) {
        console.error("Polling error:", err);
    }
};

onMounted(() => {
    pollInterval = setInterval(checkPaymentStatus, 3000);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});
</script>

<template>

    <Head title="Pembayaran QRIS - SelfBooth" />

    <div
        class="fixed inset-0 flex h-screen w-screen flex-col justify-between bg-[#050505] p-6 font-sans text-white lg:p-10">
        <header class="flex items-center justify-between border-b border-neutral-800 pb-4">
            <span class="font-mono text-xs uppercase tracking-[0.24em] text-neutral-400">
                01 / Static QRIS Payment
            </span>
            <span class="font-mono text-xs uppercase tracking-[0.2em] text-neutral-500">
                {{ session.invoice_number }}
            </span>
        </header>

        <main class="mx-auto flex w-full max-w-4xl flex-col items-center gap-8 md:flex-row md:justify-between">
            <div class="space-y-4 text-center md:text-left">
                <p class="font-mono text-[10px] uppercase tracking-[0.22em] text-neutral-500">
                    TRANSFER PERSIS HINGGA 3 DIGIT TERAKHIR
                </p>
                <h1 class="text-4xl font-medium uppercase leading-none tracking-[-0.05em] sm:text-5xl">
                    Scan to <span class="text-neutral-500">Pay.</span>
                </h1>

                <!-- Nominal dengan highlight 3 digit terakhir -->
                <div class="inline-block border border-neutral-800 bg-[#0a0a0a] px-5 py-3">
                    <p class="font-mono text-3xl font-bold tracking-tight sm:text-4xl">
                        <span class="text-neutral-400">Rp </span>
                        <span class="text-white">{{ formattedParts.prefix }}</span>
                        <span class="bg-amber-400 px-1 text-black">{{ formattedParts.unique }}</span>
                    </p>
                </div>

                <p class="max-w-sm text-xs leading-relaxed text-neutral-400">
                    Scan QRIS di samping lalu masukkan nominal <strong class="text-white">persis sama</strong> termasuk
                    3 digit kode unik berwarna kuning agar sesi foto terbuka otomatis.
                </p>

                <div class="inline-flex items-center gap-3 border border-neutral-800 bg-[#0a0a0a] px-4 py-2.5">
                    <span class="h-2 w-2 animate-pulse rounded-full bg-amber-400"></span>
                    <span class="font-mono text-[10px] uppercase tracking-[0.18em] text-neutral-300">
                        Menunggu Konfirmasi ({{ status }})
                    </span>
                </div>
            </div>

            <!-- Gambar QRIS Statis -->
            <div class="relative border border-neutral-700 bg-white p-3">
                <img v-if="qrImageSrc" :src="qrImageSrc" alt="QRIS Dinamis"
                    class="h-64 w-64 object-contain sm:h-72 sm:w-72" />
                <span class="absolute -left-2 -top-2 h-4 w-4 border-l-2 border-t-2 border-white"></span>
                <span class="absolute -right-2 -top-2 h-4 w-4 border-r-2 border-t-2 border-white"></span>
                <span class="absolute -bottom-2 -left-2 h-4 w-4 border-b-2 border-l-2 border-white"></span>
                <span class="absolute -bottom-2 -right-2 h-4 w-4 border-b-2 border-r-2 border-white"></span>
            </div>
        </main>

        <footer class="flex items-center justify-between border-t border-neutral-800 pt-4">
            <Link href="/"
                class="border border-neutral-700 px-5 py-2.5 font-mono text-[10px] uppercase tracking-[0.18em] text-neutral-400 transition hover:border-white hover:text-white">
                ← Batalkan Sesi
            </Link>
            <span class="font-mono text-[10px] uppercase tracking-[0.2em] text-neutral-600">
                Auto-Verify Webhook Active
            </span>
        </footer>
    </div>
</template>