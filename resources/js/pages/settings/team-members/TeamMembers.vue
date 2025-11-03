<template>
    <div class="pt-24 px-8 relative select-none">

        <div class="relative bg-white/80 p-4 rounded-md mb-60">

            <h1 class="text-lg font-semibold mb-4">Team Members</h1>

            <!-- Team Members Table -->
            <Table
                @search="search"
                :columns="columns"
                :perPage="perPage"
                @paginate="paginate"
                resource="team-members"
                :searchTerm="searchTerm"
                :pagination="pagination"
                @refresh="showTeamMembers"
                :isLoading="isLoadingTeamMembers"
                @updatedColumns="updatedColumns"
                @updatedFilters="updatedFilters"
                @updatedSorting="updatedSorting"
                @updatedPerPage="updatedPerPage"
                :filterExpressions="filterExpressions"
                :sortingExpressions="sortingExpressions"
                searchPlaceholder="Search by team member name">

                <template #afterRefreshButton>
                    <div class="flex items-center space-x-2">
                        <!-- Add Team Member Button -->
                        <Button
                            size="xs"
                            type="primary"
                            :leftIcon="Plus"
                            :action="onAddTeamMember">
                            <span>Add Team Member</span>
                        </Button>
                    </div>
                </template>

                <!-- Select Action -->
                <template #belowToolbar>
                    <div :class="[{ 'hidden' : totalCheckedRows == 0 }, 'bg-gray-50 border border-gray-200 flex items-center mb-2 p-4 rounded-lg shadow space-x-2']">
                        <span class="text-sm">Actions: </span>
                        <Dropdown
                            ref="actionDropdown"
                            dropdownClasses="w-72"
                            :options="bulkSelectionOptions">
                            <template #triggerText>
                                <span>Select Action ({{ `${totalCheckedRows} selected` }})</span>
                            </template>
                        </Dropdown>
                    </div>
                </template>

                <!-- Table Head -->
                <template #head>
                    <tr>
                        <th scope="col" class="whitespace-nowrap align-center px-4 py-4">
                            <Input
                                type="checkbox"
                                v-model="selectAll">
                            </Input>
                        </th>
                        <template
                            :key="index"
                            v-for="(column, index) in columns">
                            <th
                                scope="col"
                                v-if="column.active"
                                class="whitespace-nowrap align-center pr-4 py-4">
                                {{ column.name }}
                            </th>
                        </template>
                        <th scope="col" class="whitespace-nowrap align-center pr-4 py-4"></th>
                    </tr>
                </template>

                <!-- Table Body -->
                <template #body>
                    <tr
                        :key="teamMember.id"
                        @click.stop="onView(teamMember)"
                        v-for="teamMember in teamMembers"
                        :class="[checkedRows[teamMember.id] ? 'bg-blue-50' : 'bg-white hover:bg-gray-50', 'group cursor-pointer border-b border-blue-100']">
                        <template
                            :key="columnIndex"
                            v-for="(column, columnIndex) in columns">
                            <td
                                @click.stop
                                v-if="columnIndex == 0"
                                class="whitespace-nowrap align-center px-4 py-4">
                                <Input
                                    type="checkbox"
                                    v-model="checkedRows[teamMember.id]">
                                </Input>
                            </td>
                            <template v-if="column.active">
                                <!-- Name -->
                                <td v-if="column.name == 'Name'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">
                                    <span>{{ teamMember?.user?.name ?? teamMember?.first_name }}</span>
                                </td>
                                <!-- Email -->
                                <td v-else-if="column.name == 'Email'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">
                                    <span>{{ teamMember?.user?.email ?? teamMember?.email }}</span>
                                </td>
                                <!-- Role -->
                                <td v-else-if="column.name == 'Role'" class="align-center pr-4 py-4 text-sm">
                                    <Pill v-if="teamMember.creator" type="success" size="xs" class="ml-1">creator</Pill>
                                    <Pill v-else type="light" size="xs">{{ teamMember.role.name }}</Pill>
                                </td>
                                <!-- Joined Date -->
                                <td v-else-if="column.name == 'Joined Date'" class="whitespace-nowrap align-center pr-4 py-4 text-sm">
                                    <div
                                        v-if="teamMember.joined_at"
                                        class="flex space-x-1 items-center">
                                        <span>{{ formattedDatetime(teamMember.joined_at) }}</span>
                                        <Popover
                                            placement="top"
                                            class="opacity-0 group-hover:opacity-100"
                                            :content="formattedRelativeDate(teamMember.joined_at)">
                                        </Popover>
                                    </div>
                                    <Pill v-else type="warning" size="xs" class="ml-1">invitation sent</Pill>
                                </td>
                            </template>
                            <!-- Actions -->
                            <td v-if="columnIndex == (columns.length - 1)" class="align-center pr-4 py-4">
                                <div class="flex items-center space-x-4">
                                    <span v-if="!isRemovingTeamMember(teamMember)" @click.stop.prevent="onView(teamMember)" class="text-sm font-medium text-blue-600 dark:text-blue-500 hover:underline">View</span>
                                    <Loader v-if="isRemovingTeamMember(teamMember)" type="danger">
                                        <span class="text-xs ml-2">Removing...</span>
                                    </Loader>
                                    <span v-else @click.stop.prevent="showRemoveConfirmationModal(teamMember)" class="text-sm font-medium text-red-600 dark:text-red-500 hover:underline">Remove</span>
                                </div>
                            </td>
                        </template>
                    </tr>
                </template>

            </Table>

        </div>

        <!-- Remove Team Members -->
        <Modal
            approveType="danger"
            ref="removeTeamMembersModal"
            :leftApproveIcon="Trash2"
            :approveAction="removeTeamMembers"
            :approveLoading="isRemovingTeamMembers"
            :approveText="totalCheckedRows == 1 ? 'Remove Team Member' : 'Remove Team Members'">
            <template #content>
                <p class="text-lg font-bold border-b border-gray-300 border-dashed pb-4 mb-4">Remove Team Members</p>
                <div class="flex space-x-2 items-center p-4 text-xs bg-red-50 rounded-lg mb-8">
                    <svg class="w-6 h-6 text-red-500 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    <span class="text-red-500">Are you sure you want to remove {{ totalCheckedRows > 1 ? `these ${totalCheckedRows} team members` : 'this team member' }}?</span>
                </div>
            </template>
        </Modal>

        <!-- Confirm Remove Team Member -->
        <Modal
            approveType="danger"
            ref="removeTeamMemberModal"
            :leftApproveIcon="Trash2"
            approveText="Remove Team Member"
            :approveAction="removeTeamMember"
            :approveLoading="isRemovingTeamMember(removableTeamMember)">
            <template #content>
                <p class="text-lg font-bold border-b border-dashed border-gray-200 pb-4 mb-4">Confirm Remove</p>
                <p v-if="removableTeamMember" class="mb-8">Are you sure you want to remove <span class="font-bold text-black">{{ removableTeamMember?.user?.name ?? removableTeamMember?.first_name }}</span>?</p>
            </template>
        </Modal>
    </div>
</template>

<script>
    import axios from 'axios';
    import isEqual from 'lodash.isEqual';
    import Pill from '@Partials/Pill.vue';
    import Input from '@Partials/Input.vue';
    import Modal from '@Partials/Modal.vue';
    import Loader from '@Partials/Loader.vue';
    import Button from '@Partials/Button.vue';
    import Popover from '@Partials/Popover.vue';
    import Dropdown from '@Partials/Dropdown.vue';
    import Table from '@Partials/table/Table.vue';
    import { isNotEmpty } from '@Utils/stringUtils';
    import { Move, Plus, Trash2 } from 'lucide-vue-next';
    import { formattedDatetime, formattedRelativeDate } from '@Utils/dateUtils.js';
    import NoDataPlaceholder from '@Partials/table/components/NoDataPlaceholder.vue';

    export default {
        inject: ['formState', 'storeState', 'notificationState'],
        components: {
            Move, Plus, Pill, Input, Modal, Loader, Button, Popover, Dropdown, Table, NoDataPlaceholder
        },
        data() {
            return {
                Plus,
                Trash2,
                teamMembers: [],
                perPage: '15',
                checkedRows: [],
                pagination: null,
                searchTerm: null,
                selectAll: false,
                latestRequestId: 0,
                filterExpressions: [],
                sortingExpressions: [],
                removableTeamMember: null,
                cancelTokenSource: null,
                isRemovingTeamMemberIds: [],
                isLoadingTeamMembers: false,
                columns: this.prepareColumns(),
                bulkSelectionOptions: [
                    {
                        label: 'Remove',
                        action: this.showRemoveTeamMembersModal,
                    }
                ],
            }
        },
        watch: {
            store(newValue, oldValue) {
                if(!oldValue && newValue) {
                    this.showTeamMembers();
                }
            },
            selectAll(newValue) {
                this.checkedRows = this.teamMembers.reduce((acc, teamMember) => {
                    acc[teamMember.id] = newValue;
                    return acc;
                }, {});
            },
        },
        computed: {
            store() {
                return this.storeState.store;
            },
            hasSearchTerm() {
                return this.isNotEmpty(this.searchTerm);
            },
            isRemovingTeamMembers() {
                return this.isRemovingTeamMemberIds.length > 0;
            },
            totalCheckedRows() {
                return Object.values(this.checkedRows).filter(checked => checked).length;
            },
            hasFilterExpressions() {
                return this.filterExpressions.length > 0;
            },
            hasSortingExpressions() {
                return this.sortingExpressions.length > 0;
            },
        },
        methods: {
            isNotEmpty,
            formattedDatetime,
            formattedRelativeDate,
            prepareColumns() {
                const columnNames = ['Name', 'Email', 'Role', 'Joined Date'];
                const defaultColumnNames = ['Name', 'Email', 'Role', 'Joined Date'];

                return columnNames.map(name => ({
                    name,
                    active: defaultColumnNames.includes(name),
                    priority: defaultColumnNames.includes(name)
                }));
            },
            showRemoveTeamMembersModal() {
                this.$refs.actionDropdown.hideDropdown();
                this.$refs.removeTeamMembersModal.showModal();
            },
            showRemoveConfirmationModal(teamMember) {
                this.removableTeamMember = teamMember;
                this.$refs.removeTeamMemberModal.showModal();
            },
            isRemovingTeamMember(teamMember) {
                if(teamMember == null) return false;
                return this.isRemovingTeamMemberIds.findIndex((id) => id == teamMember.id) != -1;
            },
            onView(teamMember) {
                this.$router.push({
                    name: 'edit-team-member',
                    params: {
                        team_member_id: teamMember.id
                    },
                    query: {
                        store_id: this.store.id,
                        searchTerm: this.searchTerm,
                        filterExpressions: this.filterExpressions.join('|'),
                        sortingExpressions: this.sortingExpressions.join('|')
                    }
                });
            },
            onAddTeamMember() {
                this.$router.push({
                    name: 'add-team-member',
                    query: { store_id: this.store.id }
                });
            },
            paginate(page) {
                this.showTeamMembers(page);
            },
            search(searchTerm) {
                this.searchTerm = searchTerm;
                this.showTeamMembers();
            },
            updatedColumns(columns) {
                this.columns = columns;
            },
            updatedFilters(filters) {
                const newFilterExpressions = filters.map((filter) => filter.expression);
                if(!isEqual(this.filterExpressions, newFilterExpressions)) {
                    this.filterExpressions = newFilterExpressions;
                    this.showTeamMembers();
                }
            },
            updatedSorting(sorting) {
                const newSortingExpressions = sorting.map((sort) => sort.expression);
                if(!isEqual(this.sortingExpressions, newSortingExpressions)) {
                    this.sortingExpressions = newSortingExpressions;
                    this.showTeamMembers();
                }
            },
            updatedPerPage(perPage) {
                this.perPage = perPage;
                this.showTeamMembers();
            },
            async showTeamMembers(page = 1) {
                const currentRequestId = ++this.latestRequestId;

                try {
                    this.isLoadingTeamMembers = true;

                    if (this.cancelTokenSource) {
                        this.cancelTokenSource.cancel('Request superseded by a newer one');
                    }

                    this.cancelTokenSource = axios.CancelToken.source();

                    let config = {
                        params: {
                            page: page,
                            per_page: this.perPage,
                            store_id: this.store.id,
                            _relationships: ['user', 'role'].join(',')
                        },
                        cancelToken: this.cancelTokenSource.token
                    }

                    if(this.hasSearchTerm) config.params['search'] = this.searchTerm;
                    if(this.hasFilterExpressions) config.params['_filters'] = this.filterExpressions.join('|');
                    if(this.hasSortingExpressions) config.params['_sort'] = this.sortingExpressions.join('|');

                    const response = await axios.get(`/api/team-members`, config);

                    if (currentRequestId !== this.latestRequestId) return;

                    this.pagination = response.data;
                    this.teamMembers = this.pagination.data;

                    this.checkedRows = this.teamMembers.reduce((acc, teamMember) => {
                        acc[teamMember.id] = false;
                        return acc;
                    }, {});

                } catch (error) {
                    if (axios.isCancel(error)) return;
                    if (currentRequestId !== this.latestRequestId) return;

                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while fetching team members';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to fetch team members:', error);
                } finally {
                    if (currentRequestId !== this.latestRequestId) return;
                    this.isLoadingTeamMembers = false;
                    this.cancelTokenSource = null;
                }
            },
            async removeTeamMembers() {
                try {
                    const teamMemberIds = this.teamMembers.filter(teamMember => this.checkedRows[teamMember.id]).map(teamMember => teamMember.id)
                        .filter(teamMemberId => !this.isRemovingTeamMemberIds.includes(teamMemberId));

                    if (teamMemberIds.length === 0) return;

                    this.isRemovingTeamMemberIds.push(...teamMemberIds);

                    const config = {
                        data: {
                            store_id: this.store.id,
                            team_member_ids: teamMemberIds
                        }
                    }

                    await axios.delete(`/api/team-members`, config);

                    this.storeState.silentUpdate();
                    this.notificationState.showSuccessNotification(teamMemberIds.length == 1 ? 'Team member removed' : 'Team members removed');
                    this.teamMembers = this.teamMembers.filter(teamMember => !teamMemberIds.includes(teamMember.id));
                    if(this.teamMembers.length == 0) this.showTeamMembers();

                    this.isRemovingTeamMemberIds = this.isRemovingTeamMemberIds.filter(id => !teamMemberIds.includes(id));

                    teamMemberIds.forEach(teamMemberId => {
                        if (this.checkedRows[teamMemberId] !== undefined) {
                            this.checkedRows[teamMemberId] = false;
                        }
                    });

                    this.selectAll = false;

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while removing team members';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to remove team members:', error);
                } finally {
                    this.$refs.removeTeamMembersModal.hideModal();
                }
            },
            async removeTeamMember() {
                try {
                    if(this.isRemovingTeamMemberIds.includes(this.removableTeamMember.id)) return;

                    this.isRemovingTeamMemberIds.push(this.removableTeamMember.id);

                    const config = {
                        data: {
                            store_id: this.store.id
                        }
                    }

                    await axios.delete(`/api/team-members/${this.removableTeamMember.id}`, config);

                    this.storeState.silentUpdate();
                    this.notificationState.showSuccessNotification('Team member removed');
                    this.teamMembers = this.teamMembers.filter(teamMember => teamMember.id != this.removableTeamMember.id);
                    if(this.teamMembers.length == 0) this.showTeamMembers();

                } catch (error) {
                    const message = error?.response?.data?.message || error?.message || 'Something went wrong while removing team member';
                    this.notificationState.showWarningNotification(message);
                    this.formState.setServerFormErrors(error);
                    console.error('Failed to remove team member:', error);
                } finally {
                    this.isRemovingTeamMemberIds.splice(this.isRemovingTeamMemberIds.findIndex((id) => id == this.removableTeamMember.id), 1);
                    this.$refs.removeTeamMemberModal.hideModal();
                }
            }
        },
        created() {
            this.isLoadingTeamMembers = true;
            this.searchTerm = this.$route.query.searchTerm;
            if(this.$route.query.filterExpressions) this.filterExpressions = this.$route.query.filterExpressions.split('|');
            if(this.$route.query.sortingExpressions) this.sortingExpressions = this.$route.query.sortingExpressions.split('|');
            if(this.store) this.showTeamMembers();
        }
    };
</script>
