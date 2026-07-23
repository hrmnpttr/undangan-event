<div
    x-data="{
        phone: {{ Js::from($tamu->nomor_wa ?? '') }},
        message: {{ Js::from($pesan) }},
        copied: false,
        sent: {{ $tamu->wa_terkirim_at ? 'true' : 'false' }},
        normalizePhone(num) {
            num = num.replace(/\D/g, '');
            if (num.startsWith('0')) {
                num = '62' + num.slice(1);
            } else if (!num.startsWith('62')) {
                num = '62' + num;
            }
            return num;
        },
        get waUrl() {
            const trimmed = this.phone.trim();
            if (!trimmed) return null;
            return 'https://wa.me/' + this.normalizePhone(trimmed) + '?text=' + encodeURIComponent(this.message);
        },
        async copyMessage() {
            await navigator.clipboard.writeText(this.message);
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        }
    }"
    class="p-4 space-y-4"
>
    {{-- Phone input --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Nomor WhatsApp
        </label>
        <input
            type="text"
            x-model="phone"
            placeholder="08xxx  /  8xxxxxxx  /  +628xxx"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
        />
        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500" x-show="phone.trim()">
            Akan dikirim ke:&nbsp;<span class="font-mono font-semibold text-gray-700 dark:text-gray-200" x-text="normalizePhone(phone)"></span>
        </p>
    </div>

    {{-- Message preview --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Pesan
        </label>
        <textarea
            readonly
            x-text="message"
            rows="9"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 px-3 py-2 text-sm resize-none font-mono leading-relaxed"
        ></textarea>
    </div>

    {{-- Action buttons --}}
    <div class="flex flex-wrap gap-2 pt-1">
        {{-- Copy --}}
        <button
            type="button"
            @click="copyMessage()"
            class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-medium transition
                   bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600
                   text-gray-700 dark:text-gray-200"
        >
            <svg x-show="!copied" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184"/>
            </svg>
            <svg x-show="copied" class="w-4 h-4 shrink-0 text-green-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
            </svg>
            <span x-text="copied ? 'Tersalin!' : 'Salin Pesan'"></span>
        </button>

        {{-- Open WA --}}
        <template x-if="waUrl">
            <a
                :href="waUrl"
                target="_blank"
                rel="noopener noreferrer"
                @click="sent = true; $wire.call('markWaSent', {{ $tamu->id }})"
                class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-medium transition
                       bg-green-500 hover:bg-green-600 text-white shadow-sm"
            >
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                    <path d="M12 0C5.373 0 0 5.373 0 12c0 2.126.556 4.121 1.528 5.855L0 24l6.334-1.509A11.955 11.955 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.885 0-3.652-.518-5.165-1.422l-.371-.22-3.758.895.93-3.666-.242-.38A9.944 9.944 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
                </svg>
                Kirim WhatsApp
            </a>
        </template>
        <template x-if="!waUrl">
            <span class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium
                         bg-green-100 dark:bg-green-900/30 text-green-300 dark:text-green-700 cursor-not-allowed select-none">
                Kirim WhatsApp
            </span>
        </template>

        {{-- Sent badge --}}
        <template x-if="sent">
            <span class="inline-flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-medium
                         bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-700">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                </svg>
                Sudah Dikirim
            </span>
        </template>
    </div>
</div>
