<template>

    <div @click.stop="navigateToStoreHome" class="bg-white space-y-4 py-4 px-4 shadow-sm rounded-xl border border-gray-200 shadow-blue-200 hover:shadow-md hover:border-blue-200 hover:scale-105 transition-all duration-300 cursor-pointer">

        <div class="flex items-end space-x-2 mb-6">

            <StoreLogo @click.stop size="w-10 h-10" :store="store" :showButton="false"></StoreLogo>

            <div class="w-full">
                <h1 class="text-gray-700 font-bold w-4/5 truncate">
                    <span>{{ store.name  }}</span>
                </h1>
                <div class="flex items-center space-x-1">
                    <div :class="['w-2 h-2 rounded-full animate-pulse', store.online ? 'bg-green-500' : 'bg-orange-500']"></div>
                    <span class="text-xs">{{ store.online ? 'Online' : 'Offline'  }}</span>
                </div>
            </div>

        </div>

        <div class="w-full flex space-x-2 justify-end">

            <Button
                size="xs"
                type="light"
                rightIconSize="14"
                :action="visitStore"
                :rightIcon="ExternalLink">
                <span>Visit</span>
            </Button>

            <Button
                size="xs"
                type="primary"
                :action="navigateToStoreHome">
                <span>Manage</span>
            </Button>

        </div>

    </div>

</template>

<script>

    import Button from '@Partials/Button.vue';
    import { ExternalLink } from 'lucide-vue-next';
    import StoreLogo from '@Components/StoreLogo.vue';

    export default {
        components: { Button, StoreLogo },
        props: {
            store: {
                type: Object
            }
        },
        data() {
            return {
                ExternalLink
            }
        },
        methods: {
            visitStore() {
                window.open(this.store.web_link, '_blank');
            },
            navigateToStoreHome() {
                this.$router.push({
                    name: 'show-store-home',
                    params: { 'store_id': this.store.id }
                });
            }
        }
    };

</script>
