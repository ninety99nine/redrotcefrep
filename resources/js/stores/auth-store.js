import axios from 'axios';
import { defineStore } from 'pinia';
import { useChangeHistoryStore as changeHistoryState } from '@Stores/change-history-store.js';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        userForm: null,
        isLoadingUser: false,
        isUpdatingUser: false,
    }),
    actions: {
        async fetchUser() {
            this.isLoadingUser = true;
            const response = await axios.get('/api/auth/user');
            this.setUser(response.data);
            this.isLoadingUser = false;
        },
        setUser(user) {
            this.user = user;
        },
        unsetUser() {
            this.user = null;
        },
        setToken(token) {
            this.token = token;
        },
        unsetToken() {
            this.removeTokenOnLocalStorage();
            delete axios.defaults.headers.common['Authorization'];
        },
        getTokenFromLocalStorage() {
            return localStorage.getItem('auth_token');
        },
        setTokenOnLocalStorage(token) {
            localStorage.setItem('auth_token', token);
        },
        removeTokenOnLocalStorage() {
            localStorage.removeItem('auth_token');
        },
        setTokenOnRequest(token) {
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        },
        saveState(actionName) {
            changeHistoryState().saveState(actionName, this.userForm);
        },
        saveStateDebounced(actionName) {
            console.log(actionName);
            changeHistoryState().saveStateDebounced(actionName, this.userForm);
        },
        saveOriginalState(actionName) {
            changeHistoryState().saveOriginalState(actionName, this.userForm);
        },
        setUserForm() {

            this.userForm = {
                first_name: this.user.first_name ?? null,
                last_name: this.user.last_name ?? null,
                email: this.user.email ?? null,
                password_confirmation: null,
                current_password: null,
                password: null,
            };

            this.saveOriginalState('Original account');

        },
    },
});
