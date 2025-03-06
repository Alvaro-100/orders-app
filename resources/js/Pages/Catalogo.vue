<script setup>
import { computed, onMounted, ref } from "vue";
import { usePage, Link } from "@inertiajs/vue3";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import "swiper/css/navigation";
import { Autoplay, Navigation } from "swiper/modules";

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { faBars } from "@fortawesome/free-solid-svg-icons";
import axios from "axios";
import Swal from "sweetalert2";
import { text } from "@fortawesome/fontawesome-svg-core";


const page = usePage();
const user = page.props.auth.user;


const productos = ref([]);

const destacados = ref([
  { id: 1, nombre: "Laptop Gamer", precio: 1200, imagen: "/images/slider/lapto.jpg" },
  { id: 2, nombre: "Monitor 4K", precio: 400, imagen: "/images/slider/monitor.jpg" },
  { id: 3, nombre: "Teclado Gamer", precio: 40, imagen: "/images/slider/teclado.jpg" },
]);

const categorias = ref([]);
const marcas = ref([]);
const filtroCategoria = ref("");
const filtroMarca = ref("");
const searchQuery = ref("");
const isOpen = ref(false);
const urlBase = 'http://localhost:8000/api/'
const orden = ref({
id:null,
fecha: new Date().toISOString().split("T")[0],
fecha_despacho: null,
estado: "R",
"total": 0.0,
user:{...user},
detalleOrdenes:[]
});

const cantidades = ref({}); //objeto para almacenar temp cantidades

const agregarOrden = (producto, cantidad) => {
  if (!user) {
    //alert("Debes estar autenticado para realizar una orden.");
    Swal.fire("debes estar autentificado para realizar la compra")
    return;
  }
  //hacemos el proceso para realizar el producto
  const nevoProducto = {...producto};
  const existe = orden.value.detalleOrdenes.find(item => item.producto.id === productos);
  if(existe){
    existe.cantidad += cantidad;
    Swal.fire({title: "Producto Agregado",
    text: `Se ha incrementado ${cantidad} unidades del producto ${producto.nombre}`,
    icon: "success"
  });
  }else{
    orden.value.detalleOrdenes.push({
    cantidad: cantidad,
    precio: producto.precio,
    producto: nevoProducto,
    subtotal: producto.precio * cantidad
    });
    Swal.fire({title: "Producto Agregado",
    text: `Se ha agregado  ${cantidad} ${producto.nombre}`,
    icon: "success"
  });
}
orden.value.total = totalOrden;
};


onMounted(()=>{
fetchMarcas();
fetchCategorias();
fetchProductos();

})


//funcion para enviar a guardar la orden
const confirmarOrden = async () =>{
  Swal.fire({
  title: "Estas seguro (a)?",
  text: "Deseas confirmar la orden, despues no se podra revertir!",
  icon: "question",
  showCancelButton: true,
  confirmButtonColor: "#3085d6",
  cancelButtonColor: "#d33",
  confirmButtonText: "#no!",
  confirmButtonText: "si, confirmar!"
}).then((result) => {
  if (result.isConfirmed) {
    try{
      const response = axios.post(`${urlBase}ordenes`, orden.value);
      //destructuramos la respuesta del backend
      const {message, orden:nuevaOrden} = response.data;
      Swal.fire({
        title: 'confirmado!',
        'text': `${message} con el número de ${nuevaOrden.correlativo}`,
        icon: 'success'
      });
      //seteamos el objeto orden 
      orden.value = {id:nul,
        fecha: new Date().toISOString().split("T")[0], //FORMATO YYY-MM-DD
        fecha_despacho: null, estado: "R", total:0.0, user:user, detalleOrdenes:[]};
    }catch(err){
      console.error("error al confirmar la orden", err);
    }
  }
});
};





// funciones para hacer consultas a la api
const fetchMarcas = async() => {
 try{
  const response =  await axios.get(`${urlBase}marcas`);
  marcas.value = response.data;
 }catch(err){
  console.error("error", err);
 }
}

const fetchCategorias = async() => {
 try{
  const response =  await axios.get(`${urlBase}categorias`);
  categorias.value = response.data;
 }catch(err){
  console.error("error", err);
 }
}

const fetchProductos = async() => {
 try{
  const response =  await axios.get(`${urlBase}productos`);
  productos.value = response.data;
 }catch(err){
  console.error("error", err);
 }
}


//propiedad computable para filtrar los productos
const filteredProducts = computed(() => {
    return productos.value.filter((producto) => {
        return (
            (!searchQuery.value || producto.nombre.toLowerCase().includes(searchQuery.value.toLocaleLowerCase()))
            && (!filtroCategoria.value || producto.categoria.nombre === filtroCategoria.value)
            && (!filtroMarca.value || producto.marca.nombre === filtroMarca.value)
        );
    });
});

///propieda computable para calcular el total de la orden 
const totalOrden = computed(()=>{
  return orden.value.detalleOrdenes.reduce((total,item) => total + item.producto.precio * item.cantidad,0);
})

//propieda computable para calcular el total de la orden
const deleteItem = (item) =>{
  const index = orden.value.detalleOrdenes.indexOf(item);
  orden.value.detalleOrdenes.splice(index,1);
}
</script>

<template>
  <div class="container mx-auto p-6">
    <nav class="fixed top-0 left-0 w-full bg-blue-700 text-white shadow-md z-50">
        <div class="container mx-auto flex flex-wrap justify-between items-center p-4">
            <h1 class="text-xl font-bold">TiendaTech</h1>
            <!-- Botón menú hamburguesa -->
            <button @click="isOpen = !isOpen" class="lg:hidden text-white focus:outline-none">
                <FontAwesomeIcon :icon="faBars" class="w-6 h-6 text-white" />
            </button>

            <!-- Menú principal -->
            <div :class="{'hidden': !isOpen, 'block': isOpen}" class="w-full lg:w-auto lg:flex lg:items-center">
            <div v-if="!user" class="flex flex-col lg:flex-row lg:space-x-4 text-center mt-4 lg:mt-0">
                <a href="/login" class="text-white py-2 lg:py-0">Iniciar Sesión</a>
                <a href="/register" class="text-white py-2 lg:py-0">Registrarse</a>
            </div>

            <div v-else class="flex flex-col lg:flex-row lg:items-center lg:space-x-4 text-center mt-4 lg:mt-0">
                <span class="py-2 lg:py-0">{{ user.name }}</span>
                <Link :href="route('logout')" method="post" as="button" class="bg-red-500 px-4 py-2 rounded w-full lg:w-auto">Cerrar Sesión</Link>
            </div>
            </div>
        </div>
    </nav>
    <div class="pt-8">
        <Swiper :modules="[Navigation, Autoplay]" navigation :autoplay="{ delay: 3000, disableOnInteraction: false }"
      :speed="1000" loop class="my-6">
      <SwiperSlide v-for="destacado in destacados" :key="destacado.id" class="p-4">
        <div class="bg-gray-800 text-white rounded-lg overflow-hidden shadow-lg">
          <img :src="destacado.imagen" class="w-full h-56 object-cover object-center" />
          <div class="p-4">
            <h2 class="text-xl font-semibold">{{ destacado.nombre }}</h2>
            <p class="text-yellow-400 text-lg font-bold">
              ${{ destacado.precio }}
            </p>
          </div>
        </div>
      </SwiperSlide>
    </Swiper>

    <div class="flex flex-wrap justify-between items-center my-6">
      <input type="text" v-model="searchQuery" placeholder="Buscar..."
        class="border p-2 rounded w-full md:w-1/3 mb-2 md:mb-0" />
      <select v-model="filtroCategoria" class="border p-2 rounded w-full md:w-auto mb-2 md:mb-0">
        <option value="">Categoría</option>
        <option v-for="cat in categorias" :key="cat">{{ cat.nombre }}</option>
      </select>
      <select v-model="filtroMarca" class="border p-2 rounded w-full md:w-auto">
        <option value="">Marca</option>
        <option v-for="marca in marcas" :key="marca">{{ marca.nombre }}</option>
      </select>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div v-for="producto in filteredProducts" :key="producto.id" class="bg-white rounded-lg shadow-lg overflow-hidden p-4">
        <Swiper :modules="[Navigation]" navigation class="h-40">
          <SwiperSlide v-for="img in producto.imagenes" :key="img">
            <img :src="`images/products/${img.nombre}`" class="w-full h-40 object-contain" />
          </SwiperSlide>
        </Swiper>
        <div class="p-4">
          <h2 class="text-xl font-semibold">{{ producto.nombre }}</h2>
          <span class="text-sm text-blue-600">{{ producto.descripcion }}</span>
          <p class="text-yellow-500 text-lg font-bold">
            ${{ producto.precio }}
          </p>
          <div class="flex flex-col md:flex-row md:space-x-4">
            <input type="number" min="1" :value="cantidades[producto.id] || 1" @input="cantidades[producto.id] = Number($event.target.value)"  class="border p-2 rounded w-full my-2 md:w-auto md:my-0" />
            <button @click="agregarOrden(producto, cantidades[producto.id] || 1)" class="bg-blue-500 text-white px-4 py-2 rounded w-full md:w-auto">
                Agregar a Orden
            </button>
          </div>
          
        </div>
      </div>
    </div>

    <div class="mt-6 flex justify-center">
      <button class="px-4 py-2 bg-gray-700 text-white rounded">Anterior</button>
      <button class="px-4 py-2 bg-gray-700 text-white rounded ml-4">
        Siguiente
      </button>
    </div>
    <!--Div para mostral el detalle de orden -->
    </div v-if="orden.detalleOrdenes?.lengt > 0" class="mt-6 py-6 bg-white rounded-lg shadow-lg">
    <h2 class="text-2x1 font-semibold mb-4 text-gray-700"> Resumen de la orden</h2>
    <table class="min-w-full bg-white border border-gray-300 shadow-md rounded-lg">
      <thead>
        <tr>
          <th class="px-4 py-2 text-left">Productos</th>
          <th class="px-4 py-2 text-left">Marca</th>
          <th class="px-4 py-2 text-left">Precio</th> 
          <th class="px-4 py-2 text-left">Cantidad</th>
          <th class="px-4 py-2 text-left">subtotal</th>
        </tr>
      </thead>
      <tbody >
        <tr v-for="item in orden.detalleOrdenes" :key="item.id" class="border-b">
          <td class="px-4 py-2">{{ item.producto.nombre }} - {{ item.producto.descripcion}}</td>
          <td class="px-4 py-2">{{ item.producto.marca.nombre }}</td>
          <td class="px-4 py-2">${{ item.producto.precio }}</td>
          <td class="px-4 py-2">${{ item.cantidad }}</td>
          <td class="px-4 py-2">${{ ((item.producto.precio ?? 0) * (item.cantidad ?? 0)) }}</td>
          <td>
            <button @click="deleteItem(item)" class="px-3 py-1 bg-red-500 text-white rounded :hover">

            </button>
          </td>
        </tr>
      </tbody>
      <tfoot class="bg-gray-100 font-semibold">
        <tr>
          <td colspan="4" class="px-4 py-3 text-right">Total</td>
          <td class="px-4  py-3 text-right text-blue-600">${{ (totalOrden ?? 0).toFixed(2) }}</td>

        </tr>
      </tfoot>
    </table>
    </div>
  <div class ="mt-4 text-right">
    <button @click="confirmarOrden" class="px-3 py-3 bg-green-700 text-white rounde font-semibold">
    </button>
  </div>
</template>