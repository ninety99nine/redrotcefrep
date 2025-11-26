<template>

    <div class="bg-white p-8 shadow-sm rounded-xl mb-2">

        <h1 class="text-lg font-bold mb-8">General Settings</h1>

        <div class="space-y-4">

            <!-- Online Toggle Switch -->
            <Skeleton v-if="isLoadingStore || !store" width="w-8" :shine="true"></Skeleton>
            <Switch
                v-else
                size="xs"
                suffixText="Online"
                v-model="storeForm.online"
                :errorText="formState.getFormError('online')"
                @change="storeState.saveStateDebounced('Online status changed')"
                tooltipContent="Turn on to set your store online (visible to customers). Turn off to set it offline (hidden from customers)."
            />

            <!-- Offline Message Textarea Input -->
            <Input
                rows="2"
                type="textarea"
                label="Offline Message"
                v-if="!storeForm.online"
                v-model="storeForm.offline_message"
                :skeleton="isLoadingStore || !store"
                placeholder="Closed for the holidays"
                :errorText="formState.getFormError('offline_message')"
                @change="storeState.saveStateDebounced('Offline message changed')"
                tooltipContent="The message to show to customers who visit your store while its offline (hidden from customers)">
            </Input>

            <!-- Name Text Input -->
            <Input
                type="text"
                label="Store Name"
                v-model="storeForm.name"
                placeholder="Baby Cakes 🧁"
                :skeleton="isLoadingStore || !store"
                :errorText="formState.getFormError('name')"
                @change="storeState.saveStateDebounced('Store name changed')">
            </Input>

            <!-- Description Textarea Input -->
            <Input
                rows="2"
                type="textarea"
                label="Store Description"
                v-model="storeForm.description"
                :skeleton="isLoadingStore || !store"
                placeholder="The sweetest cakes in the world 🍰"
                :errorText="formState.getFormError('description')"
                @change="storeState.saveStateDebounced('Store description changed')"
                tooltipContent="A short and sweet description of your store e.g The sweetest cakes in the world 🍰">
            </Input>

            <StoreLogo @click.stop size="w-24 h-24" :showButton="true"></StoreLogo>

        </div>

    </div>

</template>

<script>

    import Input from '@Partials/Input.vue';
    import Switch from '@Partials/Switch.vue';
    import Skeleton from '@Partials/Skeleton.vue';
    import StoreLogo from '@Components/StoreLogo.vue';

    export default {
        inject: ['formState', 'storeState'],
        components: {
            Input, Switch, Skeleton, StoreLogo
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            storeForm() {
                return this.storeState.storeForm;
            }
        }
    };

</script>
