import { useUiStore } from "@Stores/ui-store.js";
import { useAuthStore } from "@Stores/auth-store.js";
import { createRouter, createWebHistory } from 'vue-router';

const routes = [
    {
        path: '/',
        redirect: '/auth/login',
    },
    {
        path: '/auth',
        component: () => import('@Layouts/auth/Auth.vue'),
        children: [
            {
                path: 'login',
                name: 'login',
                component: () => import('@Pages/auth/Login.vue'),
                props: true
            },
            {
                path: 'register',
                name: 'register',
                component: () => import('@Pages/auth/Register.vue'),
                props: true
            },
            {
                path: 'setup-account',
                name: 'setup-account',
                component: () => import('@Pages/auth/SetupAccount.vue'),
            },
            {
                path: 'forgot-password',
                name: 'forgot-password',
                component: () => import('@Pages/auth/ForgotPassword.vue'),
            },
            {
                path: 'reset-password',
                name: 'reset-password',
                component: () => import('@Pages/auth/ResetPassword.vue'),
            },
            {
                path: 'social-login',
                name: 'social-login',
                component: () => import('@Pages/auth/SocialLogin.vue'),
                props: true
            },
        ]
    },
    {
        path: '/dashboard',
        component: () => import('@Layouts/dashboard/Dashboard.vue'),
        meta: { requiresAuth: true },
        props: true,
        children: [
            {
                name: 'show-stores',
                path: 'stores',
                component: () => import('@Pages/stores/Stores.vue')
            },
            {
                path: 'store-onboarding',
                meta: { onboarding: true },
                component: () => import('@Pages/store-onboarding/StoreOnboarding.vue'),
                children: [
                    {
                        path: 'create/store',
                        name: 'create-store',
                        component: () => import('@Pages/store-onboarding/steps/create-store/CreateStore.vue'),
                    },
                    {
                        path: ':store_id',
                        children: [
                            {
                                path: 'add/products',
                                name: 'add-products',
                                component: () => import('@Pages/store-onboarding/steps/add-products/AddProducts.vue'),
                            },
                            {
                                path: 'add/payments',
                                name: 'add-payments',
                                component: () => import('@Pages/store-onboarding/steps/add-payments/AddPayments.vue'),
                            },
                            {
                                path: 'add/socials',
                                name: 'add-socials',
                                component: () => import('@Pages/store-onboarding/steps/add-socials/AddSocials.vue'),
                            },
                            {
                                path: 'add/pages',
                                name: 'add-pages',
                                component: () => import('@Pages/store-onboarding/steps/add-pages/AddPages.vue'),
                            },
                            {
                                path: 'advanced/features',
                                name: 'advanced-features',
                                component: () => import('@Pages/store-onboarding/steps/add-advanced-features/AddAdvancedFeatures.vue')
                            },
                            {
                                path: 'share',
                                name: 'share',
                                component: () => import('@Pages/store-onboarding/steps/share/Share.vue')
                            }
                        ]
                    }
                ]
            },
            {
                path: 'stores/:store_id',
                children: [
                    {
                        path: '',
                        name: 'show-store-home',
                        component: () => import('@Pages/stores/store/home/StoreHome.vue')
                    },
                ]
            },
            {
                path: 'orders',
                children: [
                    {
                        path: '',
                        name: 'show-orders',
                        component: () => import('@Pages/orders/Orders.vue')
                    },
                    {
                        path: 'create',
                        component: () => import('@Pages/orders/order/Order.vue'),
                        children: [
                            {
                                path: '',
                                name: 'create-order',
                                component: () => import('@Pages/orders/order/editable/Content.vue')
                            }
                        ]
                    },
                    {
                        path: ':order_id',
                        component: () => import('@Pages/orders/order/Order.vue'),
                        children: [
                            {
                                path: '',
                                name: 'show-order',
                                component: () => import('@Pages/orders/order/viewable/Content.vue'),
                            },
                            {
                                path: 'edit',
                                name: 'edit-order',
                                component: () => import('@Pages/orders/order/editable/Content.vue'),
                            }
                        ]
                    }
                ]
            },
            {
                path: 'products',
                children: [
                    {
                        path: '',
                        name: 'show-products',
                        component: () => import('@Pages/products/Products.vue')
                    },
                    {
                        path: 'create',
                        name: 'create-product',
                        component: () => import('@Pages/products/product/Product.vue')
                    },
                    {
                        path: 'import-products',
                        name: 'import-products',
                        component: () => import('@Pages/products/import-products/ImportProducts.vue')
                    },
                    {
                        path: 'bulk-edit',
                        name: 'bulk-edit-products',
                        component: () => import('@Pages/products/bulk-edit-products/BulkEditProducts.vue')
                    },
                    {
                        path: ':product_id',
                        name: 'edit-product',
                        component: () => import('@Pages/products/product/Product.vue')
                    },
                    {
                        path: 'tags',
                        children: [
                            {
                                path: '',
                                name: 'show-product-tags',
                                component: () => import('@Pages/tags/Tags.vue')
                            },
                            {
                                path: 'create',
                                name: 'create-product-tag',
                                component: () => import('@Pages/tags/tag/Tag.vue')
                            },
                            {
                                path: ':tag_id',
                                name: 'edit-product-tag',
                                component: () => import('@Pages/tags/tag/Tag.vue')
                            }
                        ]
                    },
                ]
            },
            {
                path: 'customers',
                children: [
                    {
                        path: '',
                        name: 'show-customers',
                        component: () => import('@Pages/customers/Customers.vue')
                    },
                    {
                        path: 'create',
                        name: 'create-customer',
                        component: () => import('@Pages/customers/customer/Customer.vue')
                    },
                    {
                        path: 'import-customers',
                        name: 'import-customers',
                        component: () => import('@Pages/customers/import-customers/ImportCustomers.vue')
                    },
                    {
                        path: 'bulk-edit',
                        name: 'bulk-edit-customers',
                        component: () => import('@Pages/customers/bulk-edit-customers/BulkEditCustomers.vue')
                    },
                    {
                        path: ':customer_id',
                        name: 'edit-customer',
                        component: () => import('@Pages/customers/customer/Customer.vue')
                    },
                    {
                        path: 'tags',
                        children: [
                            {
                                path: '',
                                name: 'show-customer-tags',
                                component: () => import('@Pages/tags/Tags.vue')
                            },
                            {
                                path: 'create',
                                name: 'create-customer-tag',
                                component: () => import('@Pages/tags/tag/Tag.vue')
                            },
                            {
                                path: ':tag_id',
                                name: 'edit-customer-tag',
                                component: () => import('@Pages/tags/tag/Tag.vue')
                            }
                        ]
                    },
                ]
            },
            {
                path: 'categories',
                children: [
                    {
                        path: '',
                        name: 'show-categories',
                        component: () => import('@Pages/categories/Categories.vue')
                    },
                    {
                        path: 'create',
                        name: 'create-category',
                        component: () => import('@Pages/categories/category/Category.vue')
                    },
                    {
                        path: ':category_id',
                        name: 'edit-category',
                        component: () => import('@Pages/categories/category/Category.vue')
                    }
                ]
            },
            {
                path: 'design',
                component: () => import('@Pages/design/Design.vue'),
                children: [
                    {
                        path: '',
                        redirect: { name: 'edit-storefront' }
                    },
                    {
                        path: 'storefront',
                        name: 'edit-storefront',
                        components: {
                            default: () => import('@Pages/design/storefront/Storefront.vue'),
                            preview: () => import('@Pages/shop/storefront/Storefront.vue')
                        }
                    },
                    {
                        path: 'checkout',
                        name: 'edit-checkout',
                        components: {
                            default: () => import('@Pages/design/checkout/Checkout.vue'),
                            preview: () => import('@Pages/shop/checkout/Checkout.vue')
                        }
                    },
                    /*
                    {
                        path: 'payment',
                        name: 'edit-payment',
                        components: {
                            default: () => import('@Pages/design/payment/Payment.vue'),
                            preview: () => import('@Pages/shop/payment/Payment.vue')
                        }
                    },
                    {
                        path: 'appearance',
                        name: 'edit-appearance',
                        components: {
                            default: () => import('@Pages/design/appearance/Appearance.vue'),
                            preview: () => import('@Pages/shop/appearance/Appearance.vue')
                        }
                    },
                    {
                        path: 'menus',
                        name: 'edit-menus',
                        components: {
                            default: () => import('@Pages/design/menus/Menus.vue'),
                            preview: () => import('@Pages/shop/menus/Menus.vue')
                        }
                    }
                    */
                ]
            },
            {
                path: 'pricing-plans',
                children: [
                    {
                        path: '',
                        name: 'show-pricing-plans',
                        component: () => import('@Pages/pricing-plans/PricingPlans.vue')
                    },
                    {
                        path: 'verify-payment',
                        name: 'verify-pricing-plan-payment',
                        component: () => import('@Pages/pricing-plans/verify-payment/VerifyPayment.vue')
                    },
                ]
            }
        ]
    },
    {
        path: '/:alias',
        component: () => import('@Layouts/shop/Shop.vue'),
        children: [
            {
                path: '',
                name: 'show-storefront',
                component: () => import('@Pages/shop/storefront/Storefront.vue'),
            },
            {
                path: 'checkout',
                name: 'show-checkout',
                component: () => import('@Pages/shop/checkout/Checkout.vue'),
            },
            {
                path: 'orders',
                children: [
                    {
                        path: ':order_id',
                        //  component: () => import('@Pages/orders/order/Order.vue'),
                        children: [
                            {
                                path: '',
                                name: 'show-order',
                                component: () => import('@Pages/shop/checkout/Checkout.vue'),
                            }
                        ]
                    }
                ]
            },

        ]
    },
    {
        path: '/:catchAll(.*)',
        name: 'notFound',
        component: () => import('@Pages/error/404.vue'),
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        return { top: 0 };
    },
});

router.beforeEach(async (to, from, next) => {
    const uiState = useUiStore();
    const authState = useAuthStore();

    uiState.showLoader();

    if (!authState.user) {
        const storedToken = authState.getTokenFromLocalStorage();

        if (storedToken) {
            authState.token = storedToken;
            authState.setTokenOnRequest(storedToken);

            try {
                await authState.fetchUser();
            } catch (e) {
                authState.unsetUser();
                authState.unsetToken();
            }
        }
    }

    if (to.meta?.requiresAuth === true && !authState.user) {
        return next({
            name: 'login',
            query: { redirect: to.fullPath },
            replace: true
        });
    }

    if (authState.user && to.name === 'login') {
        return next({ name: 'show-stores' });
    }

    return next();
});


router.afterEach(() => {
    const uiState = useUiStore();
    uiState.hideLoader();
});

export default router;
