import axios from 'axios';
import { defineStore } from 'pinia';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        isLoadingUser: false
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
    },
});
