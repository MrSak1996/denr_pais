```vue
<script setup>
import { computed } from 'vue';

// Province map images
import cavite from '../../images/cavite3.png';
import laguna from '../../images/laguna2.png';
import batangas from '../../images/batangas2.png';
import rizal from '../../images/rizal2.png';
import quezon from '../../images/quezon.png';



const props = defineProps({
    /*
    |--------------------------------------------------------------------------
    | Selected Province
    |--------------------------------------------------------------------------
    | Example:
    | "cavite"
    | "laguna"
    | "batangas"
    | "rizal"
    | "quezon"
    |--------------------------------------------------------------------------
    */
    province: {
        type: String,
        required: true,
    },

    /*
    |--------------------------------------------------------------------------
    | Municipality List
    |--------------------------------------------------------------------------
    */
    municipalities: {
        type: Array,
        default: () => [],
    },
});


/*
|--------------------------------------------------------------------------
| Province Configuration
|--------------------------------------------------------------------------
*/

const provinceConfig = {
    cavite: {
        key: 'cavite',
        name: 'CAVITE',
        map: cavite,
    },

    laguna: {
        key: 'laguna',
        name: 'LAGUNA',
        map: laguna,
    },

    batangas: {
        key: 'batangas',
        name: 'BATANGAS',
        map: batangas,
    },

    rizal: {
        key: 'rizal',
        name: 'RIZAL',
        map: rizal,
    },

    quezon: {
        key: 'quezon',
        name: 'QUEZON',
        map: quezon,
    },
};


/*
|--------------------------------------------------------------------------
| Current Province
|--------------------------------------------------------------------------
*/

const currentProvince = computed(() => {
    return (
        provinceConfig[props.province?.toLowerCase()] ||
        provinceConfig.cavite
    );
});


/*
|--------------------------------------------------------------------------
| Municipality Columns
|--------------------------------------------------------------------------
|
| This follows your old PHP:
|
| if ($key > 21)
| if ($key <= 21)
|
|--------------------------------------------------------------------------
*/

const municipalityColumn1 = computed(() => {
    return props.municipalities.filter((_, index) => index > 21);
});

const municipalityColumn2 = computed(() => {
    return props.municipalities.filter((_, index) => index <= 21);
});
</script>


<template>

    <!-- =========================================================
         SELECTED PROVINCE
    ========================================================== -->

    <div class="w-full">

        <!-- =====================================================
             HEADER
        ====================================================== -->

        <div class="rounded-t-md px-4 py-3 text-white" style="background-color: #009688;">

            <h5 class="m-0 text-base font-semibold">
                {{ currentProvince.name }}
            </h5>

        </div>


        <!-- =====================================================
             BODY
        ====================================================== -->

        <div class="p-4" style="background-color: #e2e3e345;">

            <!-- =================================================
                 MAP
            ================================================== -->

            <div class="flex justify-center">

                <div class="w-full max-w-[350px]">

                    <img :src="currentProvince.map" :alt="currentProvince.name" class="mx-auto h-auto max-w-full" />

                </div>

            </div>


            <!-- =================================================
                 MUNICIPALITIES
            ================================================== -->

            <div class="mt-4" style="font-size: 10pt;">

                <div class="grid grid-cols-1 gap-x-4 md:grid-cols-2">

                    <!-- =========================================
                         MUNICIPALITY COLUMN 1
                    ========================================== -->

                    <div>

                        <ul class="m-0 list-none p-0">

                            <li v-for="( municipality, index ) in municipalityColumn1" :key="`municipality-1-${index}`" class="mb-1 flex items-center">

                                <i class="far fa-circle mr-2 text-warning"></i>

                                <b>
                                    {{ municipality }}
                                </b>

                            </li>

                        </ul>

                    </div>


                    <!-- =========================================
                         MUNICIPALITY COLUMN 2
                    ========================================== -->

                    <div>

                        <ul class="m-0 list-none p-0">

                            <li v-for="(municipality,index ) in municipalityColumn2" :key="`municipality-2-${index}`"
                                class="mb-1 flex items-center">

                                <i class="far fa-circle mr-2 text-warning"></i>

                                <b>
                                    {{ municipality }}
                                </b>

                            </li>

                        </ul>

                    </div>

                </div>

            </div>

        </div>

    </div>

</template>

