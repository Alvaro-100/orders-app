<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

import { ref, onMounted, computed } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';

//import { ProductService } from '@/service/ProductService';

onMounted(() => {
fetchProductos();
});



const toast = useToast();
const dt = ref();
const productos = ref([]);
const dialog = ref(false);//permite mostrar/ocultar el modal para agregar/editar productos
const deleteProductDialog = ref(false);
const producto = ref({});
const selectedProducts = ref();
const filters = ref({
    'global': {value: null, matchMode: FilterMatchMode.CONTAINS},
});
const submitted = ref(false);
const url = 'http://127.0.0.1:8000/api/productos';  

const statuses = ref([
    {label: 'INSTOCK', value: 'instock'},
    {label: 'LOWSTOCK', value: 'lowstock'},
    {label: 'OUTOFSTOCK', value: 'outofstock'}
]);

const formatCurrency = (value) => {
    if(value)
        return value.toLocaleString('en-US', {style: 'currency', currency: 'USD'});
    return;
};
const openNew = () => {
    producto.value = {};
    submitted.value = false;
    dialog.value = true;
};
const hideDialog = () => {
    dialog.value = false;
    submitted.value = false;
};
//funciones para hacer peticiones a la API
const fetchProductos = async()=>{
    try {
        const response = await axios.get(url,productos);
        productos.value = response.data;
    } catch (error) {
        console.log('Error al obtener los productos', err)
        
    }
}
const saveOrUpdate = () => {
    submitted.value = true;

    if (producto?.value.nombre?.trim()) {
        if (producto.value.id) {
            //se va actualizar el producto
            toast.add({severity:'success', summary: 'Successful', detail: 'Product Updated', life: 3000});
        }
        else {
            //se va agregar un nuevo producto
           toast.add({severity:'success', summary: 'Successful', detail: 'Product Created', life: 3000});
        }

        dialog.value = false;
        producto.value = {};
    }
};
const editProduct = (prod) => {
    producto.value = {...prod};
    dialog.value = true;
};
const confirmDeleteProduct = (prod) => {
    producto.value = {...prod};
    deleteProductDialog.value = true;
};
const deleteProduct = () => {
    productos.value = productos.value.filter(val => val.id !== producto.value.id);
    deleteProductDialog.value = false;
    producto.value = {};
    toast.add({severity:'success', summary: 'Successful', detail: 'Product Deleted', life: 3000});
};
const findIndexById = (id) => {
    let index = -1;
    for (let i = 0; i < productos.value.length; i++) {
        if (productos.value[i].id === id) {
            index = i;
            break;
        }
    }

    return index;
};

const exportCSV = () => {
    dt.value.exportCSV();
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'INSTOCK':
            return 'success';

        case 'LOWSTOCK':
            return 'warn';

        case 'OUTOFSTOCK':
            return 'danger';

        default:
            return null;
    }
};

 /*propiedades computables para hacer dinámico el titulo del formulario y 
        titulo del boton */
        const dialogTitle = computed(() =>
            producto.value.id ? "Edición de productos" : "Registro de productos"
        );
        const btnTitle = computed(() => (producto.value.id ? "Actualizar" : "Guardar"));
</script>

<template>
    <Head title="Productos" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800">
                Productos
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
               <!--Inicio template de productos - primevue -->
                        <div>
                    <div class="card">
                        <Toolbar class="mb-6">
                            <template #start>
                                <Button label="Nuevo Producto" icon="pi pi-plus" class="mr-2" @click="openNew" />
                            </template>

                            <template #end>
                                <FileUpload mode="basic" accept="image/*" :maxFileSize="1000000" label="Import" customUpload chooseLabel="Import" class="mr-2" auto :chooseButtonProps="{ severity: 'secondary' }" />
                                <Button label="Export" icon="pi pi-upload" severity="secondary" @click="exportCSV($event)" />
                            </template>
                        </Toolbar>

                        <DataTable
                            ref="dt"
                            v-model:selection="selectedProducts"
                            :value="productos"
                            dataKey="id"
                            :paginator="true"
                            :rows="10"
                            :filters="filters"
                            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                            :rowsPerPageOptions="[5, 10, 25, 50]"
                            currentPageReportTemplate="Mostrando del {first} al {last} de {totalRecords} productos"
                        >
                            <template #header>
                                <div class="flex flex-wrap gap-2 items-center justify-between">
                                    <h4 class="m-0">Gestion de Productos</h4>
                                    <IconField>
                                        <InputIcon>
                                            <i class="pi pi-search" />
                                        </InputIcon>
                                        <InputText v-model="filters['global'].value" placeholder="Search..." />
                                    </IconField>
                                </div>
                            </template>

                            <Column field="nombre" header="Producto" sortable style="min-width: 16rem"></Column>
                            <Column field="descripcion" header="Descripcion" sortable style="min-width: 16rem"></Column>
                            <Column field="marca.nombre" header="Marca" sortable style="min-width: 16rem"></Column>
                            <Column field="modelo" header="Modelo" sortable style="min-width: 16rem"></Column>
                            
                        
                            <Column field="precio" header="Precio" sortable style="min-width: 8rem">
                                <template #body="slotProps">
                                    {{ formatCurrency(slotProps.data.precio) }}
                                </template>
                            </Column>
                            <Column field="stock" header="Existencia" sortable style="min-width: 16rem"></Column>

                            <Column field="categoria.nombre" header="Categoria" sortable style="min-width: 10rem"></Column>
                          <!--
                            <Column field="inventoryStatus" header="Status" sortable style="min-width: 12rem">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.inventoryStatus" :severity="getStatusLabel(slotProps.data.inventoryStatus)" />
                                </template>
                            </Column>-->
                            <Column :exportable="false" style="min-width: 12rem">
                                <template #body="slotProps">
                                    <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editProduct(slotProps.data)" />
                                    <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteProduct(slotProps.data)" />
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                    <!--Modal para guadar o actualizar un producto-->
                    
                    <Dialog v-model:visible="dialog" :style="{ width: '450px' }" :header="dialogTitle" :modal="true">
                        <div class="flex flex-col gap-6">
                            <div>
                                <label for="nombre" class="block font-bold mb-3">Name</label>
                                <InputText id="nombre" v-model.trim="producto.nombre" required="true" autofocus :invalid="submitted && !producto.nombre" fluid />
                                <small v-if="submitted && !producto.nombre" class="text-red-500">Nombre es requerido</small>
                            </div>
                            <div class="field">
                                <label for="descripcion" class="block font-bold mb-3">Description</label>
                                <Textarea id="descripcion" v-model="producto.descripcion" required="true" rows="2" cols="20" fluid />
                            </div>
                        </div>
                        <!-- <div class="formgrid ">

                        </div> -->

                        <template #footer>
                            <Button label="Cancel" icon="pi pi-times" text @click="hideDialog" />
                            <Button label="Save" icon="pi pi-check" @click="saveOrUpdate" />
                        </template>
                    </Dialog>
                    
                </div>
               <!--Fin template de productos - primevue -->

            </div>
        </div>
    </AuthenticatedLayout>
</template>