<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { usePage, Link} from "@inertiajs/vue3";
/* import { LogOutput } from 'concurrently'; */ 

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { faBars, faHome, faBox, faClipboardList, faFileAlt, faLayerGroup, faTimes, faUserCircle, faSignOutAlt } from "@fortawesome/free-solid-svg-icons";
import axios from 'axios';

 
const page = usePage();
const user = page.props.auth.user;
 const isSidebarOpen = ref([false]);
 const anioActual = ref(new Date().getFullYear());

 //funciones para la logica 
 const logout = async () =>{
    try{
        await axios.post('/logout');
        Window.location.href = '/login';
    }catch(err){
        console.error("Error al cerrar la sección, err");

    }
 };

</script>

<template>
    <div class="h-screen flex flex-col">
        <!--navbar-->
        <header class="bg-blue-700 text-white shadow-md fixed top-0 w-full z">
            <div class="px-6 py-4 flex justify-between items-center">
                <!--Boton para ocultar -->
                <button @click="isSidebarOpen = !isSidebarOpen" class="text-white md:hidden">
                    <FontAwesomeIcon :icon="faBars" />
                </button>
                <div class="text-font-semibold">Dashboard</div>
                <!--Datos de la sección-->
                <div class="flex items-center space-x-4">
                    <FontAwesomeIcon :icon="faUserCircle" class="text-2x1 " />
                    <div>
                        <p class="text-sm font-semibold">{{user.name}}</p>
                    </div>
                    <button @click="logout" class="text-white hover:bg-gray-200">
                        <FontAwesomeIcon :icon="faSignOutAlt"/>
                    </button>
                </div>
            </div>
        </header>
        <!--div para sidebar y contenido dinámico-->
        <div class="flex flex-1 pt-1">
            <!--Sidebar-->
            <aside :class="{
                '-translate-x-full md:translate-x-0': !isSidebarOpen,
                'translate-x-0' : isSidebarOpen,
            }"
            class="fixed md:relative top-16 left-0 h-[calc(100vh-64px)] w-64 bg-blue-700
            text-white transform  transition-transform duration-300 aese-in-out shadow-lg z-40"
            >
            <div class="px-4 text-xl font-semibold flex justify-between items-center ">
                <span>Menu</span>
                <button @click="isSidebarOpen = false">
                    <FontAwesomeIcon :icon="faBars" />
                </button>
            </div>
            <nav class="mt-4">
                <ul>
                    <li class="px-6 py-3 hover:bg-blue-500 flex items-center">
                        <a :href="route('dashboard')" class="flex items-center w-full">
                            <FontAwesomeIcon :icon="faHome" class="mr-3" />
                            Inicio
                        </a>
                    </li>
                    <li class="px-6 py-3 hover:bg-blue-500 flex items-center">
                        <a :href="route('marcas')" class="flex items-center w-full">
                            <FontAwesomeIcon :icon="faLayerGroup" class="mr-3" />
                            Catalogos
                        </a>
                    </li>
                    <li class="px-6 py-3 hover:bg-blue-500 flex items-center">
                        <a href="#" class="flex items-center w-full">
                            <FontAwesomeIcon :icon="faBox" class="mr-3" />
                            Productos
                        </a>
                    </li>
                    <li class="px-6 py-3 hover:bg-blue-500 flex items-center">
                        <a href="#" class="flex items-center w-full">
                            <FontAwesomeIcon :icon="faClipboardList" class="mr-3" />
                            ordenes
                        </a>
                    </li>
                    <li class="px-6 py-3 hover:bg-blue-500 flex items-center">
                        <a href="#" class="flex items-center w-full">
                            <FontAwesomeIcon :icon="faFileAlt" class="mr-3" />
                            Reportes
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        <!--Div para el contenido dinámico-->
        <main class="flex-1 p-6 overflow-auto border-t-gray-100">
            <slot></slot>
        </main>
        </div>
        <!--fin div para sidebar y contenido dinamico -->
        <footer class="bg bg-gray-700 text-white text-center py-3 shadow-md bottom-0 left-0 w-full">
            &copy; {{ new  Date().getFullYear() }} Todos los derechos reservados
        </footer>
    </div>
</template>

