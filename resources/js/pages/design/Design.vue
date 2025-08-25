<template>

    <div class="pt-24 px-8 mb-40 relative select-none">

        <div class="flex justify-between space-x-4">

            <div class="flex-grow">

                <div class="border-b border-gray-300 pb-4 mb-4">

                    <h1 class="text-lg text-gray-700 font-semibold mb-4">Design</h1>

                    <Tabs
                        :tabs="tabs"
                        v-model="tab">
                    </Tabs>

                </div>

                <!-- Content -->
                <router-view></router-view>

            </div>

            <div class="w-[360px] h-[800px] border-8 border-slate-900 rounded-4xl relative overflow-y-auto shadow-lg shadow-gray-500">


            </div>

        </div>

    </div>

</template>

<script>
    import Tabs from '@Partials/tabs.vue';

    export default {
        components: { Tabs },
        data() {
            return {
                tab: 'storefront',
                tabs: [
                    { label: 'Storefront', value: 'storefront'},
                    { label: 'Checkout', value: 'checkout'},
                    { label: 'Appearance', value: 'appearance'},
                    { label: 'Menus', value: 'menus'},
                ]
            }
        }
    }
</script>
