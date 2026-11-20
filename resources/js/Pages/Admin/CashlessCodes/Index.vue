<script setup>
import { useForm, router } from '@inertiajs/vue3';

defineProps({
    codes: Array,
});

const form = useForm({
    quantity: 1,
});

const generateCode = () => {
    form.post('/admin/cashless-codes', { preserveScroll: true });
};

const deleteCode = (id) => {
    if (confirm('Hapus kode ini?')) {
        router.delete(`/admin/cashless-codes/${id}`, { preserveScroll: true });
    }
};

const copyCode = (code) => {
    navigator.clipboard.writeText(code);
    alert(`Kode ${code} disalin!`);
};
</script>

<template>
    <div class="min-h-screen bg-neutral-950 p-8 font-mono text-white">
        <div class="mx-auto max-w-3xl">
            <div class="flex items-center justify-between border-b border-white/15 pb-6">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">KODE UNIK CASHLESS (1x PAKAI)</h1>
                    <p class="mt-1 text-xs text-neutral-400">Generate kode untuk pelanggan yang sudah membayar lewat kasir/operator.</p>
                </div>
                <button
                    @click="generateCode"
                    :disabled="form.processing"
                    class="bg-white px-5 py-3 text-xs font-bold tracking-widest text-black hover:bg-neutral-200 active:scale-95"
                >
                    + GENERATE 1 KODE
                </button>
            </div>

            <div class="mt-6 space-y-2">
                <div
                    v-for="item in codes"
                    :key="item.id"
                    class="flex items-center justify-between border border-white/15 bg-neutral-900 px-5 py-4"
                >
                    <div class="flex items-center gap-4">
                        <span
                            class="text-xl font-black tracking-[0.25em]"
                            :class="item.is_used ? 'text-neutral-500 line-through' : 'text-emerald-400'"
                        >
                            {{ item.code }}
                        </span>
                        <span
                            class="px-2 py-0.5 text-[10px] font-bold tracking-widest"
                            :class="item.is_used ? 'bg-red-500/20 text-red-400' : 'bg-emerald-500/20 text-emerald-400'"
                        >
                            {{ item.is_used ? 'TERPAKAI' : 'TERSEDIA' }}
                        </span>
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            v-if="!item.is_used"
                            @click="copyCode(item.code)"
                            class="border border-white/20 px-3 py-1.5 text-xs hover:bg-white hover:text-black"
                        >
                            SALIN
                        </button>
                        <button
                            @click="deleteCode(item.id)"
                            class="text-xs text-neutral-500 hover:text-red-400"
                        >
                            HAPUS
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>