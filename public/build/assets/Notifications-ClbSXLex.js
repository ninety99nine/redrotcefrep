import{T as n}from"./toastify-DZ0FLRMH.js";import{_ as r}from"./_plugin-vue_export-helper-DlAUqK2U.js";const a={inject:["notificationState"],watch:{"notifications.length"(i,e){if(i>e){const t=this.notifications[this.notifications.length-1];let o,s;t.type=="success"?(o=`
                            <div class="flex space-x-2 p-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 lucide shrink-0 lucide-circle-check-icon lucide-circle-check"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                                <p>${t.message}</p>
                                <div class="ms-auto">
                                    <button onclick="tostifyCustomClose(this)" type="button" class="inline-flex shrink-0 justify-center items-center size-5 rounded-lg text-green-800 opacity-50 hover:opacity-100 focus:outline-hidden focus:opacity-100 dark:text-white" aria-label="Close">
                                        <span class="sr-only">Close</span>
                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
                                    </button>
                                </div>
                            </div>
                            `,s="hs-toastify-on:opacity-100 opacity-0 fixed -top-37.5 right-5 z-90 transition-all duration-300 w-80 bg-green-200 text-sm text-green-900 border border-green-900 rounded-lg shadow-lg [&>.toast-close]:hidden dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400"):t.type=="warning"?(o=`
                            <div class="flex space-x-2 p-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 lucide shrink-0 lucide-triangle-alert-icon lucide-triangle-alert"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                                <p>${t.message}</p>
                                <div class="ms-auto">
                                    <button onclick="tostifyCustomClose(this)" type="button" class="inline-flex shrink-0 justify-center items-center size-5 rounded-lg text-yellow-800 opacity-50 hover:opacity-100 focus:outline-hidden focus:opacity-100 dark:text-white" aria-label="Close">
                                        <span class="sr-only">Close</span>
                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
                                    </button>
                                </div>
                            </div>
                            `,s="hs-toastify-on:opacity-100 opacity-0 fixed -top-37.5 right-5 z-90 transition-all duration-300 w-80 bg-yellow-200 text-sm text-yellow-900 border border-yellow-900 rounded-lg shadow-lg [&>.toast-close]:hidden dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400"):(o=`
                            <div class="flex space-x-2 p-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 lucide shrink-0 lucide-info-icon lucide-info"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                                <p>${t.message}</p>
                                <div class="ms-auto">
                                    <button onclick="tostifyCustomClose(this)" type="button" class="inline-flex shrink-0 justify-center items-center size-5 rounded-lg text-gray-800 opacity-50 hover:opacity-100 focus:outline-hidden focus:opacity-100 dark:text-white" aria-label="Close">
                                        <span class="sr-only">Close</span>
                                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
                                    </button>
                                </div>
                            </div>
                            `,s="hs-toastify-on:opacity-100 opacity-0 fixed -top-37.5 right-5 z-90 transition-all duration-300 w-80 bg-gray-200 text-sm text-gray-900 border border-gray-900 rounded-lg shadow-lg [&>.toast-close]:hidden dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400"),n({text:o,close:!0,escapeMarkup:!1,className:s,duration:t.duration}).showToast()}}},computed:{notifications(){return this.notificationState.notifications}},mounted(){window.tostifyCustomClose=function(i){const e=i.closest(".toastify");e&&(e.classList.remove("hs-toastify-on:opacity-100"),e.classList.add("opacity-0","scale-95"),setTimeout(()=>{const t=e.querySelector(".toast-close");t&&t.click()},300))}}};function l(i,e,t,o,s,c){return null}const u=r(a,[["render",l]]);export{u as N};
