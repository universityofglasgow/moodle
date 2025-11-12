<template>
     <DebugDisplay :debug="debug"></DebugDisplay>

    <div id="managemaps">
        <h2>{{ mstrings.conversionmaps }}</h2>

        <div class="alert alert-info" v-html="mstrings.conversionhelp"></div>

        <!-- show available maps -->
        <div v-if="!editmap && loaded">
            <div v-if="!maps.length" class="alert alert-warning">
                {{ mstrings.noconversionmaps }}
            </div>

            <EasyDataTable 
                v-if="loaded" 
                :headers="headers" 
                :items="maps"
                ref="dataTable"
                hide-footer
            >
                <template #item-inuse="map">
                    <span v-if="map.inuse">{{ mstrings.yes }}</span>
                    <span v-else>{{ mstrings.no }}</span>
                </template>
                <template #item-actions="map">
                    <div class="btn-group" role="group" aria-label="Actions">
                        <button v-if="caneditgrades" class="btn btn-outline-primary btn-sm mr-1" @click="edit_clicked(map.id)">{{ mstrings.edit }}</button>
                        <button v-if="!caneditgrades" class="btn btn-outline-primary btn-sm mr-1" @click="edit_clicked(map.id)">{{ mstrings.view }}</button>
                        <button v-if="caneditgrades" class="btn btn-outline-danger btn-sm mr-1" :class="{ disabled: map.inuse }" :disabled="map.inuse" @click="delete_clicked(map.id)">{{ mstrings.delete }}</button>
                        <button class="btn btn-outline-secondary btn-sm mr-1" @click="export_clicked(map.id)">{{ mstrings.export }}</button>
                    </div>
                </template>
            </EasyDataTable>

            <!-- Implementation of our own accessible footer. -->
            <CustomPagination
                v-if="loaded"
                v-bind="props"
            />

            <div v-if="caneditgrades" class="mt-4">
                <button class="btn btn-primary mr-1" @click="add_map">{{ mstrings.addconversionmap }}</button>
                <button class="btn btn-info" @click="import_clicked">{{ mstrings.importconversionmap }}</button>
            </div>
        </div>

        <!-- Map creation/editing -->
        <div v-if="editmap">
            <EditMap :mapid="editmapid" :caneditgrades="caneditgrades" @close="editmap_closed"></EditMap>
        </div>
    </div>

    <!-- Modal for delete confirm -->
    <ConfirmModal :show="showconfirm" :message="mstrings.deletemapconfirm" @confirm="confirmdelete"></ConfirmModal>

    <!-- Modal for map upload -->
    <VueModal v-model="showimportmodal" :enableClose="false" modalClass="col-11 col-lg-6 rounded" :title="mstrings.importconversionmap">
        <div class="p-4 mb-3 border rounded">
            <button class="btn btn-primary mr-1" type="button" @click="open()">
                Choose files
            </button>
            <button class="btn btn-warning" type="button" :disabled="!files" @click="reset()">
                Reset
            </button>
            <div class="mt-2" v-if="files">
                <p>You have selected: <b>{{ `${files.length} ${files.length === 1 ? 'file' : 'files'}` }}</b></p>
                <li v-for="file of files" :key="file.name">
                    {{ file.name }}
                </li>
            </div>
        </div>
        <button class="btn btn-info mr-1" @click="process_import">{{ mstrings.import }}</button>
        <button class="btn btn-warning" @click="showimportmodal = false">{{ mstrings.cancel }}</button>
    </VueModal>
</template>

<script setup>
    import {ref, computed, inject, onMounted} from '@vue/runtime-core';
    import { useToast } from "vue-toastification";
    import EditMap from '@/components/Conversion/EditMap.vue';
    import ConfirmModal from '@/components/ConfirmModal.vue';
    import { saveAs } from 'file-saver';
    import { useFileDialog } from '@vueuse/core';
    import DebugDisplay from '@/components/DebugDisplay.vue';
    import CustomPagination from '@/components/CustomPagination.vue';

    const maps = ref([]);
    const editmap = ref(false);
    const editmapid = ref(0);
    const loaded = ref(false);
    const showconfirm = ref(false);
    const deletemapid = ref(0);
    const showimportmodal = ref(false);
    const mstrings = inject('mstrings');
    const debug = ref({});
    const toast = useToast();
    const headers = ref([]);
    const caneditgrades = ref(false);
    // pagination related.
    const dataTable = ref();
    const props = {
        dataTable: dataTable,
    }

    const { files, open, reset } = useFileDialog({
        accept: 'text/json', // Set to accept only json files
        multiple: false,
        directory: false, // Select directories instead of files if set true
    });

    /**
     * Get/update the maps
     */
    function get_maps() {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_get_conversion_maps',
            args: {
                courseid: courseid,
            }
        }])[0]
        .then((result) => {
            maps.value = result;
            loaded.value = true;
        })
        .catch((error) => {
            window.console.error(error);
            debug.value = error;
        });
    }

    /**
     * Edit button was clicked
     */
    function edit_clicked(mapid) {
        editmapid.value = mapid;
        editmap.value = true;
    }

    /**
     * Import button clicked
     */
    function import_clicked() {

        // Clear any list of import files
        reset();

        showimportmodal.value = true;
    }

    /**
     * Import json map
     */
    function process_json(jsonmap) {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_import_conversion_map',
            args: {
                courseid: courseid,
                jsonmap: jsonmap
            }
        }])[0]
        .then(() => {
            get_maps();
        })
        .catch((error) => {
            window.console.error(error);
            debug.value = error;
        });
    }

    /**
     * Import button on modal clicked
     * Proces selected file.
     */
    function process_import() {
        if (!files.value) {
            toast.warning('No file to import');
            return;
        }

        const file = files.value[0];
        const reader = new FileReader();
        reader.addEventListener('load', (event) => {
            const jsondata = event.target.result;

            process_json(jsondata);
            showimportmodal.value = false;
        });
        reader.readAsText(file);
    }

    /**
     * EditMap was closed
     */
    function editmap_closed() {
        editmap.value = false;
        get_maps();
    }

    /**
     * Export button was clicked
     */
    function export_clicked(mapid) {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        fetchMany([{
            methodname: 'local_gugrades_get_conversion_map',
            args: {
                courseid: courseid,
                mapid: mapid,
                schedule: '',
            }
        }])[0]
        .then((result) => {
            const json = JSON.stringify(result, null, 4);
            let filename = result.name + '.json';
            filename = filename.replace(/[/\\?%*:|"<>]/g, '-');
            const blob = new Blob([json], {type: 'text/json;charset=utf-8'});
            saveAs(blob, filename);
            toast.success('Map exported');
        })
        .catch((error) => {
            window.console.error(error);
            debug.value = error;
        });
    }

    /**
     * Get all the maps for this course
     */
    onMounted(() => {
        const GU = window.GU;
        const courseid = GU.courseid;
        const fetchMany = GU.fetchMany;

        headers.value = [
            {text: mstrings.name, value: 'name'},
            {text: mstrings.scalehead, value: 'scale'},
            {text: mstrings.maxgrade, value: 'maxgrade'},
            {text: mstrings.createdby, value: 'createdby'},
            {text: mstrings.createdat, value: 'createdat'},
            {text: mstrings.inuse, value: 'inuse'},
            {text: '', value: 'actions'},
        ];

        // Check editing capability
        fetchMany([{
            methodname: 'local_gugrades_has_capability',
            args: {
                courseid: courseid,
                capability: 'local/gugrades:editgrades'
            }
        }])[0]
        .then((result) => {
            caneditgrades.value = result.hascapability;
        })
        .catch((error) => {
            window.console.log(error);
            debug.value = error;
        });

        get_maps();
    });

    /**
     * Add map button has been pressed
     */
    function add_map() {
        editmap.value = true;
        editmapid.value = 0;
    }

    /**
     * Delete map button has been clicked
     */
    function delete_clicked(mapid) {
        showconfirm.value = true;
        deletemapid.value = mapid;
    }

    /**
     * Confirm modal for deletion clicked
     */
    function confirmdelete(confirm) {

        if (confirm) {
            const GU = window.GU;
            const courseid = GU.courseid;
            const fetchMany = GU.fetchMany;

            fetchMany([{
                methodname: 'local_gugrades_delete_conversion_map',
                args: {
                    courseid: courseid,
                    mapid: deletemapid.value,
                }
            }])[0]
            .then(() => {
                get_maps();
            })
            .catch((error) => {
                window.console.error(error);
                debug.value = error;
            });
        }

        showconfirm.value = false;
    }
</script>