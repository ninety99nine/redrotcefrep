<template>

    <div
        class="bg-white rounded-2xl p-4"
        v-if="designCard.metadata.type === 'countdown' && (designCard.metadata.body || designCard.metadata.date || designCard.photos?.[0]?.path)">

        <div class="space-y-4">

            <Markdown
                v-if="designCard.metadata.upper_text"
                :text="designCard.metadata.upper_text">
            </Markdown>

            <div
                v-if="designCard.photos?.[0]?.path"
                class="w-full aspect-square relative flex items-center justify-center bg-gray-100 rounded-lg overflow-hidden">

                <img
                    alt="Countdown Image"
                    :src="designCard.photos[0].path"
                    class="w-full h-full object-cover" />

            </div>

            <Countdown
                type="design2"
                class="w-full"
                :time="designCard.metadata.date">
                <template #prefix="{ hasExpired }">
                    <span v-if="hasExpired" class="text-lg font-semibold text-red-600">Expired</span>
                </template>
            </Countdown>

            <Markdown
                v-if="designCard.metadata.lower_text"
                :text="designCard.metadata.lower_text">
            </Markdown>

        </div>

    </div>

</template>

<script>

    import Markdown from '@Partials/Markdown.vue';
    import Countdown from '@Partials/Countdown.vue';

    export default {
        components: { Markdown, Countdown },
        props: {
            designCard: {
                type: Object
            }
        }
    }
</script>
