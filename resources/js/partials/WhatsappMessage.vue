<template>
    <div ref="chatContainer" class="bg-[#efe9e0] rounded-lg p-4 space-y-4 overflow-y-auto pb-8">
        <div v-for="(msg, index) in displayedMessages" :key="index"
             :class="['flex mb-4 animate-slide-in', msg.isOwnMessage ? 'justify-end' : 'justify-start']">
            <div :class="[
                'p-3 rounded-lg max-w-xs shadow-md relative',
                msg.isOwnMessage ? 'bg-[#d0fecf]' : 'bg-white'
            ]">
                <!-- Sender name (for group chat, non-own messages) -->
                <div v-if="!msg.isOwnMessage" class="text-xs font-semibold text-gray-600">{{ msg.sender }}</div>
                <!-- Render message with markdown formatting -->
                <div class="text-sm text-gray-700 pb-4 break-words whitespace-pre-wrap" v-html="formatMessage(msg.text)"></div>
                <!-- Timestamp and read receipt -->
                <div class="absolute bottom-1 right-2 flex items-center space-x-1">
                    <span class="text-xs text-gray-500">{{ msg.timestamp }}</span>
                    <svg v-if="msg.isOwnMessage" class="w-5 h-5 text-blue-500 flex-shrink-0" viewBox="0 -0.5 25 25" fill="currentColor">
                        <path d="M5.03033 11.4697C4.73744 11.1768 4.26256 11.1768 3.96967 11.4697C3.67678 11.7626 3.67678 12.2374 3.96967 12.5303L5.03033 11.4697ZM8.5 16L7.96967 16.5303C8.26256 16.8232 8.73744 16.8232 9.03033 16.5303L8.5 16ZM17.0303 8.53033C17.3232 8.23744 17.3232 7.76256 17.0303 7.46967C16.7374 7.17678 16.2626 7.17678 15.9697 7.46967L17.0303 8.53033ZM9.03033 11.4697C8.73744 11.1768 8.26256 11.1768 7.96967 11.4697C7.67678 11.7626 7.67678 12.2374 7.96967 12.5303L9.03033 11.4697ZM12.5 16L11.9697 16.5303C12.2626 16.8232 12.7374 16.8232 13.0303 16.5303L12.5 16ZM21.0303 8.53033C21.3232 8.23744 21.3232 7.76256 21.0303 7.46967C20.7374 7.17678 20.2626 7.17678 19.9697 7.46967L21.03033 8.53033ZM3.96967 12.5303L7.96967 16.5303L9.03033 15.4697L5.03033 11.4697L3.96967 12.5303ZM9.03033 16.5303L17.0303 8.53033L15.9697 7.46967L7.96967 15.4697L9.03033 16.5303ZM7.96967 12.5303L11.9697 16.5303L13.0303 15.4697L9.03033 11.4697L7.96967 12.5303ZM13.0303 16.5303L21.0303 8.53033L19.9697 7.46967L11.9697 15.4697L13.0303 16.5303Z" fill="#027bfc"/>
                    </svg>
                </div>
            </div>
        </div>
        <!-- Typing indicator -->
        <div v-show="isTyping" class="flex items-center space-x-2 p-3 bg-[#efe9e0] rounded-lg">
            <span class="text-sm text-gray-500">{{ typingSender }} typing</span>
            <div class="flex space-x-1">
                <div class="w-1 h-1 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0s"></div>
                <div class="w-1 h-1 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                <div class="w-1 h-1 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        messages: {
            type: Array,
            default: () => [],
            validator: (messages) => messages.every(msg =>
                typeof msg === 'object' &&
                'text' in msg &&
                typeof msg.text === 'string' &&
                'sender' in msg &&
                typeof msg.sender === 'string' &&
                'timestamp' in msg &&
                typeof msg.timestamp === 'string' &&
                'isOwnMessage' in msg &&
                typeof msg.isOwnMessage === 'boolean'
            )
        },
        animate: {
            type: Boolean,
            default: false
        },
        loopAnimation: {
            type: Boolean,
            default: false
        },
        delayBeforeLoop: {
            type: Number,
            default: 5
        },
        typingDuration: {
            type: Number,
            default: 2000
        },
        replyDelay: {
            type: Number,
            default: 1500
        },
        scrollDuration: {
            type: Number,
            default: 1000 // Default scroll duration in milliseconds
        }
    },
    data() {
        return {
            displayedMessages: [],
            isTyping: false,
            typingSender: '',
            currentMessageIndex: 0,
            scrollTimeout: null
        };
    },
    watch: {
        messages: {
            handler(newMessages) {
                if (this.animate) {
                    this.resetAnimation();
                    this.animateMessages();
                } else {
                    this.displayedMessages = newMessages;
                    this.isTyping = false;
                    this.$nextTick(() => this.scrollToBottom());
                }
            },
            immediate: true,
            deep: true
        }
    },
    methods: {
        formatMessage(text) {
            if (!text) return '';

            return text
                .replace(/\n/g, '<br>') // Preserve line breaks
                .replace(/\*(.*?)\*/g, '<strong>$1</strong>') // Bold
                .replace(/_(.*?)_/g, '<em>$1</em>') // Italic
                .replace(/`(.*?)`/g, '<span class="text-xs font-mono p-1 rounded break-all" style="background:#cff4cd;">$1</span>') // Monospace
                .replace(/\[([^\]]+)\]\(([^\)]+)\)/g, '<a href="$2" target="_blank" class="text-blue-500 underline">$1</a>'); // Links
        },
        resetAnimation() {
            this.displayedMessages = [];
            this.currentMessageIndex = 0;
            this.isTyping = false;
            this.typingSender = '';
            if (this.scrollTimeout) {
                clearTimeout(this.scrollTimeout);
                this.scrollTimeout = null;
            }
            this.$nextTick(() => this.scrollToTop());
        },
        smoothScrollTo(element, targetScrollTop, duration) {
            const startScrollTop = element.scrollTop;
            const distance = targetScrollTop - startScrollTop;
            const startTime = performance.now();

            const easeInOutQuad = (t) => t < 0.5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2;

            const scroll = (currentTime) => {
                const elapsedTime = currentTime - startTime;
                const progress = Math.min(elapsedTime / duration, 1);
                const easedProgress = easeInOutQuad(progress);
                element.scrollTop = startScrollTop + distance * easedProgress;

                if (progress < 1) {
                    requestAnimationFrame(scroll);
                }
            };

            requestAnimationFrame(scroll);
        },
        scrollToBottom() {
            if (this.$refs.chatContainer && !this.scrollTimeout) {
                const targetScrollTop = this.$refs.chatContainer.scrollHeight - this.$refs.chatContainer.clientHeight;
                this.smoothScrollTo(this.$refs.chatContainer, targetScrollTop, this.scrollDuration);
            }
        },
        scrollToTop() {
            if (this.$refs.chatContainer) {
                this.smoothScrollTo(this.$refs.chatContainer, 0, this.scrollDuration);
            }
        },
        animateMessages() {
            if (!this.animate || this.currentMessageIndex >= this.messages.length) {
                this.isTyping = false;
                if (this.loopAnimation && this.animate) {
                    setTimeout(() => {
                        this.resetAnimation();
                        this.animateMessages();
                    }, this.delayBeforeLoop * 1000);
                }
                return;
            }

            const message = this.messages[this.currentMessageIndex];
            this.isTyping = true;
            this.typingSender = message.isOwnMessage ? 'you are' : `${message.sender} is`;

            // Single scroll to bottom after typing starts
            this.$nextTick(() => this.scrollToBottom());

            const typingDuration = this.typingDuration < this.scrollDuration ? this.scrollDuration : this.typingDuration;

            setTimeout(() => {

                this.displayedMessages.push(message);
                this.isTyping = false;
                this.currentMessageIndex++;

                // Single scroll to bottom after typing starts
                this.$nextTick(() => this.scrollToBottom());

                const replyDelay = this.replyDelay < this.scrollDuration ? this.scrollDuration : this.replyDelay;

                setTimeout(() => this.animateMessages(), replyDelay); // Delay between messages

            }, typingDuration);
        }
    },
    beforeDestroy() {
        if (this.scrollTimeout) {
            clearTimeout(this.scrollTimeout);
        }
        this.resetAnimation();
    }
};
</script>

<style scoped>
.animate-slide-in {
    animation: slideIn 0.5s ease-in-out;
}
@keyframes slideIn {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}
.animate-bounce {
    animation: bounce 0.75s infinite;
}
@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-5px);
    }
}
</style>
