<template>

    <div class="pt-24 px-20">

        <h1 class="space-x-2 text-xl text-gray-700 font-semibold mb-8">
            <span>👋</span>
            <span>Welcome back, {{ authUser ? authUser.first_name : '' }}</span>
        </h1>

        <UpgradeBannerCard></UpgradeBannerCard>

        <div class="grid grid-cols-3 gap-4 mb-8">

            <StoreQrCodeCard></StoreQrCodeCard>
            <StoreLinkCard></StoreLinkCard>
            <ShareYourStoreCard></ShareYourStoreCard>

        </div>

        <h1 class="space-x-2 text-xl text-gray-700 font-semibold mb-8">
            <span>🤭</span>
            <span>You need an upgrade</span>
        </h1>

        <div class="grid grid-cols-3 gap-4 mb-8">

            <UpgradeCard></UpgradeCard>
            <GettingStartedCard></GettingStartedCard>
            <TutorialsCard></TutorialsCard>

        </div>

        <Insights v-if="!isLoadingStore" class="mb-8"></Insights>

        <h1 class="space-x-2 text-xl text-gray-700 font-semibold mb-8">
            <span>😎</span>
            <span>Lets get down to business</span>
        </h1>

        <div class="grid grid-cols-3 gap-4 mb-8">

            <AddProductsCard></AddProductsCard>
            <AddCouponsCard></AddCouponsCard>
            <AddTeamCard></AddTeamCard>

        </div>

    </div>

</template>

<script>

    import Insights from '@Pages/stores/store/home/components/Insights.vue';
    import UpgradeCard from '@Pages/stores/store/home/components/UpgradeCard.vue';
    import AddTeamCard from '@Pages/stores/store/home/components/AddTeamCard.vue';
    import StoreLinkCard from '@Pages/stores/store/home/components/StoreLinkCard.vue';
    import TutorialsCard from '@Pages/stores/store/home/components/TutorialsCard.vue';
    import AddCouponsCard from '@Pages/stores/store/home/components/AddCouponsCard.vue';
    import StoreQrCodeCard from '@Pages/stores/store/home/components/StoreQrCodeCard.vue';
    import AddProductsCard from '@Pages/stores/store/home/components/AddProductsCard.vue';
    import UpgradeBannerCard from '@Pages/stores/store/home/components/UpgradeBannerCard.vue';
    import ShareYourStoreCard from '@Pages/stores/store/home/components/ShareYourStoreCard.vue';
    import GettingStartedCard from '@Pages/stores/store/home/components/GettingStartedCard.vue';

    export default {
        inject: ['authState', 'storeState'],
        components: {
            Insights, UpgradeCard, AddTeamCard, StoreLinkCard, TutorialsCard, AddCouponsCard, StoreQrCodeCard,
            AddProductsCard, UpgradeBannerCard, ShareYourStoreCard, GettingStartedCard
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            authUser() {
                return this.authState.user;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            }
        }
    };

</script>
