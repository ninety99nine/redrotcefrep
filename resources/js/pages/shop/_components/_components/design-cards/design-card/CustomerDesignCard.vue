<template>

    <div
        class="bg-white rounded-2xl p-4"
        v-if="designCard.type == 'customer' && (designCard.metadata.title || designCard.metadata.show_first_name || designCard.metadata.show_last_name || designCard.metadata.show_email)">

        <h1
            v-if="designCard.metadata.title"
            class="text-base text-gray-700 font-semibold">
            {{ designCard.metadata.title }}
        </h1>

        <p
            v-if="designCard.metadata.description"
            class="text-sm text-gray-700 mt-2">
            {{ designCard.metadata.description }}
        </p>

        <div class="space-y-2 mt-4">

            <Input
                type="text"
                class="w-full"
                label="First name"
                v-model="orderForm.customer_first_name"
                v-if="designCard.metadata.show_first_name"
                :showAsterisk="designCard.metadata.first_name_required"
                :errorText="formState.getFormError('customer_first_name')"
                :secondaryLabel="designCard.metadata.first_name_required ? null : '(optional)'">
            </Input>

            <Input
                type="text"
                class="w-full"
                label="Last name"
                v-model="orderForm.customer_last_name"
                v-if="designCard.metadata.show_last_name"
                :showAsterisk="designCard.metadata.last_name_required"
                :errorText="formState.getFormError('customer_last_name')"
                :secondaryLabel="designCard.metadata.last_name_required ? null : '(optional)'">
            </Input>

            <Input
                type="text"
                class="w-full"
                label="Mobile"
                placeholder="+26772000001"
                v-model="orderForm.customer_mobile_number"
                v-if="designCard.metadata.show_mobile_number"
                :showAsterisk="designCard.metadata.mobile_number_required"
                :errorText="formState.getFormError('customer_mobile_number')"
                :secondaryLabel="designCard.metadata.mobile_number_required  ? null : '(optional)'">
            </Input>

            <Input
                type="email"
                label="Email"
                class="w-full"
                placeholder="johndoe@example.com"
                v-model="orderForm.customer_email"
                v-if="designCard.metadata.show_email"
                :showAsterisk="designCard.metadata.email_required"
                :errorText="formState.getFormError('customer_email')"
                :secondaryLabel="designCard.metadata.email_required ? null : '(optional)'">
            </Input>

        </div>

    </div>

</template>

<script>

    import Input from '@Partials/Input.vue';

    export default {
        inject: ['formState', 'orderState'],
        components: { Input },
        props: {
            designCard: {
                type: Object
            }
        },
        computed: {
            orderForm() {
                return this.orderState.orderForm;
            }
        }
    }
</script>
