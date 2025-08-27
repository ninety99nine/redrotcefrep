<template>

    <div class="pt-24 px-20 mb-40 relative select-none">

        <div class="flex space-x-8">

            <div class="flex-grow space">

                <div class="border-b border-gray-300 rounded-l-md mb-4">

                    <h1 class="text-lg text-gray-700 font-semibold mb-4">Design</h1>

                    <Tabs
                        :tabs="tabs"
                        v-model="tab">
                    </Tabs>

                </div>

                <!-- Content -->
                <router-view
                    @designCards="(updatedDesignCards) => designCards = updatedDesignCards">
                </router-view>

            </div>

            <div class="w-96">

                <div class="scale-75 w-[360px] h-[800px] border-8 border-slate-900 rounded-4xl fixed top-8 right-8 overflow-y-auto shadow-lg shadow-gray-500">

                    <Storefront :designCards="designCards"></Storefront>

                </div>

            </div>

        </div>

    </div>

</template>

<script>

    import Tabs from '@Partials/Tabs.vue';
    import Storefront from '@Pages/storefront/Storefront.vue';

    export default {
        components: { Tabs, Storefront },
        data() {
            return {
                designCards: [],
                tab: 'storefront',
                tabs: [
                    { label: 'Storefront', value: 'storefront'},
                    { label: 'Checkout', value: 'checkout'},
                    { label: 'Payment', value: 'payment'},
                    { label: 'Appearance', value: 'appearance'},
                    { label: 'Menus', value: 'menus'},
                ]
            }
        }
    }
</script>
