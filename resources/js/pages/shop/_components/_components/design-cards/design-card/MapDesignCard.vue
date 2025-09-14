<template>

    <div
        class="bg-white rounded-2xl p-4"
        v-if="designCard.type === 'map' && (designCard.metadata.upper_text || designCard.metadata.lower_text || designCard.address)">

        <div class="space-y-4">

            <Markdown
                :text="designCard.metadata.upper_text"
                v-if="designCard.metadata.upper_text != null && designCard.metadata.upper_text?.trim() != ''">
            </Markdown>

            <AddressInput
                height="250px"
                triggerClass=""
                :editable="false"
                :address="designCard.address"
                v-if="designCard.address?.latitude && designCard.address?.longitude">
            </AddressInput>

            <p v-if="designCard.metadata.show_address && designCard.address?.complete_address" class="text-gray-600">
                {{ designCard.address.complete_address }}
            </p>

            <Markdown
                :text="designCard.metadata.lower_text"
                v-if="designCard.metadata.lower_text != null && designCard.metadata.lower_text?.trim() != ''">
            </Markdown>

        </div>

    </div>

</template>

<script>

    import Markdown from '@Partials/Markdown.vue';
    import AddressInput from '@Partials/AddressInput.vue';

    export default {
        components: { Markdown, AddressInput },
        props: {
            designCard: {
                type: Object
            }
        }
    }
</script>
