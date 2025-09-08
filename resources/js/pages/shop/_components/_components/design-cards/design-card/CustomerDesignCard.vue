<template>

    <div
        class="bg-white rounded-2xl p-4 space-y-4"
        v-if="designCard.metadata.type == 'customer' && (designCard.metadata.title || designCard.metadata.show_first_name || designCard.metadata.show_last_name || designCard.metadata.show_email)">

        <h1 class="text-base text-gray-700 font-semibold mb-4">{{ designCard.metadata.title }}</h1>

        <div class="space-y-2">

            <Input
                type="text"
                class="w-full"
                label="First name"
                v-model="firstName"
                v-if="designCard.metadata.show_first_name"
                :showAsterisk="designCard.metadata.first_name_required">
            </Input>

            <Input
                type="text"
                class="w-full"
                label="Last name"
                v-model="lastName"
                v-if="designCard.metadata.show_last_name"
                :showAsterisk="designCard.metadata.last_name_required">
            </Input>

            <Input
                type="text"
                class="w-full"
                label="Mobile"
                v-model="mobileNumber"
                placeholder="+26772000001"
                v-if="designCard.metadata.show_mobile_number"
                :showAsterisk="designCard.metadata.mobile_number_required">
            </Input>


            <Input
                type="email"
                label="Email"
                class="w-full"
                v-model="email"
                placeholder="johndoe@example.com"
                v-if="designCard.metadata.show_email"
                :showAsterisk="designCard.metadata.email_required">
            </Input>

        </div>

    </div>

</template>

<script>

    import Input from '@Partials/Input.vue';

    export default {
        components: { Input },
        props: {
            designCard: {
                type: Object
            }
        },
        data() {
            return {
                email: '',
                lastName: '',
                firstName: '',
                mobileNumber: '',
            }
        }
    }
</script>
