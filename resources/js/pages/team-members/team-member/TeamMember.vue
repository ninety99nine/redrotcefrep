<template>
    <div class="pt-24 px-4">
        <div class="grid grid-cols-12 gap-4 mb-4">
            <div class="col-span-8 col-start-3">
                <div class="select-none bg-white rounded-lg p-4 mb-4">
                    <div class="flex items-center space-x-4">
                        <!-- Back Button -->
                        <Button
                            size="xs"
                            type="light"
                            :action="goBack"
                            :leftIcon="MoveLeft">
                            <span>Back</span>
                        </Button>
                        <div v-if="isLoadingStore || isLoadingTeamMember || (isEditing && !hasTeamMember)" class="flex items-center space-x-2">
                            <Skeleton width="w-40"></Skeleton>
                            <Skeleton width="w-4"></Skeleton>
                        </div>
                        <template v-else>
                            <!-- Heading -->
                            <div class="flex items-center space-x-1">
                                <h1 class="text-lg text-gray-700 font-semibold">
                                    {{ isAdding ? 'Add Team Member' : teamMemberForm.name || '...' }}
                                </h1>
                                <Popover content="Team members are users assigned to manage your store with specific roles." placement="top"></Popover>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="relative mb-8">
                    <BackdropLoader v-if="isLoadingTeamMember || isSubmitting" :showSpinningLoader="false" class="rounded-lg"></BackdropLoader>
                    <div class="bg-white rounded-lg space-y-4 p-4">
                        <!-- Email Input -->
                        <Input
                            type="email"
                            label="Email"
                            placeholder="john@store.com"
                            v-model="teamMemberForm.email"
                            :errorText="formState.getFormError('email')"
                            tooltipContent="The email address of the team member"
                            @input="teamMemberState.saveStateDebounced('Email changed')">
                        </Input>
                        <!-- Role Select -->
                        <Select
                            class="w-full"
                            :search="false"
                            label="Role"
                            :options="roleOptions"
                            v-model="teamMemberForm.role_id"
                            :errorText="formState.getFormError('role_id')"
                            @change="teamMemberState.saveStateDebounced('Role changed')"
                            tooltipContent="Select the role for this team member">
                        </Select>
                    </div>
                </div>

                <div
                    v-if="teamMember"
                    :class="['flex items-center justify-between space-x-4 overflow-hidden rounded-lg p-4 border mb-20', isLoadingTeamMember ? 'border-gray-300 bg-gray-50' : 'border-red-300 bg-red-50']">
                    <div class="space-y-2">
                        <p>Remove <span class="font-bold text-black">{{ teamMemberForm.name }}</span>?</p>
                        <p class="text-sm">Once this team member is removed, they will lose access to the store.</p>
                    </div>
                    <div class="flex justify-end">
                        <Modal
                            triggerType="danger"
                            approveType="danger"
                            :approveLeftIcon="Trash2"
                            triggerText="Remove Team Member"
                            approveText="Remove Team Member"
                            :approveAction="removeTeamMember"
                            :triggerLoading="isRemovingTeamMember"
                            :approveLoading="isRemovingTeamMember">
                            <template #content>
                                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">Confirm Remove</p>
                                <p class="mb-8">Are you sure you want to permanently remove <span class="font-bold text-black">{{ teamMemberForm.name }}</span>?</p>
                            </template>
                        </Modal>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Input from '@Partials/Input.vue';
    import Modal from '@Partials/Modal.vue';
    import cloneDeep from 'lodash/cloneDeep';
    import Button from '@Partials/Button.vue';
    import Loader from '@Partials/Loader.vue';
    import Select from '@Partials/Select.vue';
    import Popover from '@Partials/Popover.vue';
    import { isEmpty } from '@Utils/stringUtils';
    import Skeleton from '@Partials/Skeleton.vue';
    import { Trash2, MoveLeft } from 'lucide-vue-next';
    import BackdropLoader from '@Partials/BackdropLoader.vue';

    export default {
        inject: ['formState', 'storeState', 'teamMemberState', 'changeHistoryState', 'notificationState'],
        components: {
            Input, Modal, Button, Loader, Select, Popover, Skeleton, BackdropLoader
        },
        data() {
            return {
                Trash2,
                MoveLeft,
                roleOptions: [
                    { label: 'Admin', value: 'admin' },
                    { label: 'Editor', value: 'editor' },
                    { label: 'Viewer', value: 'viewer' }
                ]
            }
        },
        watch: {
            store(newValue, oldValue) {
                if(!oldValue && newValue) {
                    this.setup();
                }
            },
            '$route.params.user_id'(newValue) {
                if(newValue) {
                    this.setup();
                    this.setActionButtons();
                }
            },
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            teamMember() {
                return this.teamMemberState.teamMember;
            },
            hasTeamMember() {
                return this.teamMemberState.hasTeamMember;
            },
            userId() {
                return this.$route.params.user_id;
            },
            isLoadingStore() {
                return this.storeState.isLoadingStore;
            },
            isLoadingTeamMember() {
                return this.teamMemberState.isLoadingTeamMember;
            },
            isEditing() {
                return this.$route.name === 'edit-team-member';
            },
            isAdding() {
                return this.$route.name === 'add-team-member';
            },
            teamMemberForm() {
                return this.teamMemberState.teamMemberForm;
            },
            isSubmitting() {
                if(this.changeHistoryState.actionButtons.length == 0) return false;
                return this.changeHistoryState.actionButtons[1].loading;
            },
            isRemovingTeamMember() {
                return this.teamMemberState.isRemovingTeamMember;
            }
        },
        methods: {
            isEmpty,
            goBack() {
                this.navigateToTeamMembers();
            },
            async setup() {
                if(this.teamMemberForm == null) this.teamMemberState.setTeamMemberForm(null, this.isAdding);
                if(this.isEditing && this.store) await this.showTeamMember();
            },
            async navigateToTeamMembers() {
                await this.$router.replace({
                    name: 'show-team-members',
                    query: {
                        store_id: this.store.id,
                        searchTerm: this.$route.query.searchTerm,
                        filterExpressions: this.$route.query.filterExpressions,
                        sortingExpressions: this.$route.query.sortingExpressions
                    }
                });
            },
            async navigateToEditTeamMember(teamMember) {
                await this.$router.push({
                    name: 'edit-team-member',
                    params: {
                        user_id: teamMember.id
                    },
                    query: {
                        store_id: this.store.id,
                        searchTerm: this.$route.query.searchTerm,
                        filterExpressions: this.$route.query.filterExpressions,
                        sortingExpressions: this.$route.query.sortingExpressions
                    }
                });
            },
            async showTeamMember() {
                try {
                    this.teamMemberState.isLoadingTeamMember = true;

                    let config = {
                        params: {
                            store_id: this.store.id,
                            _relationships: ['roles'].join(',')
                        }
                    };

                    const response = await axios.get(`/api/team-members/${this.userId}`, config);

                    const teamMember = response.data;
                    this.teamMemberState.setTeamMember(teamMember);
                    this.teamMemberState.setTeamMemberForm(teamMember);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching team member';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch team member:', error);

                    if (error.response?.status === 404) {
                        await this.navigateToTeamMembers();
                    }
                } finally {
                    this.teamMemberState.isLoadingTeamMember = false;
                }
            },
            async addTeamMember() {
                try {
                    if(this.teamMemberState.isAddingTeamMember || this.teamMemberState.isUploading) return;

                    this.formState.hideFormErrors();

                    if(this.isEmpty(this.teamMemberForm.email)) {
                        this.formState.setFormError('email', 'The email is required');
                    }

                    if(this.isEmpty(this.teamMemberForm.role_id)) {
                        this.formState.setFormError('role_id', 'The role is required');
                    }

                    if(this.formState.hasErrors) {
                        return;
                    }

                    this.teamMemberState.isAddingTeamMember = true;
                    this.changeHistoryState.actionButtons[1].loading = true;

                    const data = {
                        ...this.getPayload(),
                        store_id: this.store.id
                    }

                    const response = await axios.post(`/api/team-members`, data);
                    const addedTeamMember = response.data.user;

                    this.teamMemberForm.id = addedTeamMember.id;

                    this.storeState.silentUpdate();
                    this.notificationState.showSuccessNotification(`Team member added`);
                    this.teamMemberState.saveOriginalState('Original team member');
                    await this.navigateToEditTeamMember(addedTeamMember);

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while adding team member';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to add team member:', error);
                } finally {
                    this.teamMemberState.isAddingTeamMember = false;
                    this.changeHistoryState.actionButtons[1].loading = false;
                }
            },
            async updateTeamMember() {
                try {
                    if(this.teamMemberState.isUpdatingTeamMember) return;

                    this.formState.hideFormErrors();

                    if(this.isEmpty(this.teamMemberForm.email)) {
                        this.formState.setFormError('email', 'The email is required');
                    }

                    if(this.isEmpty(this.teamMemberForm.role_id)) {
                        this.formState.setFormError('role_id', 'The role is required');
                    }

                    if(this.formState.hasErrors) {
                        return;
                    }

                    this.teamMemberState.isUpdatingTeamMember = true;
                    this.changeHistoryState.actionButtons[1].loading = true;

                    const data = {
                        ...this.getPayload(),
                        store_id: this.store.id
                    }

                    await axios.put(`/api/team-members/${this.teamMemberForm.id}`, data);

                    this.storeState.silentUpdate();
                    this.notificationState.showSuccessNotification(`Team member updated`);
                    this.teamMemberState.saveOriginalState('Original team member');

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while updating team member';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to update team member:', error);
                } finally {
                    this.teamMemberState.isUpdatingTeamMember = false;
                    this.changeHistoryState.actionButtons[1].loading = false;
                }
            },
            async removeTeamMember(hideModal) {
                try {
                    if(this.teamMemberState.isRemovingTeamMember) return;

                    this.teamMemberState.isRemovingTeamMember = true;

                    const config = {
                        data: {
                            store_id: this.store.id
                        }
                    }

                    await axios.delete(`/api/team-members/${this.teamMember.id}`, config);

                    this.storeState.silentUpdate();
                    this.notificationState.showSuccessNotification('Team member removed');

                    hideModal();
                    await new Promise(resolve => setTimeout(resolve, 500));
                    await this.navigateToTeamMembers();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while removing team member';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to remove team member:', error);
                    hideModal();
                } finally {
                    this.teamMemberState.isRemovingTeamMember = false;
                }
            },
            getPayload() {
                let data = cloneDeep(this.teamMemberForm);
                return data;
            },
            setActionButtons() {
                if(this.isAdding || this.isEditing) {
                    this.changeHistoryState.removeButtons();
                    this.changeHistoryState.addDiscardButton();
                    this.changeHistoryState.addActionButton(
                        this.isEditing ? 'Save Changes' : 'Add Team Member',
                        this.isEditing ? this.updateTeamMember : this.addTeamMember,
                        'primary',
                        null,
                    );
                }
            },
            setTeamMemberForm(teamMemberForm) {
                this.teamMemberState.teamMemberForm = teamMemberForm;
            }
        },
        beforeRouteLeave(to, from, next) {
            if (this.changeHistoryState.hasChangeHistory) {
                const answer = window.confirm("You have unsaved changes. Are you sure you want to leave?");
                if (!answer) {
                    return next(false);
                }
            }
            next();
        },
        unmounted() {
            this.teamMemberState.reset();
        },
        created() {
            this.setup();
            this.setActionButtons();

            const listeners = ['undo', 'redo', 'jumpToHistory', 'resetHistoryToCurrent', 'resetHistoryToOriginal'];
            for (let i = 0; i < listeners.length; i++) {
                let listener = listeners[i];
                this.changeHistoryState.listeners[listener] = this.setTeamMemberForm;
            }
        }
    };
</script>
