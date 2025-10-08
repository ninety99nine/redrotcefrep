<template>

    <div class="animated-border-blue bg-white space-y-4 py-4 px-4 shadow-sm rounded-xl flex flex-col items-center">

        <h1 class="flex items-center space-x-2 text-sm text-gray-700 font-bold">
            <span class="text-xl">🛍️</span>
            <span class="mt-1">Add Porducts</span>
        </h1>

        <p class="text-xs text-center text-gray-600">
            Start by stocking your store with products your customers will love
        </p>

        <Button
            size="xs"
            type="primary"
            :leftIcon="Plus"
            :action="onAddProduct"
            :skeleton="isLoadingStore">
            <span>Add Products</span>
        </Button>

    </div>

</template>

<script>

    import { Plus } from 'lucide-vue-next';
    import Button from '@Partials/Button.vue';

    export default {
        inject: ['storeState'],
        components: {
            Button
        },
        data() {
            return {
                Plus
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            }
        },
        methods: {
            onAddProduct() {
                this.$router.push({
                    name: 'create-product',
                    query: {
                        store_id: this.store.id
                    }
                });
            }
        }
    };

</script>
