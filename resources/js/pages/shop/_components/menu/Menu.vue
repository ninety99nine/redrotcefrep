<template>
    <div :style="{ height: '65px' }" class="relative">
        <div :class="[menuClass, 'fixed top-0 left-0 right-0 z-10 boder-b flex items-center justify-between bg-white shadow p-4 mx-auto']">
            <MenuButton></MenuButton>
            <div class="flex items-center justify-between space-x-4">
                <HomeButton v-if="onSearchPage"></HomeButton>
                <SearchButton v-else></SearchButton>
                <CartButton></CartButton>
            </div>
        </div>
    </div>
</template>

<script>
    import HomeButton from '@Pages/shop/_components/menu/_components/HomeButton.vue';
    import CartButton from '@Pages/shop/_components/menu/_components/CartButton.vue';
    import MenuButton from '@Pages/shop/_components/menu/_components/MenuButton.vue';
    import SearchButton from '@Pages/shop/_components/menu/_components/SearchButton.vue';

    export default {
        inject: [],
        props: {
            menuClass: {
                type: String,
                default: 'w-full'
            }
        },
        components: {
            HomeButton, CartButton, MenuButton, SearchButton
        },
        data() {
            return {}
        },
        computed: {
            onSearchPage() {
                return this.$route.name === 'show-search';
            }
        },
        methods: {},
        created() {}
    }
</script>
