<template>

    <div class="animated-border-blue bg-white py-4 px-4 shadow-sm rounded-xl flex flex-col items-center">

        <h1 class="space-x-2 text-sm text-gray-700 font-bold mb-4">
            Store Link
        </h1>

        <!-- Instruction -->
        <p class="text-xs mb-4">Your store is ready to be shared. Copy your store link and share it anywhere you want</p>

        <Copy
            class="w-full mb-4"
            :text="store?.web_link"
            :loading="isLoadingStore"
            :placeholder="`${currentDomain}/...`">
        </Copy>

        <Button
            size="lg"
            class="w-full"
            type="primary"
            rightIconSize="20"
            buttonClass="w-full"
            :rightIcon="Forward"
            :action="visitStore"
            :skeleton="isLoadingStore">
            <span class="text-sm">Visit Store</span>
        </Button>

        <div class="w-full border-t border-dashed border-gray-200 my-6"></div>

        <div class="flex flex-col items-center">

            <h2 class="text-sm text-gray-700 font-semibold mb-2">
                Prefer Your Own Domain?
            </h2>

            <!-- Instruction -->
            <p class="text-xs mb-4">You can buy a domain or connect an existing one</p>

            <Button
                size="xs"
                type="success"
                leftIconSize="16"
                :leftIcon="Earth"
                buttonClass="w-full"
                :skeleton="isLoadingStore"
                :action="navigateToShowDomains">
                <span>Connect Your Own Domain</span>
            </Button>

        </div>

    </div>

</template>

<script>

    import Copy from '@Partials/Copy.vue';
    import Button from '@Partials/Button.vue';
    import { Earth, Forward } from 'lucide-vue-next';

    export default {
        inject: ['storeState'],
        components: {
            Copy, Button
        },
        data() {
            return {
                Earth,
                Forward
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            currentDomain() {
                return typeof window !== "undefined" ? window.location.origin : "";
            }
        },
        methods: {
            visitStore() {
                if(this.isLoadingStore) return;
                window.open(this.store.web_link, '_blank');
            },
            async navigateToShowDomains() {
                await this.$router.push({
                    name: 'show-domains',
                    query: { store_id: this.store.id }
                });
            },
        }
    };

</script>
