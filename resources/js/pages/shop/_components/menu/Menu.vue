<template>
    <div :style="{ height: '52px' }" class="relative">
        <div class="fixed top-0 left-0 right-0 z-10 boder-b flex items-center justify-between space-x-2 bg-white shadow px-6 py-2.5 mx-auto">
            <MenuButton></MenuButton>
            <div class="flex items-center justify-between space-x-4">
                <LoginButton></LoginButton>
                <HomeButton v-if="!onStorefront && !onSearchPage"></HomeButton>
                <SearchButton v-if="!onStorefront && !onSearchPage"></SearchButton>
                <CartButton></CartButton>
            </div>
        </div>
    </div>
</template>

<script>
    import HomeButton from '@Pages/shop/_components/menu/_components/HomeButton.vue';
    import CartButton from '@Pages/shop/_components/menu/_components/CartButton.vue';
    import MenuButton from '@Pages/shop/_components/menu/_components/MenuButton.vue';
    import LoginButton from '@Pages/shop/_components/menu/_components/LoginButton.vue';
    import SearchButton from '@Pages/shop/_components/menu/_components/SearchButton.vue';

    export default {
        components: {
            HomeButton, CartButton, MenuButton, LoginButton, SearchButton
        },
        data() {
            return {}
        },
        computed: {
            onStorefront() {
                return ['show-storefront', 'edit-storefront'].includes(this.$route.name);
            },
            onSearchPage() {
                return this.$route.name === 'show-search';
            }
        },
        methods: {},
        created() {}
    }
</script>
