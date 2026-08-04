<template>

    <Head title="DENR Online Protected Area Information System" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4">
                <div class="flex items-center justify-between flex-wrap gap-4">

                    <!-- LEFT -->
                    <div class="flex items-center gap-3">

                        <div class="w-9 h-9 rounded-lg bg-[#1a6b45] flex items-center justify-center shrink-0">

                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="white"
                                stroke-width="2.2">

                                <path
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                </path>
                            </svg>
                        </div>

                        <div>

                            <h5 class="text-[15px] font-bold text-gray-900">
                                Monitoring of Protected Area Patrol Plans in {{ props.pa_code }}
                            </h5>

                            <p class="text-xs text-gray-400 mt-0.5">
                                Real-time tracking of patrol plans
                            </p>

                        </div>
                    </div>

                    <!-- RIGHT -->
                    <div class="flex items-center">

                       
                    </div>
                </div>
            <div class="box">
                <div ref="mapDiv" class="map-container">

                    <!-- =========================================================
             PATROL MAP LEGEND
             ========================================================= -->
                    <div class="patrol-legend">

                        <div class="legend-title">
                            Patrol Map
                        </div>

                        <div class="legend-item">

                            <span class="legend-line patrol-line"></span>

                            <span>
                                Patrol / Transect Route
                            </span>

                        </div>


                        <div class="legend-item">

                            <span class="legend-circle start-circle"></span>

                            <span>
                                Patrol Start
                            </span>

                        </div>


                        <div class="legend-item">

                            <span class="legend-circle end-circle"></span>

                            <span>
                                Patrol End
                            </span>

                        </div>


                        <div class="legend-item">

                            <span class="legend-line protected-line"></span>

                            <span>
                                Protected Area
                            </span>

                        </div>

                    </div>


                    <!-- =========================================================
             MAP TITLE
             ========================================================= -->
                    <div class="map-title">

                        <div class="map-title-main">
                            PATROL MAP
                        </div>

                        <div class="map-title-sub">
                            Protected Area Patrol / Transect Walk
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </AppLayout>

</template>


<script setup lang="ts">

import {
    ref,
    onMounted
} from "vue";


/*
|--------------------------------------------------------------------------
| ArcGIS
|--------------------------------------------------------------------------
*/

import Map from "@arcgis/core/Map";

import MapView from
    "@arcgis/core/views/MapView";

import GraphicsLayer from
    "@arcgis/core/layers/GraphicsLayer";

import Graphic from
    "@arcgis/core/Graphic";


/*
|--------------------------------------------------------------------------
| Geometry
|--------------------------------------------------------------------------
*/

import Polygon from
    "@arcgis/core/geometry/Polygon";

import Polyline from
    "@arcgis/core/geometry/Polyline";

import Point from
    "@arcgis/core/geometry/Point";


/*
|--------------------------------------------------------------------------
| Symbols
|--------------------------------------------------------------------------
*/

import SimpleFillSymbol from
    "@arcgis/core/symbols/SimpleFillSymbol";

import SimpleLineSymbol from
    "@arcgis/core/symbols/SimpleLineSymbol";

import SimpleMarkerSymbol from
    "@arcgis/core/symbols/SimpleMarkerSymbol";


/*
|--------------------------------------------------------------------------
| Zoom
|--------------------------------------------------------------------------
*/

import Zoom from
    "@arcgis/core/widgets/Zoom";


/*
|--------------------------------------------------------------------------
| PROPS
|--------------------------------------------------------------------------
*/
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';const props = defineProps<{

    pa_id: number;
    pa_code: string;

    geojson: {

        type: string;

        features: any[];

    };

}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Protected Area Patrol Monitoring',
        href: '/lawin_monitoring/index',
    },
];


/*
|--------------------------------------------------------------------------
| MAP
|--------------------------------------------------------------------------
*/

const mapDiv =
    ref<HTMLDivElement | null>(null);


let view: MapView;

let graphicsLayer:
    GraphicsLayer;


/*
|--------------------------------------------------------------------------
| FILES
|--------------------------------------------------------------------------
*/



/*
|--------------------------------------------------------------------------
| PATROL GEOJSON BY PROTECTED AREA ID
|--------------------------------------------------------------------------
*/

const PATROL_FILES: Record<number, string[]> = {

    // ============================================================
    // PA ID 4
    // Multiple patrol / transect GeoJSON files
    // ============================================================
    4: [
        "/BRWFR-07072026.json",
        "/BRWFR-07162026.json",
    ],
    12:[
        "/MIGRBS.json",
    ],

    // ============================================================
    // PA ID 17
    // ============================================================
    17: [
        "/Rizal_2026_line.json",
    ],

    // ============================================================
    // Examples
    // ============================================================
    // 20: [
    //     "/PA20-Day1.json",
    //     "/PA20-Day2.json",
    //     "/PA20-Day3.json",
    // ],

};


/*
|--------------------------------------------------------------------------
| GET PATROL FILE FOR CURRENT PA
|--------------------------------------------------------------------------
*/

function getPatrolFiles(): string[] {

    const files = PATROL_FILES[props.pa_id];

    if (!files || files.length === 0) {

        console.warn(
            `No patrol GeoJSON configured for pa_id: ${props.pa_id}`
        );

        return [];
    }

    console.log(
        `PA ID ${props.pa_id} → ${files.length} patrol file(s)`,
        files
    );

    return files;
}


/*
|--------------------------------------------------------------------------
| COORDINATE SYSTEM
|--------------------------------------------------------------------------
|
| Your patrol shapefile/map indicates:
|
| WGS 1984
| UTM Zone 51N
|
| EPSG / WKID = 32651
|
*/

const TRANSECT_WKID =
    32651;


/*
|--------------------------------------------------------------------------
| LOAD PROTECTED AREA
|--------------------------------------------------------------------------
*/

async function loadProtectedArea() {

    console.log(
        "Loading protected area..."
    );


    const data =
        props.geojson;


    if (
        !data ||
        !data.features ||
        !data.features.length
    ) {

        console.warn(
            "No protected area geometry found."
        );

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | LOOP THROUGH PROTECTED AREA FEATURES
    |--------------------------------------------------------------------------
    */

    data.features.forEach(
        (feature: any) => {

            if (
                !feature.geometry
            ) {

                return;

            }


            const geometry =
                feature.geometry;


            const properties =
                feature.properties || {};


            /*
            |--------------------------------------------------------------------------
            | POLYGON
            |--------------------------------------------------------------------------
            */

            if (
                geometry.type ===
                "Polygon"
            ) {

                const polygon =
                    new Polygon({

                        rings:
                            geometry.coordinates,

                        spatialReference: {
                            wkid: 4326
                        }

                    });


                addProtectedAreaGraphic(
                    polygon,
                    properties
                );

            }


            /*
            |--------------------------------------------------------------------------
            | MULTIPOLYGON
            |--------------------------------------------------------------------------
            */

            else if (
                geometry.type ===
                "MultiPolygon"
            ) {

                const rings: any[] =
                    [];


                geometry.coordinates.forEach(
                    (polygon: any) => {

                        polygon.forEach(
                            (ring: any) => {

                                rings.push(
                                    ring
                                );

                            }
                        );

                    }
                );


                const polygon =
                    new Polygon({

                        rings:
                            rings,

                        spatialReference: {
                            wkid: 4326
                        }

                    });


                addProtectedAreaGraphic(
                    polygon,
                    properties
                );

            }


            else {

                console.warn(
                    "Unsupported protected-area geometry:",
                    geometry.type
                );

            }

        }
    );

}


/*
|--------------------------------------------------------------------------
| ADD PROTECTED AREA GRAPHIC
|--------------------------------------------------------------------------
*/

function addProtectedAreaGraphic(
    polygon: Polygon,
    properties: any
) {

    const graphic =
        new Graphic({

            geometry:
                polygon,

            symbol:
                new SimpleFillSymbol({

                    /*
                    | Transparent fill
                    */

                    color: [
                        120,
                        200,
                        220,
                        0.12
                    ],

                    outline: {

                        color: [
                            5,
                            9,
                            112,
                            1
                        ],

                        width: 2

                    }

                }),

            attributes:
                properties,

            popupTemplate: {

                title:
                    "{NAME}",

                content: `

                    <b>Protected Area:</b>
                    {NAME}
                    <br><br>

                    <b>Designation:</b>
                    {DESIG}
                    <br>

                    <b>Status:</b>
                    {STATUS}
                    <br>

                    <b>Area:</b>
                    {GIS_AREA}

                `

            }

        });


    graphicsLayer.add(
        graphic
    );

}


/*
|--------------------------------------------------------------------------
| LOAD REAL PATROL ROUTES
|--------------------------------------------------------------------------
*/

async function loadTransects() {

    try {

        console.log(
            "================================"
        );

        console.log(
            "Loading MULTIPLE patrol routes..."
        );

        console.log(
            "================================"
        );


        const patrolFiles =
            getPatrolFiles();


        if (!patrolFiles.length) {

            console.warn(
                "No patrol files available for this protected area."
            );

            return;

        }


        let totalRoutes = 0;


        /*
        |--------------------------------------------------------------------------
        | LOAD EVERY GEOJSON FILE
        |--------------------------------------------------------------------------
        */

        for (
            const patrolFile of patrolFiles
        ) {

            console.log(
                "Loading patrol file:",
                patrolFile
            );


            try {

                const response =
                    await fetch(patrolFile);


                if (!response.ok) {

                    console.error(
                        `Failed to load ${patrolFile}: ${response.status}`
                    );

                    continue;

                }


                const data =
                    await response.json();


                if (
                    !data.features ||
                    !data.features.length
                ) {

                    console.warn(
                        `No patrol routes found in ${patrolFile}`
                    );

                    continue;

                }


                console.log(
                    `${patrolFile}: ${data.features.length} feature(s)`
                );


                /*
                |--------------------------------------------------------------------------
                | DRAW EACH FEATURE
                |--------------------------------------------------------------------------
                */

                data.features.forEach(
                    (
                        feature: any,
                        index: number
                    ) => {

                        if (
                            !feature.geometry
                        ) {

                            return;

                        }


                        const geometry =
                            feature.geometry;


                        const properties =
                            feature.properties || {};


                        let polyline:
                            Polyline | null =
                            null;


                        /*
                        |--------------------------------------------------------------------------
                        | LINESTRING
                        |--------------------------------------------------------------------------
                        */

                        if (
                            geometry.type ===
                            "LineString"
                        ) {

                            polyline =
                                new Polyline({

                                    paths:
                                        geometry.coordinates,

                                    spatialReference: {

                                        wkid:
                                            TRANSECT_WKID

                                    }

                                });

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | MULTILINESTRING
                        |--------------------------------------------------------------------------
                        */

                        else if (
                            geometry.type ===
                            "MultiLineString"
                        ) {

                            polyline =
                                new Polyline({

                                    paths:
                                        geometry.coordinates,

                                    spatialReference: {

                                        wkid:
                                            TRANSECT_WKID

                                    }

                                });

                        }


                        else {

                            console.warn(
                                "Unsupported patrol geometry:",
                                geometry.type,
                                patrolFile
                            );

                            return;

                        }


                        if (!polyline) {

                            return;

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | DETERMINE PATROL COLOR
                        |--------------------------------------------------------------------------
                        |
                        | You can customize this based on the file.
                        |
                        */

                        let lineColor:
                            number[];


                        /*
                        | Day 1
                        */

                        if (
                            patrolFile.includes(
                                "0707"
                            )
                        ) {

                            lineColor = [
                                255,
                                0,
                                0,
                                1
                            ];

                        }

                        /*
                        | Day 2
                        */

                        else if (
                            patrolFile.includes(
                                "0716"
                            )
                        ) {

                            lineColor = [
                                0,
                                90,
                                255,
                                1
                            ];

                        }

                        /*
                        | Default
                        */

                        else {

                            lineColor = [
                                7,
                                77,
                                23,
                                1
                            ];

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | PATROL ROUTE
                        |--------------------------------------------------------------------------
                        */

                        const graphic =
                            new Graphic({

                                geometry:
                                    polyline,

                                symbol:
                                    new SimpleLineSymbol({

                                        color:
                                            lineColor,

                                        width:
                                            3,

                                        style:
                                            "solid"

                                    }),

                                attributes: {

                                    ...properties,

                                    source_file:
                                        patrolFile,

                                    pa_id:
                                        props.pa_id

                                },

                                popupTemplate: {

                                    title:
                                        "Patrol / Transect Walk",

                                    content: [

                                        {

                                            type:
                                                "fields",

                                            fieldInfos: [

                                                {

                                                    fieldName:
                                                        "Patrol_ID",

                                                    label:
                                                        "Patrol ID"

                                                },

                                                {

                                                    fieldName:
                                                        "Patrol_Tra",

                                                    label:
                                                        "Patrol Type"

                                                },

                                                {

                                                    fieldName:
                                                        "Patrol_Sta",

                                                    label:
                                                        "Start Date"

                                                },

                                                {

                                                    fieldName:
                                                        "Patrol_End",

                                                    label:
                                                        "End Date"

                                                },

                                                {

                                                    fieldName:
                                                        "Patrol_Leg",

                                                    label:
                                                        "Patrol Leg"

                                                },

                                                {

                                                    fieldName:
                                                        "Team",

                                                    label:
                                                        "Team"

                                                },

                                                {

                                                    fieldName:
                                                        "Leader",

                                                    label:
                                                        "Leader"

                                                },

                                                {

                                                    fieldName:
                                                        "Mandate",

                                                    label:
                                                        "Mandate"

                                                },

                                                {

                                                    fieldName:
                                                        "source_file",

                                                    label:
                                                        "Source File"

                                                }

                                            ]

                                        }

                                    ]

                                }

                            });


                        graphicsLayer.add(
                            graphic
                        );


                        totalRoutes++;

                    }
                );

            }

            catch (error) {

                console.error(
                    `Error loading patrol file ${patrolFile}:`,
                    error
                );

            }

        }


        console.log(
            "================================"
        );

        console.log(
            "TOTAL PATROL ROUTES:",
            totalRoutes
        );

        console.log(
            "================================"
        );

    }

    catch (error) {

        console.error(
            "Error loading patrol routes:",
            error
        );

    }

}


/*
|--------------------------------------------------------------------------
| CREATE START / END MARKERS
|--------------------------------------------------------------------------
|
| IMPORTANT:
|
| We get the first and last coordinate directly
| from the REAL patrol line.
|
| This means the marker uses exactly the same
| coordinate system as the route.
|
*/

async function loadPatrolStartEnd() {

    try {

        console.log(
            "================================"
        );

        console.log(
            "Creating patrol start/end markers..."
        );

        console.log(
            "================================"
        );


        const patrolFiles =
            getPatrolFiles();


        if (!patrolFiles.length) {

            return;

        }


        let startCount = 0;
        let endCount = 0;


        /*
        |--------------------------------------------------------------------------
        | LOAD EVERY PATROL FILE
        |--------------------------------------------------------------------------
        */

        for (
            const patrolFile of patrolFiles
        ) {

            console.log(
                "Creating markers from:",
                patrolFile
            );


            const response =
                await fetch(patrolFile);


            if (!response.ok) {

                console.error(
                    `Failed to load ${patrolFile}`
                );

                continue;

            }


            const data =
                await response.json();


            if (
                !data.features ||
                !data.features.length
            ) {

                continue;

            }


            /*
            |--------------------------------------------------------------------------
            | PROCESS EVERY FEATURE
            |--------------------------------------------------------------------------
            */

            data.features.forEach(
                (
                    feature: any
                ) => {

                    if (
                        !feature.geometry
                    ) {

                        return;

                    }


                    const geometry =
                        feature.geometry;


                    const properties =
                        feature.properties || {};


                    let paths:
                        any[] = [];


                    /*
                    |--------------------------------------------------------------------------
                    | LINESTRING
                    |--------------------------------------------------------------------------
                    */

                    if (
                        geometry.type ===
                        "LineString"
                    ) {

                        paths = [
                            geometry.coordinates
                        ];

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | MULTILINESTRING
                    |--------------------------------------------------------------------------
                    */

                    else if (
                        geometry.type ===
                        "MultiLineString"
                    ) {

                        paths =
                            geometry.coordinates;

                    }


                    else {

                        return;

                    }


                    if (
                        !paths.length
                    ) {

                        return;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | FIND START
                    |--------------------------------------------------------------------------
                    */

                    let startCoordinate:
                        number[] | null =
                        null;


                    for (
                        const path of paths
                    ) {

                        if (
                            path &&
                            path.length
                        ) {

                            startCoordinate =
                                path[0];

                            break;

                        }

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | FIND END
                    |--------------------------------------------------------------------------
                    */

                    let endCoordinate:
                        number[] | null =
                        null;


                    for (
                        let i =
                            paths.length - 1;

                        i >= 0;

                        i--
                    ) {

                        const path =
                            paths[i];


                        if (
                            path &&
                            path.length
                        ) {

                            endCoordinate =
                                path[
                                    path.length - 1
                                ];

                            break;

                        }

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | DETERMINE COLOR BASED ON FILE
                    |--------------------------------------------------------------------------
                    */

                    let startColor:
                        number[];

                    let endColor:
                        number[];


                    if (
                        patrolFile.includes(
                            "0707"
                        )
                    ) {

                        /*
                        | Day 1
                        | Green start
                        | Dark red end
                        */

                        startColor = [
                            0,
                            200,
                            80,
                            1
                        ];

                        endColor = [
                            180,
                            0,
                            0,
                            1
                        ];

                    }

                    else if (
                        patrolFile.includes(
                            "0716"
                        )
                    ) {

                        /*
                        | Day 2
                        | Green start
                        | Blue end
                        */

                        startColor = [
                            0,
                            200,
                            80,
                            1
                        ];

                        endColor = [
                            0,
                            70,
                            255,
                            1
                        ];

                    }

                    else {

                        startColor = [
                            0,
                            200,
                            80,
                            1
                        ];

                        endColor = [
                            255,
                            0,
                            0,
                            1
                        ];

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | START MARKER
                    |--------------------------------------------------------------------------
                    */

                    if (
                        startCoordinate &&
                        startCoordinate.length >= 2
                    ) {

                        const point =
                            new Point({

                                x:
                                    Number(
                                        startCoordinate[0]
                                    ),

                                y:
                                    Number(
                                        startCoordinate[1]
                                    ),

                                spatialReference: {

                                    wkid:
                                        TRANSECT_WKID

                                }

                            });


                        const graphic =
                            new Graphic({

                                geometry:
                                    point,

                                symbol:
                                    new SimpleMarkerSymbol({

                                        style:
                                            "circle",

                                        color:
                                            startColor,

                                        size:
                                            13,

                                        outline: {

                                            color: [
                                                255,
                                                255,
                                                255,
                                                1
                                            ],

                                            width:
                                                2

                                        }

                                    }),

                                attributes: {

                                    ...properties,

                                    marker_type:
                                        "Patrol Start",

                                    source_file:
                                        patrolFile

                                },

                                popupTemplate: {

                                    title:
                                        "🟢 Patrol Start",

                                    content: `

                                        <b>Patrol ID:</b>
                                        ${properties.Patrol_ID ?? "-"}
                                        <br>

                                        <b>Team:</b>
                                        ${properties.Team ?? "-"}
                                        <br>

                                        <b>Leader:</b>
                                        ${properties.Leader ?? "-"}
                                        <br>

                                        <b>Start:</b>
                                        ${properties.Patrol_Sta ?? "-"}
                                        <br>

                                        <b>File:</b>
                                        ${patrolFile}

                                    `

                                }

                            });


                        graphicsLayer.add(
                            graphic
                        );


                        startCount++;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | END MARKER
                    |--------------------------------------------------------------------------
                    */

                    if (
                        endCoordinate &&
                        endCoordinate.length >= 2
                    ) {

                        const point =
                            new Point({

                                x:
                                    Number(
                                        endCoordinate[0]
                                    ),

                                y:
                                    Number(
                                        endCoordinate[1]
                                    ),

                                spatialReference: {

                                    wkid:
                                        TRANSECT_WKID

                                }

                            });


                        const graphic =
                            new Graphic({

                                geometry:
                                    point,

                                symbol:
                                    new SimpleMarkerSymbol({

                                        style:
                                            "circle",

                                        color:
                                            endColor,

                                        size:
                                            13,

                                        outline: {

                                            color: [
                                                255,
                                                255,
                                                255,
                                                1
                                            ],

                                            width:
                                                2

                                        }

                                    }),

                                attributes: {

                                    ...properties,

                                    marker_type:
                                        "Patrol End",

                                    source_file:
                                        patrolFile

                                },

                                popupTemplate: {

                                    title:
                                        "🔴 Patrol End",

                                    content: `

                                        <b>Patrol ID:</b>
                                        ${properties.Patrol_ID ?? "-"}
                                        <br>

                                        <b>Team:</b>
                                        ${properties.Team ?? "-"}
                                        <br>

                                        <b>Leader:</b>
                                        ${properties.Leader ?? "-"}
                                        <br>

                                        <b>End:</b>
                                        ${properties.Patrol_End ?? "-"}
                                        <br>

                                        <b>File:</b>
                                        ${patrolFile}

                                    `

                                }

                            });


                        graphicsLayer.add(
                            graphic
                        );


                        endCount++;

                    }

                }
            );

        }


        console.log(
            "Start markers:",
            startCount
        );

        console.log(
            "End markers:",
            endCount
        );

    }

    catch (error) {

        console.error(
            "Error creating patrol markers:",
            error
        );

    }

}


/*
|--------------------------------------------------------------------------
| INITIALIZE MAP
|--------------------------------------------------------------------------
*/

onMounted(
    async () => {

        try {

            console.log(
                "================================"
            );

            console.log(
                "INITIALIZING PATROL MAP"
            );

            console.log(
                "================================"
            );


            /*
            |--------------------------------------------------------------------------
            | GRAPHICS LAYER
            |--------------------------------------------------------------------------
            */

            graphicsLayer =
                new GraphicsLayer({

                    title:
                        "Patrol Map"

                });


            /*
            |--------------------------------------------------------------------------
            | MAP
            |--------------------------------------------------------------------------
            */

            const map =
                new Map({

                    basemap:
                        "streets-vector",

                    layers: [

                        graphicsLayer

                    ]

                });


            /*
            |--------------------------------------------------------------------------
            | MAP VIEW
            |--------------------------------------------------------------------------
            */

            view =
                new MapView({

                    container:
                        mapDiv.value!,

                    map:
                        map,

                    zoom:
                        10,

                    constraints: {

                        minZoom:
                            5,

                        maxZoom:
                            20

                    }

                });


            /*
            |--------------------------------------------------------------------------
            | WAIT FOR MAP
            |--------------------------------------------------------------------------
            */

            await view.when();


            /*
            |--------------------------------------------------------------------------
            | ZOOM CONTROL
            |--------------------------------------------------------------------------
            */

            const zoom =
                new Zoom({

                    view:
                        view

                });


            view.ui.add(
                zoom,
                "top-right"
            );


            /*
            |--------------------------------------------------------------------------
            | LOAD PROTECTED AREA
            |--------------------------------------------------------------------------
            */

            await loadProtectedArea();


            /*
            |--------------------------------------------------------------------------
            | LOAD PATROL ROUTES
            |--------------------------------------------------------------------------
            */

            await loadTransects();


            /*
            |--------------------------------------------------------------------------
            | LOAD START / END
            |--------------------------------------------------------------------------
            */

            await loadPatrolStartEnd();


            /*
            |--------------------------------------------------------------------------
            | FIND PROTECTED AREA GRAPHICS
            |--------------------------------------------------------------------------
            */

            const protectedAreaGraphics =
                graphicsLayer.graphics.filter(
                    (
                        graphic: Graphic
                    ) => {

                        return (
                            graphic.geometry &&
                            graphic.geometry.type ===
                            "polygon"
                        );

                    }
                );


            /*
            |--------------------------------------------------------------------------
            | ZOOM TO PROTECTED AREA
            |--------------------------------------------------------------------------
            */

            if (
                protectedAreaGraphics.length
            ) {

                console.log(
                    "Zooming to protected area..."
                );


                await view.goTo(
                    protectedAreaGraphics,
                    {

                        padding: {

                            top:
                                100,

                            bottom:
                                80,

                            left:
                                80,

                            right:
                                80

                        },

                        duration:
                            1200

                    }
                );

            }


            console.log(
                "================================"
            );

            console.log(
                "PATROL MAP READY"
            );

            console.log(
                "================================"
            );


        } catch (error) {

            console.error(
                "Error initializing map:",
                error
            );

        }

    }
);

</script>


<style scoped>
@import url('../../../css/style.css');

:deep(.p-datatable .p-datatable-thead > tr > th) {
    background-color: #1a3a6b;
    color: #fff;
    border-color: rgba(255, 255, 255, 0.1);
}

/*
|--------------------------------------------------------------------------
| MAP CONTAINER
|--------------------------------------------------------------------------
*/

.map-container {

    position:
        relative;

    width:
        100%;

    height:
        100vh;

    overflow:
        hidden;

}


/*
|--------------------------------------------------------------------------
| PATROL MAP LEGEND
|--------------------------------------------------------------------------
*/

.patrol-legend {

    position:
        absolute;

    top:
        20px;

    left:
        20px;

    z-index:
        20;

    background:
        rgba(255,
            255,
            255,
            0.96);

    border-radius:
        8px;

    padding:
        14px 16px;

    min-width:
        220px;

    box-shadow:
        0 2px 10px rgba(0,
            0,
            0,
            0.20);

    font-family:
        Arial,
        sans-serif;

}


/*
|--------------------------------------------------------------------------
| LEGEND TITLE
|--------------------------------------------------------------------------
*/

.legend-title {

    font-size:
        16px;

    font-weight:
        700;

    margin-bottom:
        12px;

    color:
        #222;

}


/*
|--------------------------------------------------------------------------
| LEGEND ITEM
|--------------------------------------------------------------------------
*/

.legend-item {

    display:
        flex;

    align-items:
        center;

    gap:
        9px;

    margin-bottom:
        9px;

    font-size:
        13px;

    color:
        #333;

}


/*
|--------------------------------------------------------------------------
| LEGEND LINE
|--------------------------------------------------------------------------
*/

.legend-line {

    display:
        inline-block;

    width:
        28px;

    height:
        3px;

    flex-shrink:
        0;

}


/*
|--------------------------------------------------------------------------
| PATROL LINE
|--------------------------------------------------------------------------
*/

.patrol-line {

    background:
        #07551f;

}


/*
|--------------------------------------------------------------------------
| PROTECTED AREA LINE
|--------------------------------------------------------------------------
*/

.protected-line {

    background:
        #ff0000;

}


/*
|--------------------------------------------------------------------------
| LEGEND CIRCLE
|--------------------------------------------------------------------------
*/

.legend-circle {

    width:
        15px;

    height:
        15px;

    border-radius:
        50%;

    display:
        inline-block;

    flex-shrink:
        0;

}


/*
|--------------------------------------------------------------------------
| START
|--------------------------------------------------------------------------
*/

.start-circle {

    background:
        #00c853;

    border:
        2px solid white;

    box-shadow:
        0 0 0 1px #00c853;

}


/*
|--------------------------------------------------------------------------
| END
|--------------------------------------------------------------------------
*/

.end-circle {

    background:
        #ff0000;

    border:
        2px solid white;

    box-shadow:
        0 0 0 1px #ff0000;

}


/*
|--------------------------------------------------------------------------
| MAP TITLE
|--------------------------------------------------------------------------
*/

.map-title {

    position:
        absolute;

    top:
        20px;

    left:
        50%;

    transform:
        translateX(-50%);

    z-index:
        20;

    background:
        rgba(255,
            255,
            255,
            0.92);

    padding:
        8px 18px;

    border-radius:
        6px;

    text-align:
        center;

    box-shadow:
        0 2px 8px rgba(0,
            0,
            0,
            0.15);

    font-family:
        Arial,
        sans-serif;

    pointer-events:
        none;

}


/*
|--------------------------------------------------------------------------
| MAIN TITLE
|--------------------------------------------------------------------------
*/

.map-title-main {

    font-size:
        18px;

    font-weight:
        700;

    color:
        #222;

}


/*
|--------------------------------------------------------------------------
| SUBTITLE
|--------------------------------------------------------------------------
*/

.map-title-sub {

    font-size:
        12px;

    color:
        #555;

    margin-top:
        2px;

}


/*
|--------------------------------------------------------------------------
| MOBILE
|--------------------------------------------------------------------------
*/

@media (max-width: 768px) {

    .patrol-legend {

        top:
            10px;

        left:
            10px;

        min-width:
            180px;

        padding:
            10px 12px;

    }


    .legend-title {

        font-size:
            14px;

    }


    .legend-item {

        font-size:
            11px;

    }


    .map-title {

        top:
            10px;

    }


    .map-title-main {

        font-size:
            14px;

    }


    .map-title-sub {

        font-size:
            10px;

    }

}
</style>