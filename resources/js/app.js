import 'preline';
import './bootstrap';
import router from './router';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { useUiStore } from "@Stores/ui-store.js";
import { useTagStore } from "@Stores/tag-store.js";
import { useAuthStore } from "@Stores/auth-store.js";
import { useFormStore } from "@Stores/form-store.js";
import { useOrderStore } from "@Stores/order-store.js";
import { useStoreStore } from "@Stores/store-store.js";
import { useProductStore } from "@Stores/product-store.js";
import { useCustomerStore } from "@Stores/customer-store.js";
import { useCategoryStore } from "@Stores/category-store.js";
import { useNotificationStore } from "@Stores/notification-store.js";
import { useChangeHistoryStore } from "@Stores/change-history-store.js";

const app = createApp({});
const pinia = createPinia();

app.use(pinia);
app.use(router);
app.mount('#app');

// Make Pinia States globally available
app.provide("uiState", useUiStore());
app.provide("tagState", useTagStore());
app.provide("authState", useAuthStore());
app.provide("formState", useFormStore());
app.provide("orderState", useOrderStore());
app.provide("storeState", useStoreStore());
app.provide("productState", useProductStore());
app.provide("customerState", useCustomerStore());
app.provide("categoryState", useCategoryStore());
app.provide("notificationState", useNotificationStore());
app.provide("changeHistoryState", useChangeHistoryStore());
