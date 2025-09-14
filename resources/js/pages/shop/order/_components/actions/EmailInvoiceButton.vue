<template>

    <Modal
        size="md"
        class="w-full"
        approveType="primary"
        :leftApproveIcon="Mail"
        ref="emailInvoiceModal"
        :scrollOnContent="false"
        approveText="Send Email"
        :approveAction="emailInvoice">

        <template #trigger="props">

            <Button
                size="md"
                type="light"
                :leftIcon="Mail"
                buttonClass="w-full"
                wrapperClass="w-full"
                :action="props.showModal"
                :skeleton="isLoadingStore || isLoadingOrder">
                <span class="ml-1">Email Invoice</span>
            </Button>

        </template>

        <template #content>

            <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Email Invoice</p>

            <div class="space-y-4 mb-8">

                <!-- Subject -->
                <Input
                    type="text"
                    label="Subject"
                    v-model="subject">
                </Input>

                <!-- Body -->
                <Input
                    rows="8"
                    label="Body"
                    :resize="true"
                    v-model="body"
                    type="textarea">
                </Input>

                <!-- Emails -->
                <SelectTags
                    label="Send to"
                    :showOptions="false"
                    v-model="receipientEmails"
                    placeholder="Add emails to send to" />

                <!-- CC Emails -->
                <SelectTags
                    label="Cc Emails"
                    v-model="ccEmails"
                    :showOptions="false"
                    placeholder="Add emails to cc" />

            </div>

        </template>

    </Modal>

</template>

<script>

    import { Mail } from 'lucide-vue-next';
    import Input from '@Partials/Input.vue';
    import Modal from '@Partials/Modal.vue';
    import Button from '@Partials/Button.vue';
    import SelectTags from '@Partials/SelectTags.vue';

    export default {
        inject: ['orderState', 'storeState', 'notificationState'],
        components: { Input, Modal, Button, SelectTags },
        data() {
            return {
                Mail,
                body: '',
                subject: '',
                ccEmails: [],
                receipientEmails: [],
            }
        },
        watch: {
            order(newValue, oldValue) {
                if(!oldValue && newValue) {
                    this.prepareEmail();
                }
            }
        },
        computed: {
            order() {
                return this.orderState.order;
            },
            store() {
                return this.storeState.store;
            },
            isLoadingOrder() {
                return this.orderState.isLoadingOrder;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            orderPageUrl() {
                return window.location.origin + this.$router.resolve({
                    name: 'show-shop-order',
                    params: {
                        alias: this.store.alias,
                        order_id: this.order.id
                    }
                }).href;
            },
        },
        methods: {
            prepareEmail() {
                this.body = this.generateBody();
                this.subject = this.generateSubject();

                if(this.order.customer_email != null || this.order.customer_email?.trim() != '') {
                    this.receipientEmails.push(this.order.customer_email);
                }
            },
            generateSubject() {
                return `Your Order #${this.order.number} from ${this.store.name}`;
            },
            generateBody() {
                let body = `Dear ${this.order.customer_first_name || this.order.customer_name || 'Customer'},

Thank you for your order! Below are your order details:

Order #${this.order.number}

`;

        // Add order products
        for (let index = 0; index < this.order.order_products.length; index++) {
            const orderProduct = this.order.order_products[index];
            body += `${orderProduct.quantity}x ${orderProduct.name} ${orderProduct.grand_total.amount_with_currency}\n`;
        }

        // Add grand total and quantity
        body += `
Grand Total: ${this.order.grand_total.amount_with_currency} (Qty: ${this.order.total_uncancelled_product_quantities})

`;

        // Add customer details
        const hasMobile = this.order.customer_mobile_number != null;
        const hasName = this.order.customer_name != null && this.order.customer_name.trim() !== '';
        const hasEmail = this.order.customer_email != null && this.order.customer_email.trim() !== '';

        if (hasName) {
            body += `Name: ${this.order.customer_name}\n`;
        }
        if (hasMobile) {
            body += `Mobile: ${this.order.customer_mobile_number.international}\n`;
        }
        if (hasEmail) {
            body += `Email: ${this.order.customer_email}\n`;
        }
        if (hasName || hasMobile || hasEmail) {
            body += `\n`;
        }

        // Add delivery and payment details
        if (this.order.delivery_method != null) {
            body += `Delivery: ${this.order.delivery_method.name}\n`;
        }
        if (this.order.delivery_address != null) {
            body += `Address: ${this.order.delivery_address.complete_address}\n`;
        }
        if (this.order.payment_method != null) {
            body += `Payment: ${this.order.payment_method} (Confirm Payment)\n`;
        }

        body += `
Order Link:
${this.orderPageUrl}
`;

        if (this.store.email || this.store.whatsapp_mobile_number) {
            body += `
If you have any questions, please contact us.

`;
        }

        if (this.store.email) {
            body += `Email: ${this.store.email}
`;
        }

        if (this.store.whatsapp_mobile_number) {
            body += `Whatsapp: ${this.store.whatsapp_mobile_number.international}
`;
        }

        body += `
Best regards,
${this.store.name}`;

                return body;
            },
            emailInvoice() {
                try {
                    this.isEmailingOrder = true;

                    if (this.subject.trim() == '') {
                        this.notificationState.showWarningNotification('Email subject is required');
                        return;
                    }

                    if (this.body.trim() == '') {
                        this.notificationState.showWarningNotification('Email body is required');
                        return;
                    }

                    if (!this.receipientEmails.length) {
                        this.notificationState.showWarningNotification('Email recipient is required');
                        return;
                    }

                    // Email validation regex
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                    // Validate each recipient email
                    for (const email of this.receipientEmails) {
                        if (!emailRegex.test(email)) {
                            this.notificationState.showWarningNotification(`Invalid recipient email: ${email}`);
                            return;
                        }
                    }

                    // Validate each CC email (if any)
                    for (const email of this.ccEmails) {
                        if (!emailRegex.test(email)) {
                            this.notificationState.showWarningNotification(`Invalid CC email: ${email}`);
                            return;
                        }
                    }

                    // Join multiple recipient emails with commas
                    const toEmails = this.receipientEmails.join(',');

                    // Join multiple CC emails with commas
                    const ccEmails = this.ccEmails.length ? this.ccEmails.join(',') : '';

                    // Construct mailto link with to and cc
                    let mailtoLink = `mailto:${toEmails}`;

                    const queryParams = [];

                    if (this.subject) {
                        queryParams.push(`subject=${encodeURIComponent(this.subject)}`);
                    }

                    if (this.body) {
                        queryParams.push(`body=${encodeURIComponent(this.body)}`);
                    }

                    if (ccEmails) {
                        queryParams.push(`cc=${encodeURIComponent(ccEmails)}`);
                    }

                    if (queryParams.length) {
                        mailtoLink += `?${queryParams.join('&')}`;
                    }

                    window.open(mailtoLink, '_blank');
                    this.$refs.emailInvoiceModal.hideModal();
                    this.notificationState.showSuccessNotification('Email client opened with invoice details');
                } catch (error) {
                    const message = error?.message || 'Something went wrong while preparing the email';
                    this.notificationState.showWarningNotification(message);
                    console.error('Failed to prepare email:', error);
                } finally {
                    this.isEmailingOrder = false;
                }
            }
        },
        created() {
            if(this.store && this.order) {
                this.prepareEmail();
            }
        }
    };

</script>
