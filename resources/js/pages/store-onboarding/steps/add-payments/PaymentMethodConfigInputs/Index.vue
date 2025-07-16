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
                    :paymentMethod="paymentMethod"
                    :configSchemaEntity="configSchemaEntity"
                    v-else-if="configSchemaEntity.type == 'image'"
                    :deletePaymentMethodImage="deletePaymentMethodImage"
                    v-model="paymentMethod.configs[configSchemaEntity.attribute]"
                    :uploadSinglePaymentMethodImage="uploadSinglePaymentMethodImage"
                    :getPaymentMethodValidationErrors="getPaymentMethodValidationErrors">
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
    import EmailConfig from '@Pages/store-onboarding/steps/add-payments/PaymentMethodConfigInputs/PaymentMethodConfigInput/EmailConfig.vue';
    import ImageConfig from '@Pages/store-onboarding/steps/add-payments/PaymentMethodConfigInputs/PaymentMethodConfigInput/ImageConfig.vue';
    import SelectConfig from '@Pages/store-onboarding/steps/add-payments/PaymentMethodConfigInputs/PaymentMethodConfigInput/SelectConfig.vue';
    import StringConfig from '@Pages/store-onboarding/steps/add-payments/PaymentMethodConfigInputs/PaymentMethodConfigInput/StringConfig.vue';
    import ContentConfig from '@Pages/store-onboarding/steps/add-payments/PaymentMethodConfigInputs/PaymentMethodConfigInput/ContentConfig.vue';
    import CurrencyConfig from '@Pages/store-onboarding/steps/add-payments/PaymentMethodConfigInputs/PaymentMethodConfigInput/CurrencyConfig.vue';
    import MobileNumberConfig from '@Pages/store-onboarding/steps/add-payments/PaymentMethodConfigInputs/PaymentMethodConfigInput/MobileNumberConfig.vue';

    export default {
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
            uploadSinglePaymentMethodImage: {
                type: Function
            },
            getPaymentMethodValidationErrors: {
                type: Function
            },
            getPaymentMethodFirstValidationError: {
                type: Function
            },
            checkIfPaymentMethodConfigSchemaEntityPassesCondition: {
                type: Function
            }
        }
    };
  </script>
