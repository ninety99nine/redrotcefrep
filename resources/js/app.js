import 'preline';
import './bootstrap';
import router from './router';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import VueEasymde from 'vue3-easymde';
import "easymde/dist/easymde.min.css";
import { useUiStore } from "@Stores/ui-store.js";
import { useTagStore } from "@Stores/tag-store.js";
import { useAuthStore } from "@Stores/auth-store.js";
import { useFormStore } from "@Stores/form-store.js";
import { useOrderStore } from "@Stores/order-store.js";
import { useStoreStore } from "@Stores/store-store.js";
import { useReviewStore } from "@Stores/review-store.js";
import { useDesignStore } from "@Stores/design-store.js";
import { useDomainStore } from "@Stores/domain-store.js";
import { useProductStore } from "@Stores/product-store.js";
import { useCustomerStore } from "@Stores/customer-store.js";
import { useCategoryStore } from "@Stores/category-store.js";
import { useWorkflowStore } from "@Stores/workflow-store.js";
import { usePromotionStore } from "@Stores/promotion-store.js";
import { useTeamMemberStore } from "@Stores/team-member-store.js";
import { useNotificationStore } from "@Stores/notification-store.js";
import { useChangeHistoryStore } from "@Stores/change-history-store.js";
import { useDeliveryMethodStore } from "@Stores/delivery-method-store.js";
import { useStorePaymentMethodStore } from "@Stores/store-payment-method-store.js";

// Store for PWA install prompt
const pwaStore = {
  deferredPrompt: null
};

// Register service worker
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('/service-worker.js')
    .then((registration) => {
      console.log('Service Worker registered with scope:', registration.scope);
    })
    .catch((error) => {
      console.error('Service Worker registration failed:', error);
    });

  // Capture beforeinstallprompt globally
  window.addEventListener('beforeinstallprompt', (e) => {
    console.log('beforeinstallprompt fired', e);
    e.preventDefault();
    pwaStore.deferredPrompt = e; // Always update with latest event
    console.log('pwaStore.deferredPrompt updated:', pwaStore.deferredPrompt);
    // Debug: Log deferredPrompt state after 5 seconds
    setTimeout(() => {
      console.log('DeferredPrompt state after 5s:', pwaStore.deferredPrompt);
    }, 5000);
  });
}

const app = createApp({});
const pinia = createPinia();

app.use(pinia);
app.use(router);
app.use(VueEasymde);

app.mount('#app');

// Provide pwaStore globally
app.provide('pwaStore', pwaStore);

// Make Pinia States globally available
app.provide("uiState", useUiStore());
app.provide("tagState", useTagStore());
app.provide("authState", useAuthStore());
app.provide("formState", useFormStore());
app.provide("orderState", useOrderStore());
app.provide("storeState", useStoreStore());
app.provide("designState", useDesignStore());
app.provide("domainState", useDomainStore());
app.provide("reviewState", useReviewStore());
app.provide("productState", useProductStore());
app.provide("customerState", useCustomerStore());
app.provide("categoryState", useCategoryStore());
app.provide("workflowState", useWorkflowStore());
app.provide("promotionState", usePromotionStore());
app.provide("teamMemberState", useTeamMemberStore());
app.provide("notificationState", useNotificationStore());
app.provide("changeHistoryState", useChangeHistoryStore());
app.provide("deliveryMethodState", useDeliveryMethodStore());
app.provide("storePaymentMethodState", useStorePaymentMethodStore());
