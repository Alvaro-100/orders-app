<script setup>
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import { Head } from '@inertiajs/vue3';
  
    import { ref, onMounted,computed } from 'vue';
    import { FilterMatchMode } from '@primevue/core/api';
    import { Toast } from 'primevue';
    import { useToast } from 'primevue/usetoast';
    import Button from "primevue/button"        
    import Toolbar from 'primevue/toolbar';
    import DataTable from 'primevue/datatable';
    import Column from 'primevue/column';
    import Dialog from 'primevue/dialog';
    import InputText from 'primevue/inputtext';
    import IconField from 'primevue/iconfield';
    import InputIcon from 'primevue/inputicon';
    import axios from 'axios';
    

    onMounted(() => {
        fetchMarcas();     
    });

        const toast = useToast();
        const dt = ref();
        const marcas = ref([]);
        const dialog = ref(false);
        const deleteMarcaDialog = ref(false);
        const marca = ref({});
        const selectedMarcas = ref();
        const filters = ref({
            'global': {value: null, matchMode: FilterMatchMode.CONTAINS},
        });
        const submitted = ref(false);
        const url = 'http://127.0.0.1:8000/api/marcas';    
       
        const openNew = () => {
            marca.value = {};
            submitted.value = false;
            dialog.value = true;
        };
        const hideDialog = () => {
            dialog.value = false;
            submitted.value = false;
        };
        //método para obtener las marcas
        const fetchMarcas = async () => {
            try{
                const response = await axios.get(url);
                marcas.value = response.data;
              
            }catch(err){
                console.error('Error al obtener las Marcas', err);
            }
        }
        const saveOrUpdate = () => {
            submitted.value = true;

            if (marca?.value.nombre?.trim()) {
                if (marca.value.id) {
                    //se va actualizar el registro
                    
                    toast.add({severity:'success', summary: 'Successful', detail: 'Product Updated', life: 3000});
                }
                else {
                    //proceso para nuevo registro
                    
                    toast.add({severity:'success', summary: 'Successful', detail: 'Product Created', life: 3000});
                }

                dialog.value = false;
                marca.value = {};
            }
        };
        const editMarca = (item ) => {
            
            marca.value = {...item};
            dialog.value = true;
        };
        const confirmDeleteMarca = (m) => {
            marca.value = m;
            deleteMarcaDialog.value = true;
        };
        const deleteMarca = () => {
            marcas.value = marcas.value.filter(val => val.id !== marca.value.id);
            deleteMarcaDialog.value = false;
            marca.value = {};
            toast.add({severity:'success', summary: 'Successful', detail: 'Product Deleted', life: 3000});
        };
       
        const exportCSV = () => {
            dt.value.exportCSV();
        };

        /*propiedades computables para hacer dinámico el titulo del formulario y 
        titulo del boton */
        const dialogTitle = computed(() =>
            marca.value.id ? "Edición de Marcas" : "Registro de Marcas"
        );
        const btnTitle = computed(() => (marca.value.id ? "Actualizar" : "Guardar"));
</script>

<template>
    <Head title="Marcas" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                CRUD de Marcas
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
               
                <div>
                    <div class="card">
                        <Toolbar class="mb-6">
                            <template #start>
                                <Button label="Nuevo" icon="pi pi-plus" class="mr-2" @click="openNew" />
                            </template>

                            <template #end>
                                <Button label="Exportar" icon="pi pi-upload" severity="secondary" @click="exportCSV($event)" />
                            </template>
                        </Toolbar>

                        <DataTable
                            ref="dt"
                            v-model:selection="selectedMarcas"
                            :value="marcas"
                            dataKey="id"
                            :paginator="true"
                            :rows="10"
                            :filters="filters"
                            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                            :rowsPerPageOptions="[5, 10, 25]"
                            currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} marcas"
                        >
                            <template #header>
                                <div class="flex flex-wrap gap-2 items-center justify-between">
                                    <h4 class="m-0">Gestión de Marcas</h4>
                                    <IconField>
                                        <InputIcon>
                                            <i class="pi pi-search" />
                                        </InputIcon>
                                        <InputText v-model="filters['global'].value" placeholder="Search..." />
                                    </IconField>
                                </div>
                            </template>
                            <Column field="nombre" header="Marca" sortable style="min-width: 16rem"></Column>
                            <Column :exportable="false" style="min-width: 12rem">
                                <template #body="slotProps">
                                    <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editMarca(slotProps.data)" />
                                    <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteMarca(slotProps.data)" />
                                </template>
                            </Column>
                        </DataTable>
                    </div>

                    <Dialog v-model:visible="dialog" :style="{ width: '450px' }" :header="dialogTitle" :modal="true">
                        <div class="flex flex-col gap-6">
                            <div>
                                <label for="nombre" class="block font-bold mb-3">Nombre Marca</label>
                                <InputText id="nombre" v-model.trim="marca.nombre" required="true" autofocus :invalid="submitted && !marca.nombre" fluid />
                                <small v-if="submitted && !marca.nombre" class="text-red-500">Nombre es requerido.</small>
                            </div>
                        </div>

                        <template #footer>
                            <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
                            <Button :label="btnTitle" icon="pi pi-check" @click="saveOrUpdate" />
                        </template>
                    </Dialog>

                    <Dialog v-model:visible="deleteMarcaDialog" :style="{ width: '450px' }" header="Confirmación" :modal="true">
                        <div class="flex items-center gap-4">
                            <i class="pi pi-exclamation-triangle !text-3xl" />
                            <span v-if="marca"
                                >Seguro(a) de eliminar la marca <b>{{ marca.nombre }}</b
                                >?</span
                            >
                        </div>
                        <template #footer>
                            <Button label="No" icon="pi pi-times" text @click="deleteMarcaDialog = false" />
                            <Button label="Si" icon="pi pi-check" @click="deleteMarca" />
                        </template>
                    </Dialog>
                
                </div> 
            </div>
        </div>

    </AuthenticatedLayout>
</template>
