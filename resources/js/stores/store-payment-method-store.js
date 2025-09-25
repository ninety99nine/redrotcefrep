import { defineStore } from 'pinia';
import { useChangeHistoryStore as changeHistoryState } from '@Stores/change-history-store.js';

export const useStorePaymentMethodStore = defineStore('storePaymentMethod', {
    state: () => ({
        isUploading: false,
        paymentMethod: null,
        storePaymentMethod: null,
        storePaymentMethodForm: null,
        isLoadingStorePaymentMethod: false,
        isCreatingStorePaymentMethod: false,
        isUpdatingStorePaymentMethod: false,
        isDeletingStorePaymentMethod: false,
    }),
    actions: {
        reset() {
            this.isUploading = false;
            this.paymentMethod = null;
            this.storePaymentMethod = null;
            this.storePaymentMethodForm = null;
            this.isLoadingStorePaymentMethod = false;
            this.isCreatingStorePaymentMethod = false;
            this.isUpdatingStorePaymentMethod = false;
            this.isDeletingStorePaymentMethod = false;
            changeHistoryState().reset();
        },
        saveState(actionName) {
            changeHistoryState().saveState(actionName, this.storePaymentMethodForm);
        },
        saveStateDebounced(actionName) {
            changeHistoryState().saveStateDebounced(actionName, this.storePaymentMethodForm);
        },
        saveOriginalState(actionName) {
            changeHistoryState().saveOriginalState(actionName, this.storePaymentMethodForm);
        },
        setPaymentMethod(paymentMethod) {
            this.paymentMethod = paymentMethod;
        },
        setStorePaymentMethod(storePaymentMethod) {
            this.storePaymentMethod = storePaymentMethod;
        },
        setStorePaymentMethodForm(storePaymentMethod = null, paymentMethod = null, saveState = true) {

            // Initialize storePaymentMethodForm with defaults
            this.storePaymentMethodForm = {
                configs: storePaymentMethod?.configs ?? {},
                active: storePaymentMethod?.active ?? true,
                instruction: storePaymentMethod?.instruction ?? null,
                custom_name: storePaymentMethod?.custom_name ?? paymentMethod?.name ?? '',
                require_proof_of_payment: storePaymentMethod?.require_proof_of_payment ?? false,
                enable_contact_seller_before_payment: storePaymentMethod?.enable_contact_seller_before_payment ?? true,
                mark_as_paid_on_customer_confirmation: storePaymentMethod?.mark_as_paid_on_customer_confirmation ?? false,
            };

            // Map config_schema to configs object
            if (paymentMethod?.config_schema) {
                this.storePaymentMethodForm.configs = paymentMethod.config_schema.reduce((configs, configSchema) => {
                    let value;

                    // Handle image types for logo and photo
                    if (configSchema.type === 'image' && configSchema.attribute === 'logo') {
                        value = storePaymentMethod?.logo ? [storePaymentMethod.logo] : [];
                    } else if (configSchema.type === 'image' && configSchema.attribute === 'photo') {
                        value = storePaymentMethod?.photo ? [storePaymentMethod.photo] : [];
                    } else {
                        // For other types, use existing config value or null
                        value = storePaymentMethod?.configs?.[configSchema.attribute] ?? null;
                    }

                    return {
                        ...configs,
                        [configSchema.attribute]: value
                    };
                }, {});
            }

            if(saveState) {
                this.saveOriginalState('Original payment method');
            }

        },
        getPaymentMethodFirstValidationError(configSchemaEntity, configs) {
            const errors = this.getPaymentMethodValidationErrors(configSchemaEntity, configs);
            return errors.length ? errors[0] : null;
        },
        getPaymentMethodValidationErrors(configSchemaEntity, configs) {
            let errors = [];

            // Ensure validation rules exist
            if (!configSchemaEntity.hasOwnProperty('validation_rules')) {
                return errors;
            }

            const value = configs[configSchemaEntity.attribute];

            // Check for required validation
            if (configSchemaEntity.validation_rules.hasOwnProperty('required')) {
                const [isRequired, message] = configSchemaEntity.validation_rules.required;

                if (isRequired) {
                    if (
                        value === null ||
                        value === undefined ||
                        (typeof value === "string" && value.trim() === "") ||
                        (typeof value === "object" && Object.keys(value).length === 0) ||
                        (Array.isArray(value) && value.length === 0)
                    ) {
                        errors.push(message);
                    }
                }
            }

            // Check for regex pattern validation (only applicable for string values)
            if (configSchemaEntity.validation_rules.hasOwnProperty('regex_pattern')) {
                const [pattern, message] = configSchemaEntity.validation_rules.regex_pattern;

                if (typeof value === "string") {
                    try {
                        const regex = new RegExp(pattern);
                        if (!regex.test(value.trim())) {
                            errors.push(message);
                        }
                    } catch (error) {
                        console.error(`Invalid regex pattern: ${pattern}`);
                    }
                }
            }

            // Check for QR Code validation
            if (configSchemaEntity.validation_rules.hasOwnProperty('qr_code')) {

                const [message] = configSchemaEntity.validation_rules.qr_code;

                if (value?.path?.startsWith('blob:') && value?.valid_qr === false) {
                    errors.push(message);
                }
            }

            // Check for MIME Type validation
            if (configSchemaEntity.validation_rules.hasOwnProperty('mime_types')) {
                const [allowedmime_types, message] = configSchemaEntity.validation_rules.mime_types;

                if (value && value.file_ref) {
                    const uploadedFileType = value.file_ref.type;

                    if (!allowedmime_types.includes(uploadedFileType)) {
                        errors.push(message);
                    }
                }
            }

            // Check for max_size validation
            if (configSchemaEntity.validation_rules.hasOwnProperty('max_size')) {

                const [max_size, message] = configSchemaEntity.validation_rules.max_size;

                if (value?.file_ref && value.file_ref.size > max_size) {
                    errors.push(message);
                }
            }

            return errors;
        },
        checkIfPaymentMethodConfigSchemaEntityPassesCondition(configSchemaEntity, configs) {

            if (!configSchemaEntity.hasOwnProperty('condition')) {
                // No condition means it's always valid
                return true;
            }

            return configSchemaEntity.condition.every(condition => {

                // Ensure condition follows 'attribute=value' or 'attribute!=value' format
                const match = condition.match(/^([^!=]+)(!=|=)(.+)$/);

                if (!match) {
                    console.log(`Invalid condition format: ${condition}`);
                    return false;
                }

                const [, attribute, operator, expectedValue] = match.map(str => str.trim());

                // Check if attribute exists in the configs
                if (!configs.hasOwnProperty(attribute)) {
                    return false;
                }

                // Handle equality and inequality conditions
                if (operator === '=') {
                    return configs[attribute] === expectedValue;
                } else if (operator === '!=') {
                    return configs[attribute] !== expectedValue;
                }

                return false;
            });
        },
    },
});
