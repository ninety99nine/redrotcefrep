<template>
    <div class="flex justify-end bg-[#efe9e0] rounded-lg p-4 mb-8">
        <div class="bg-[#d0fecf] p-3 rounded-lg max-w-xs shadow-md relative">
            <!-- Render message with markdown formatting -->
            <div class="text-sm text-gray-700 pb-4 break-words whitespace-pre-wrap" v-html="formattedMessage"></div>

            <div class="absolute bottom-1 right-2 flex items-center space-x-1">
                <span class="text-xs text-gray-500">{{ new Date().toLocaleTimeString().slice(0, 5) }}</span>
                <svg class="w-5 h-5 text-blue-500 flex-shrink-0" viewBox="0 -0.5 25 25" fill="currentColor">
                    <path d="M5.03033 11.4697C4.73744 11.1768 4.26256 11.1768 3.96967 11.4697C3.67678 11.7626 3.67678 12.2374 3.96967 12.5303L5.03033 11.4697ZM8.5 16L7.96967 16.5303C8.26256 16.8232 8.73744 16.8232 9.03033 16.5303L8.5 16ZM17.0303 8.53033C17.3232 8.23744 17.3232 7.76256 17.0303 7.46967C16.7374 7.17678 16.2626 7.17678 15.9697 7.46967L17.0303 8.53033ZM9.03033 11.4697C8.73744 11.1768 8.26256 11.1768 7.96967 11.4697C7.67678 11.7626 7.67678 12.2374 7.96967 12.5303L9.03033 11.4697ZM12.5 16L11.9697 16.5303C12.2626 16.8232 12.7374 16.8232 13.0303 16.5303L12.5 16ZM21.0303 8.53033C21.3232 8.23744 21.3232 7.76256 21.0303 7.46967C20.7374 7.17678 20.2626 7.17678 19.9697 7.46967L21.0303 8.53033ZM3.96967 12.5303L7.96967 16.5303L9.03033 15.4697L5.03033 11.4697L3.96967 12.5303ZM9.03033 16.5303L17.0303 8.53033L15.9697 7.46967L7.96967 15.4697L9.03033 16.5303ZM7.96967 12.5303L11.9697 16.5303L13.0303 15.4697L9.03033 11.4697L7.96967 12.5303ZM13.0303 16.5303L21.0303 8.53033L19.9697 7.46967L11.9697 15.4697L13.0303 16.5303Z" fill="#027bfc"/>
                </svg>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        message: {
            type: String,
            default: ''
        }
    },
    computed: {
        formattedMessage() {
            if (!this.message) return '';

            return this.message
                .replace(/\n/g, '<br>') // Preserve line breaks
                .replace(/\*(.*?)\*/g, '<strong>$1</strong>') // Bold
                .replace(/_(.*?)_/g, '<em>$1</em>') // Italic
                .replace(/`(.*?)`/g, '<span class="text-xs font-mono p-1 rounded break-all" style="background:#cff4cd;">$1</span>'); // Monospace with word breaking
        }
    }
};
</script>
