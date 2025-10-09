<template>
    <div :style="{ height: '65px' }" class="relative">
        <div :class="[menuClass, 'fixed top-0 left-0 right-0 z-10 boder-b flex items-center justify-between bg-white shadow p-4 mx-auto']">
            <MenuButton></MenuButton>
            <div class="flex items-center justify-between space-x-4">
                <LoginButton v-if="onStorefront"></LoginButton>
                <HomeButton v-if="!onStorefront"></HomeButton>
                <SearchButton v-if="!onSearchPage"></SearchButton>
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
        inject: [],
        props: {
            menuClass: {
                type: String,
                default: 'w-full'
            }
        },
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
