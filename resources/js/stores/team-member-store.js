import { defineStore } from 'pinia';
import { useChangeHistoryStore as changeHistoryState } from '@Stores/change-history-store.js';

export const useTeamMemberStore = defineStore('teamMember', {
    state: () => ({
        teamMember: null,
        teamMemberForm: null,
        isUploading: false,
        isLoadingTeamMember: false,
        isAddingTeamMember: false,
        isUpdatingTeamMember: false,
        isRemovingTeamMember: false,
    }),
    actions: {
        reset() {
            this.teamMember = null;
            this.teamMemberForm = null;
            this.isUploading = false;
            this.isLoadingTeamMember = false;
            this.isAddingTeamMember = false;
            this.isUpdatingTeamMember = false;
            this.isRemovingTeamMember = false;
            changeHistoryState().reset();
        },
        setTeamMember(teamMember) {
            this.teamMember = teamMember;
        },
        saveState(actionName) {
            changeHistoryState().saveState(actionName, this.teamMemberForm);
        },
        saveStateDebounced(actionName) {
            changeHistoryState().saveStateDebounced(actionName, this.teamMemberForm);
        },
        saveOriginalState(actionName) {
            changeHistoryState().saveOriginalState(actionName, this.teamMemberForm);
        },
        setTeamMemberForm(teamMember = null, isAdding = false) {
            this.teamMemberForm = {
                role_id: teamMember?.role?.id ?? null,
                email: teamMember?.user?.email ?? teamMember?.email ?? null,
                first_name: teamMember?.user?.first_name ?? teamMember?.first_name ?? null
            };

            this.saveOriginalState('Original team member');
        },
    },
    getters: {
        hasTeamMember() {
            return this.teamMember != null;
        }
    }
});
