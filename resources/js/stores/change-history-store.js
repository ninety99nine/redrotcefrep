import { diff } from 'deep-diff';
import { defineStore } from 'pinia';
import debounce from 'lodash.debounce';
import cloneDeep from 'lodash.clonedeep';

export const useChangeHistoryStore = defineStore('change-history', {
  state: () => {
    return {
      data: null,
      history: {
        timeline: [],
        currentIndex: null,
      },
      actionButtons: [],
      listeners: {
        undo: null,
        redo: null,
        jumpToHistory: null,
        resetHistoryToCurrent: null,
        resetHistoryToOriginal: null,
      },
    };
  },
  actions: {
    reset() {
      this.data = null;
      this.history = {
        timeline: [],
        currentIndex: null,
      };
      this.actionButtons = [];
      this.listeners = {
        undo: null,
        redo: null,
        jumpToHistory: null,
        resetHistoryToCurrent: null,
        resetHistoryToOriginal: null,
      };
    },
    removeButtons() {
      this.actionButtons = [];
    },
    addDiscardButton() {
      this.actionButtons.push({
        icon: null,
        type: 'light',
        loading: false,
        label: 'Discard',
        action: this.resetHistoryToOriginal,
      });
    },
    addActionButton(label, action, type, icon) {
      this.actionButtons.push({
        icon: icon,
        type: type,
        label: label,
        action: action,
        loading: false,
      });
    },
    async saveOriginalState(actionName, data) {
      this.resetHistory();
      data = cloneDeep(data);
      await this.saveState(actionName, data);
    },
    saveStateDebounced: debounce(function (actionName, data) {
      this.saveState(actionName, data);
    }, 500),
    async saveState(actionName, data) {

      if (!actionName) {
        console.warn('Action name is required to save the state.');
        return;
      }

      // Convert files to Base64 before saving
      const serializedData = await this.convertFilesToBase64(data);

      if (this.history.timeline.length === 0) {

        this.history.timeline.unshift({
          state: JSON.stringify(serializedData),
          timestamp: new Date().toISOString(),
          name: actionName,
        });

        this.history.currentIndex = 0;
        this.data = serializedData;

      }else{

        const lastState = JSON.parse(this.history.timeline[0].state);
        const differences = diff(lastState, serializedData);

        if (differences && differences.length > 0) {

            if (this.history.currentIndex > 0) {

                this.history.timeline = this.history.timeline.slice(this.history.currentIndex);
                this.history.currentIndex = 0;

            }

            this.history.timeline.unshift({
                state: JSON.stringify(serializedData),
                timestamp: new Date().toISOString(),
                name: actionName,
            });
            this.history.currentIndex = 0;
            this.data = serializedData;

        }

      }
    },
    undo() {
      if (this.canUndo) {
        this.history.currentIndex += 1;
        const previousState = this.history.timeline[this.history.currentIndex];
        this.data = this.convertBase64ToFiles(JSON.parse(previousState.state));
      } else {
        console.warn('Cannot undo. Already at the earliest state.');
      }

      if (this.listeners.undo) {
        this.listeners.undo(this.data);
      }
    },
    redo() {
      if (this.canRedo) {
        this.history.currentIndex -= 1;
        const nextState = this.history.timeline[this.history.currentIndex];
        this.data = this.convertBase64ToFiles(JSON.parse(nextState.state));
      } else {
        console.warn('Cannot redo. Already at the latest state.');
      }

      if (this.listeners.redo) {
        this.listeners.redo(this.data);
      }
    },
    jumpToHistory(index) {
      if (index >= 0 && index < this.history.timeline.length) {
        this.history.currentIndex = index;
        const selectedState = this.history.timeline[index];
        this.data = this.convertBase64ToFiles(JSON.parse(selectedState.state));
      } else {
        console.warn('Invalid history index.');
      }

      if (this.listeners.jumpToHistory) {
        this.listeners.jumpToHistory(this.data);
      }
    },
    resetHistory() {
      this.history.currentIndex = null;
      this.history.timeline = [];
    },
    resetHistoryToOriginal() {
      if (this.history.timeline.length > 0) {
        this.history.currentIndex = 0;
        const originalState = this.history.timeline[this.history.timeline.length - 1];
        this.history.timeline = [originalState];
        this.data = this.convertBase64ToFiles(JSON.parse(originalState.state));
      }

      if (this.listeners.resetHistoryToOriginal) {
        this.listeners.resetHistoryToOriginal(this.data);
      }
    },
    resetHistoryToCurrent() {
      if (this.history.timeline.length > 0) {
        this.history.currentIndex = 0;
        const currentState = this.history.timeline[0];
        this.history.timeline = [currentState];
        this.data = this.convertBase64ToFiles(JSON.parse(currentState.state));
      }

      if (this.listeners.resetHistoryToCurrent) {
        this.listeners.resetHistoryToCurrent(this.data);
      }
    },
    // Helper function to convert a File to a Base64 string
    async fileToBase64(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result); // Returns Base64 string
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    },
    // Helper function to convert a Base64 string back to a File
    base64ToFile(base64, filename, mimeType) {
        const byteString = atob(base64.split(',')[1]);
        const mime = mimeType || base64.match(/data:([a-zA-Z0-9]+\/[a-zA-Z0-9-.+]+).*?,/)[1];
        const ab = new ArrayBuffer(byteString.length);
        const ia = new Uint8Array(ab);
        for (let i = 0; i < byteString.length; i++) {
            ia[i] = byteString.charCodeAt(i);
        }
        return new File([ab], filename, { type: mime });
    },
    // Helper function to recursively convert Files to Base64 in an object
    async convertFilesToBase64(obj) {
        const result = Array.isArray(obj) ? [] : {};
        for (const key in obj) {
            if (obj[key] instanceof File) {
            result[key] = {
                __isFile: true,
                base64: await this.fileToBase64(obj[key]),
                name: obj[key].name,
                type: obj[key].type,
            };
            } else if (typeof obj[key] === 'object' && obj[key] !== null) {
            result[key] = await this.convertFilesToBase64(obj[key]);
            } else {
            result[key] = obj[key];
            }
        }
        return result;
    },
    // Helper function to recursively convert Base64 back to Files
    convertBase64ToFiles(obj) {
        const result = Array.isArray(obj) ? [] : {};
        for (const key in obj) {
            if (obj[key]?.__isFile) {
            result[key] = this.base64ToFile(obj[key].base64, obj[key].name, obj[key].type);
            } else if (typeof obj[key] === 'object' && obj[key] !== null) {
            result[key] = this.convertBase64ToFiles(obj[key]);
            } else {
            result[key] = obj[key];
            }
        }
        return result;
    },
    getOriginalState() {
        return JSON.parse(this.history.timeline[this.history.timeline.length - 1].state);
    },
    getCurrentState() {
        return JSON.parse(this.history.timeline[this.history.currentIndex].state);
    },
  },
  getters: {
    canUndo: (state) => {
      return state.history.currentIndex < state.history.timeline.length - 1;
    },
    canRedo: (state) => {
      return state.history.currentIndex > 0;
    },
    historyItems: (state) => {
      return state.history.timeline.map((item, index) => ({
        ...item,
        isActive: index === state.history.currentIndex,
      }));
    },
    totalHistoryItems: (state) => {
      return state.history.timeline.length;
    },
    hasChangeHistory: (state) => {
      return state.totalHistoryItems >= 2;
    },
    hasChanges: (state) => {
      if (state.history.timeline.length === 0 || state.history.currentIndex === null) {
        return false;
      }
      const currentState = state.getCurrentState();
      const originalState = state.getOriginalState();
      const differences = diff(originalState, currentState);
      return differences && differences.length > 0;
    },
  },
});
