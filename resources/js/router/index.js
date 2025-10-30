import { useUiStore } from "@Stores/ui-store.js";
import { useAuthStore } from "@Stores/auth-store.js";
import { createRouter, createWebHistory } from 'vue-router';

const routes = [
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
                path: 'verify-email',
                name: 'verify-email',
                component: () => import('@Pages/auth/VerifyEmail.vue'),
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
        path: '/:alias?',   //  Responds to custom domain and alias: Handles { path: '/' } and { path: '/:alias' }
        component: () => import('@Layouts/shop/Shop.vue'),
        children: [
            {
                path: '',
                name: 'show-storefront',
                component: () => import('@Pages/shop/storefront/Storefront.vue'),
                meta: { title: 'Storefront' }
            },
            {
                path: 'search',
                name: 'show-search',
                component: () => import('@Pages/shop/search/Search.vue'),
                meta: { title: 'Search' }
            },
            {
                path: 'checkout',
                name: 'show-checkout',
                component: () => import('@Pages/shop/checkout/Checkout.vue'),
                meta: { title: 'Checkout' }
            },
            {
                path: 'login',
                name: 'show-login',
                component: () => import('@Pages/shop/login/Login.vue'),
                meta: { title: 'Login' }
            },
            {
                path: 'orders',
                children: [
                    {
                        path: ':order_id',
                        children: [
                            {
                                path: '',
                                name: 'show-shop-order',
                                component: () => import('@Pages/shop/orders/Order.vue'),
                                meta: { title: 'Order summary' }
                            },
                            {
                                path: 'pay',
                                children: [
                                    {
                                        path: '',
                                        name: 'show-shop-payment-methods',
                                        component: () => import('@Pages/shop/payments/PaymentMethods.vue'),
                                        meta: { title: 'Payment options' }
                                    },
                                    {
                                        path: ':store_payment_method_id',
                                        name: 'show-shop-payment-method',
                                        component: () => import('@Pages/shop/payments/PaymentMethod.vue'),
                                        meta: { title: 'Payment option' }
                                    },
                                    {
                                        path: 'pending',
                                        name: 'show-shop-pending-payment',
                                        component: () => import('@Pages/shop/payments/PendingPayment.vue'),
                                        meta: { title: 'Pending payment' }
                                    },
                                    {
                                        path: 'confirming',
                                        name: 'show-shop-confirming-payment',
                                        component: () => import('@Pages/shop/payments/ConfirmingPayment.vue'),
                                        meta: { title: 'Confirming payment' }
                                    }
                                ]
                            },
                            {
                                path: 'verify-payment',
                                name: 'verify-shop-payment',
                                component: () => import('@Pages/shop/payments/VerifyPayment.vue'),
                                meta: { title: 'Verifying Payment' }
                            },
                        ]
                    }
                ]
            },
            {
                path: 'products',
                children: [
                    {
                        path: ':product_id',
                        children: [
                            {
                                path: '',
                                name: 'show-shop-product',
                                component: () => import('@Pages/shop/products/Product.vue'),
                                meta: { title: 'Product' }
                            }
                        ]
                    }
                ]
            },
            {
                path: 'categories',
                children: [
                    {
                        path: ':category_id',
                        children: [
                            {
                                path: '',
                                name: 'show-shop-category',
                                component: () => import('@Pages/shop/categories/Category.vue'),
                                meta: { title: 'Category' }
                            }
                        ]
                    }
                ]
            },
            {
                path: 'reviews',
                children: [
                    {
                        path: '',
                        name: 'show-shop-reviews',
                        component: () => import('@Pages/shop/reviews/Reviews.vue'),
                        meta: { title: 'Customer reviews' }
                    },
                    {
                        path: 'create',
                        name: 'create-shop-review',
                        component: () => import('@Pages/shop/reviews/CreateReview.vue'),
                        meta: { title: 'Write a review' }
                    }
                ]
            }
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
                path: 'promotions',
                children: [
                    {
                        path: '',
                        name: 'show-promotions',
                        component: () => import('@Pages/promotions/Promotions.vue')
                    },
                    {
                        path: 'create',
                        name: 'create-promotion',
                        component: () => import('@Pages/promotions/promotion/Promotion.vue')
                    },
                    {
                        path: 'import-promotions',
                        name: 'import-promotions',
                        component: () => import('@Pages/promotions/import-promotions/ImportPromotions.vue')
                    },
                    {
                        path: 'bulk-edit',
                        name: 'bulk-edit-promotions',
                        component: () => import('@Pages/promotions/bulk-edit-promotions/BulkEditPromotions.vue')
                    },
                    {
                        path: ':promotion_id',
                        name: 'edit-promotion',
                        component: () => import('@Pages/promotions/promotion/Promotion.vue')
                    }
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
                path: 'reviews',
                children: [
                    {
                        path: '',
                        name: 'show-reviews',
                        component: () => import('@Pages/reviews/Reviews.vue')
                    },
                    {
                        path: 'create',
                        name: 'create-review',
                        component: () => import('@Pages/reviews/review/Review.vue')
                    },
                    {
                        path: ':review_id',
                        name: 'edit-review',
                        component: () => import('@Pages/reviews/review/Review.vue')
                    }
                ]
            },
            {
                path: 'analytics',
                children: [
                    {
                        path: '',
                        name: 'show-analytics',
                        component: () => import('@Pages/analytics/Analytics.vue')
                    }
                ]
            },
            {
                path: 'marketing',
                children: [
                    {
                        path: '',
                        name: 'show-marketing',
                        component: () => import('@Pages/marketing/Marketing.vue')
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
                    {
                        path: 'payment',
                        name: 'edit-payment',
                        components: {
                            default: () => import('@Pages/design/payment/Payment.vue'),
                            preview: () => import('@Pages/shop/payments/PaymentMethods.vue'),
                        }
                    },
                    {
                        path: 'menu',
                        name: 'edit-menu',
                        components: {
                            default: () => import('@Pages/design/menu/Menu.vue'),
                            preview: () => import('@Pages/shop/storefront/Storefront.vue')
                        }
                    },
                    /*
                    {
                        path: 'appearance',
                        name: 'edit-appearance',
                        components: {
                            default: () => import('@Pages/design/appearance/Appearance.vue'),
                            preview: () => import('@Pages/shop/appearance/Appearance.vue')
                        }
                    }
                    */
                ]
            },
            {
                path: 'settings',
                meta: { settings: true },
                children: [
                    {
                        path: '',
                        name: 'show-general-settings',
                        component: () => import('@Pages/settings/general/General.vue')
                    },
                    {
                        path: 'payment-methods',
                        children: [
                            {
                                path: '',
                                name: 'show-payment-methods',
                                component: () => import('@Pages/settings/payment-methods/PaymentMethods.vue')
                            },
                            {
                                path: 'add',
                                name: 'add-payment-method',
                                component: () => import('@Pages/settings/payment-methods/PaymentMethod.vue')
                            },
                            {
                                path: ':store_payment_method_id',
                                name: 'edit-payment-method',
                                component: () => import('@Pages/settings/payment-methods/PaymentMethod.vue')
                            }
                        ]
                    },
                    {
                        path: 'delivery-methods',
                        children: [
                            {
                                path: '',
                                name: 'show-delivery-methods',
                                component: () => import('@Pages/settings/delivery-methods/DeliveryMethods.vue')
                            },
                            {
                                path: 'add',
                                name: 'add-delivery-method',
                                component: () => import('@Pages/settings/delivery-methods/DeliveryMethod.vue')
                            },
                            {
                                path: ':delivery_method_id',
                                name: 'edit-delivery-method',
                                component: () => import('@Pages/settings/delivery-methods/DeliveryMethod.vue')
                            }
                        ]
                    },
                    {
                        path: 'workflows',
                        children: [
                            {
                                path: '',
                                name: 'show-workflows',
                                component: () => import('@Pages/settings/workflows/Workflows.vue')
                            },
                            {
                                path: 'add',
                                name: 'add-workflow',
                                component: () => import('@Pages/settings/workflows/Workflow.vue')
                            },
                            {
                                path: ':workflow_id',
                                name: 'edit-workflow',
                                component: () => import('@Pages/settings/workflows/Workflow.vue')
                            }
                        ]
                    },
                    {
                        path: 'checkout',
                        name: 'show-checkout-settings',
                        component: () => import('@Pages/settings/checkout/Checkout.vue')
                    },
                    {
                        path: 'seo',
                        name: 'show-seo-settings',
                        component: () => import('@Pages/settings/seo/SEO.vue')
                    },
                    {
                        path: 'billing',
                        name: 'show-billing-settings',
                        component: () => import('@Pages/settings/billing/Billing.vue')
                    },
                    {
                        path: 'domains',
                        children: [
                            {
                                path: '',
                                name: 'show-domains',
                                component: () => import('@Pages/settings/domains/Domains.vue')
                            },
                            {
                                path: 'buy',
                                name: 'buy-domain',
                                component: () => import('@Pages/settings/domains/BuyDomain.vue')
                            },
                            {
                                path: 'add',
                                name: 'add-domain',
                                component: () => import('@Pages/settings/domains/Domain.vue')
                            },
                            {
                                path: 'verify-payment',
                                name: 'verify-domain-payment',
                                component: () => import('@Pages/settings/domains/VerifyDomainPayment.vue')
                            },
                            {
                                path: ':domain_id',
                                name: 'edit-domain',
                                component: () => import('@Pages/settings/domains/Domain.vue')
                            }
                        ]
                    },
                    {
                        path: 'team-members',
                        children: [
                            {
                                path: '',
                                name: 'show-team-members',
                                component: () => import('@Pages/team-members/TeamMembers.vue')
                            },
                            {
                                path: 'add',
                                name: 'add-team-member',
                                component: () => import('@Pages/team-members/team-member/TeamMember.vue')
                            },
                            {
                                path: ':user_id',
                                name: 'edit-team-member',
                                component: () => import('@Pages/team-members/team-member/TeamMember.vue')
                            }
                        ]
                    },
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
        path: '/:catchAll(.*)',
        name: 'notFound',
        component: () => import('@Pages/error/404.vue'),
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (from.name === 'show-shop-product' && to.name === 'show-storefront') {
            console.log('savedPosition !!!!!!!');
            console.log(savedPosition);
            return savedPosition;
        }else{
            return { top: 0 };
        }
    },
});

// Axios interceptor to add custom headers
axios.interceptors.request.use(config => {

    /**
     *  Headers to capture and send page views to the backend.
     */
    const currentRoute = router.currentRoute.value;

    if(currentRoute.meta?.title) {
        config.headers['frontend-page-name'] = currentRoute.meta.title;
        config.headers['frontend-page-url'] = currentRoute.fullPath || '/';

    }

    return config;

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

    // storeId for custom domains (Refer to: resources/views/render.blade.php for window.storeId)
    if (to.path === '/' && !window.storeId) {
        return next({
            name: 'login',
            query: { redirect: to.fullPath },
            replace: true
        });
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
