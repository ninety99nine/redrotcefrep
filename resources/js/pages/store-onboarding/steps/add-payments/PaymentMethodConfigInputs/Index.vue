<template>

    <div v-if="paymentMethod.active">

        <!-- Custom Name Text Input -->
        <Input
            type="text"
            class="mt-4"
            label="Custom Name"
            v-model="paymentMethod.custom_name"
            v-if="paymentMethod.type == 'other'"
            description="Provide your own custom payment name">
        </Input>

        <div
            :key="index"
            class="mt-4"
            v-for="(configSchemaEntity, index) in paymentMethod.config_schema">

            <template v-if="checkIfPaymentMethodConfigSchemaEntityPassesCondition(configSchemaEntity, paymentMethod.configs)">

                <StringConfig
                    :configSchemaEntity="configSchemaEntity"
                    v-if="configSchemaEntity.type == 'string'"
                    v-model="paymentMethod.configs[configSchemaEntity.attribute]">
                </StringConfig>

                <MobileNumberConfig
                    :configSchemaEntity="configSchemaEntity"
                    v-else-if="configSchemaEntity.type == 'mobile_number'"
                    v-model="paymentMethod.configs[configSchemaEntity.attribute]">
                </MobileNumberConfig>

                <EmailConfig
                    :configSchemaEntity="configSchemaEntity"
                    v-else-if="configSchemaEntity.type == 'email'"
                    v-model="paymentMethod.configs[configSchemaEntity.attribute]">
                </EmailConfig>

                <SelectConfig
                    :configSchemaEntity="configSchemaEntity"
                    v-else-if="configSchemaEntity.type == 'select'"
                    v-model="paymentMethod.configs[configSchemaEntity.attribute]">
                </SelectConfig>

                <CurrencyConfig
                    :paymentMethod="paymentMethod"
                    :configSchemaEntity="configSchemaEntity"
                    v-else-if="configSchemaEntity.type == 'currency'"
                    v-model="paymentMethod.configs[configSchemaEntity.attribute]">
                </CurrencyConfig>

                <ImageConfig
                    :configSchemaEntity="configSchemaEntity"
                    v-else-if="configSchemaEntity.type == 'image'"
                    v-model="paymentMethod.configs[configSchemaEntity.attribute]"
                    :uploadImages="(fileIndex) => uploadImages(paymentMethod, fileIndex)">
                </ImageConfig>

                <ContentConfig
                    :configSchemaEntity="configSchemaEntity"
                    v-else-if="configSchemaEntity.type == 'content'">
                </ContentConfig>

                <!-- Validation Error Message -->
                <p
                    v-if="getPaymentMethodFirstValidationError(configSchemaEntity, paymentMethod.configs)"
                    class="flex space-x-1 text-xs text-yellow-600 font-semibold bg-yellow-100 border border-yellow-300 p-3 rounded-lg shadow-md mt-2">
                    <Info size="14" class="shrink-0"></Info>
                    <span>{{ getPaymentMethodFirstValidationError(configSchemaEntity, paymentMethod.configs) }}</span>
                </p>

            </template>

        </div>

    </div>

</template>

<script>

    import { Info } from 'lucide-vue-next';
    import Input from '@Partials/Input.vue';
    import EmailConfig from '@Pages/settings/payment-methods/_components/PaymentMethodConfigInput/EmailConfig.vue';
    import ImageConfig from '@Pages/settings/payment-methods/_components/PaymentMethodConfigInput/ImageConfig.vue';
    import SelectConfig from '@Pages/settings/payment-methods/_components/PaymentMethodConfigInput/SelectConfig.vue';
    import StringConfig from '@Pages/settings/payment-methods/_components/PaymentMethodConfigInput/StringConfig.vue';
    import ContentConfig from '@Pages/settings/payment-methods/_components/PaymentMethodConfigInput/ContentConfig.vue';
    import CurrencyConfig from '@Pages/settings/payment-methods/_components/PaymentMethodConfigInput/CurrencyConfig.vue';
    import MobileNumberConfig from '@Pages/settings/payment-methods/_components/PaymentMethodConfigInput/MobileNumberConfig.vue';

    export default {
        inject: ['storePaymentMethodState'],
        components: {
            Info, Input, EmailConfig, ImageConfig, SelectConfig,
            StringConfig, ContentConfig, CurrencyConfig, MobileNumberConfig
        },
        props: {
            paymentMethod: {
                type: Object
            },
            paymentMethodIndex: {
                type: Number
            },
            deletePaymentMethodImage: {
                type: Function
            },
            uploadImages: {
                type: Function
            }
        },
        methods: {
            getPaymentMethodFirstValidationError(configSchemaEntity, configs) {
                return this.storePaymentMethodState.getPaymentMethodFirstValidationError(configSchemaEntity, configs);
            },
            checkIfPaymentMethodConfigSchemaEntityPassesCondition(configSchemaEntity, configs) {
                return this.storePaymentMethodState.checkIfPaymentMethodConfigSchemaEntityPassesCondition(configSchemaEntity, configs);
            },
        }
    };
  </script>
