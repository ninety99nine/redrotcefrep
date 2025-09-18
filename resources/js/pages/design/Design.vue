<template>
    <div class="pt-24 px-20 mb-40 relative select-none">

        <div class="flex space-x-8">

            <div class="w-2xl">

                <div class="border-b border-gray-300 rounded-l-md mb-4">

                    <h1 class="text-lg text-gray-700 font-semibold mb-4">Design</h1>

                    <div class="flex items-center justify-between space-x-8">

                        <Tabs
                            :tabs="tabs"
                            v-model="tab"
                            @change="onTabChange">
                        </Tabs>

                        <Button
                            size="xs"
                            type="light"
                            :action="openWebLink"
                            :leftIcon="ExternalLink">
                            <span class="ml-1">Preview</span>
                        </Button>

                    </div>

                </div>

                <!-- Content -->
                <router-view></router-view>

            </div>

            <div class="flex-grow">
                <div class="mockup-phone scale-75 overflow-hidden border-8 border-slate-900 rounded-4xl fixed top-8 right-8 shadow-lg shadow-gray-500">
                    <div class="w-[360px] h-[800px] overflow-y-auto text-gray-700">
                        <router-view name="preview"></router-view>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
    import Tabs from '@Partials/Tabs.vue';
    import Button from '@Partials/Button.vue';
    import { ExternalLink } from 'lucide-vue-next';

    export default {
        inject: ['orderState', 'storeState'],
        components: { Tabs, Button },
        data() {
            return {
                ExternalLink,
                tab: this.$route.name,
                tabs: [
                    { label: 'Storefront', value: 'edit-storefront'},
                    { label: 'Checkout', value: 'edit-checkout'},
                    { label: 'Payment', value: 'edit-payment'},
                    { label: 'Menu', value: 'edit-menu'},
                    { label: 'Appearance', value: 'edit-appearance'},
                ]
            }
        },
        watch: {
            store(newValue) {
                if(newValue) {
                    this.setOrderForm();
                }
            },
            '$route.name'(newName) {
                this.tab = newName;
            }
        },
        computed: {
            store() {
                return this.storeState.store;
            }
        },
        methods: {
            openWebLink() {
                if (this.store.web_link) {
                    window.open(`${this.store.web_link}?mode=preview`, '_blank');
                }
            },
            setOrderForm() {
                this.orderState.setOrderForm(null);
            },
            onTabChange(tabName) {
                this.$router.push({
                    name: tabName,
                    query: {
                        store_id: this.store.id
                    }
                 });
            }
        },
        created() {
            if(this.store) {
                this.setOrderForm();
            }
        }
    }
</script>
