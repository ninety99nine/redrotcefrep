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
                id: teamMember?.id ?? null,
                email: teamMember?.email ?? null,
                name: teamMember?.name ?? (teamMember?.first_name && teamMember?.last_name ? `${teamMember.first_name} ${teamMember.last_name}`.trim() : null),
                role_id: teamMember?.roles?.[0]?.name ?? null,
            };

            if (isAdding) {
                this.teamMemberForm.email = '';
                this.teamMemberForm.role_id = '';
            }

            this.saveOriginalState('Original team member');
        },
    },
    getters: {
        hasTeamMember() {
            return this.teamMember != null;
        }
    }
});
