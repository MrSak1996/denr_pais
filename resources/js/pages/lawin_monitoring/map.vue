<template>

    <Head title="DENR Online Protected Area Information System" />

    <AppLayout :breadcrumbs="breadcrumbs">

        <div class="space-y-6 p-4">

            <!-- =========================================================
                 HEADER
            ========================================================== -->

            <div class="flex items-center justify-between flex-wrap gap-4">

                <!-- LEFT -->

                <div class="flex items-center gap-3">

                    <div class="w-9 h-9 rounded-lg bg-[#1a6b45] flex items-center justify-center shrink-0">

                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.2">

                            <path
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />

                        </svg>

                    </div>

                    <div>

                        <h5 class="text-[15px] font-bold text-gray-900">

                            Monitoring of Protected Area Patrol Plans in
                            {{ props.pa_code }}

                        </h5>

                        <p class="text-xs text-gray-400 mt-0.5">

                            Real-time tracking of patrol plans

                        </p>

                    </div>

                </div>

            </div>


            <!-- =========================================================
                 MAP
            ========================================================== -->

            <div class="box">

                <div ref="mapDiv" class="map-container">

                    <!-- =====================================================
                         LEGEND
                    ====================================================== -->

                    <div class="patrol-legend">

                        <div class="legend-title">

                            Patrol Map

                        </div>


                        <!-- DAY 1 -->

                        <div class="legend-item">

                            <span class="legend-line day1-line"></span>

                            <span>
                                Day 1 Patrol
                            </span>

                        </div>


                        <!-- DAY 2 -->

                        <div class="legend-item">

                            <span class="legend-line day2-line"></span>

                            <span>
                                Day 2 Patrol
                            </span>

                        </div>


                        <!-- SINGLE DAY -->

                        <div class="legend-item">

                            <span class="legend-line single-line"></span>

                            <span>
                                Single-Day Patrol
                            </span>

                        </div>


                        <!-- START -->

                        <div class="legend-item">

                            <span class="legend-circle start-circle"></span>

                            <span>
                                Patrol Start
                            </span>

                        </div>


                        <!-- END -->

                        <div class="legend-item">

                            <span class="legend-circle end-circle"></span>

                            <span>
                                Patrol End
                            </span>

                        </div>


                        <!-- PA -->

                        <div class="legend-item">

                            <span class="legend-line protected-line"></span>

                            <span>
                                Protected Area
                            </span>

                        </div>

                    </div>


                    <!-- =====================================================
                         MAP TITLE
                    ====================================================== -->

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

import { ref, onMounted } from "vue";

/*
|--------------------------------------------------------------------------
| ArcGIS
|--------------------------------------------------------------------------
*/

import Map from "@arcgis/core/Map";
import MapView from "@arcgis/core/views/MapView";
import GraphicsLayer from "@arcgis/core/layers/GraphicsLayer";
import Graphic from "@arcgis/core/Graphic";


/*
|--------------------------------------------------------------------------
| Geometry
|--------------------------------------------------------------------------
*/

import Polygon from "@arcgis/core/geometry/Polygon";
import Polyline from "@arcgis/core/geometry/Polyline";
import Point from "@arcgis/core/geometry/Point";


/*
|--------------------------------------------------------------------------
| Symbols
|--------------------------------------------------------------------------
*/

import SimpleFillSymbol from "@arcgis/core/symbols/SimpleFillSymbol";
import SimpleLineSymbol from "@arcgis/core/symbols/SimpleLineSymbol";
import SimpleMarkerSymbol from "@arcgis/core/symbols/SimpleMarkerSymbol";


/*
|--------------------------------------------------------------------------
| Zoom
|--------------------------------------------------------------------------
*/

import Zoom from "@arcgis/core/widgets/Zoom";


/*
|--------------------------------------------------------------------------
| Layout / Inertia
|--------------------------------------------------------------------------
*/

import AppLayout from "@/layouts/AppLayout.vue";
import { type BreadcrumbItem } from "@/types";
import { Head } from "@inertiajs/vue3";


/*
|--------------------------------------------------------------------------
| PROPS
|--------------------------------------------------------------------------
*/

const props = defineProps<{

    pa_id: number;

    pa_code: string;

    geojson: {

        type: string;

        features: any[];

    };

}>();


/*
|--------------------------------------------------------------------------
| BREADCRUMBS
|--------------------------------------------------------------------------
*/

const breadcrumbs: BreadcrumbItem[] = [

    {
        title:
            "Protected Area Patrol Monitoring",

        href:
            "/lawin_monitoring/index",
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
| PATROL GEOJSON FILES
|--------------------------------------------------------------------------
*/

const PATROL_FILES: Record<number, string[]> = {

    1: [
        "/MBSCPL-JULY-LILIW1.json",
        "/MBSCPL-JULY-LILIW2.json",
        "/MBSCPL-JULY-MAJAYJAY1.json",
        "/MBSCPL-JULY-MAJAYJAY2.json",
        "/MBSCPL-JULY-NAGCARLAN1.json",
        "/MBSCPL-JULY-NAGCARLAN2.json",
        "/MBSCPL-JULY-SAN PABLO1.json",
        "/MBSCPL-JULY-SAN PABLO2.json",
        "/MBSCPL-JULY-RIZAL1.json",
        "/MBSCPL-JULY-RIZAL2.json",
        "/MBSCPL-JULY-RIZAL3.json",
        "/MBSCPL-JULY-RIZAL4.json",

        "/AWFR_JULY.json",
        "/BPL_JULY.json",

        "/BRWFR-07072026.json",
        "/BRWFR-07162026.json",

        "/CIPLS_JULY.json",
        "/MIGRBS.json",
        "/CPLS-July.json",
        "/CWFR_JULY.json",
        "/HTPL-July.json",
        "/IWFR_JULY.json",
        "/MSPL-JULY.json",

        "/Rizal_2026_line.json",

        "/MBSCPL_QUEZON-JULY.json",

        "/PaWFR-JULY.json",

        "/PMSFR-07112026.json",
        "/PMSFR-07222026.json",

        "/PPL-JULY.json",

        "/SFPL_07012026.json",
        "/SFPL_07022026.json",

        "/UMRBPL_07102026_1.json",
        "/UMRBPL_07102026_2.json",
        "/UMRBPL_07132026_3.json",
        "/UMRBPL_07172026_4.json",

        "/URWR-JULY.json",

        "/PoWFR-JULY.json",
    ],

    2: [
        "/AWFR_JULY.json",
    ],

    3: [ "/BPL_JULY.json", ],

    4: [ "/BRWFR-07072026.json", "/BRWFR-07162026.json", ],
    5: [

        "/CIPLS_JULY.json",

    ],

    12: [

        "/MIGRBS.json",

    ],

    6: [

        "/CPLS-July.json",

    ],

    7: [

        "/CWFR_JULY.json",

    ],

    8: [



    ],
    9: [
        "/IWFR_JULY.json"
    ],
    14: [
        "/MSPL-JULY.json"
    ],

    17: [

        "/Rizal_2026_line.json",

    ],
    11: [
        "/MBSCPL_QUEZON-JULY.json"
    ],
    15: [
        "/PaWFR-JULY.json"
    ],
    16: [
        "/PMSFR-07112026.json",
        "/PMSFR-07222026.json",
    ],
    18:[
        "/PPL-JULY.json",
    ],
    20:[
        "/SFPL_07012026.json",
        "/SFPL_07022026.json",
    ],
    22:[
        "/UMRBPL_07102026_1.json",
        "/UMRBPL_07102026_2.json",
        "/UMRBPL_07132026_3.json",
        "/UMRBPL_07172026_4.json",
    ],
    23:[
        "/URWR-JULY.json",
    ],
    24:[
        "/PoWFR-JULY.json",
    ],
    27: [
        "/MBSCPL-JULY-LILIW1.json",
        "/MBSCPL-JULY-LILIW2.json",
        "/MBSCPL-JULY-MAJAYJAY1.json",
        "/MBSCPL-JULY-MAJAYJAY2.json",
        "/MBSCPL-JULY-NAGCARLAN1.json",
        "/MBSCPL-JULY-NAGCARLAN2.json",
        "/MBSCPL-JULY-SAN PABLO1.json",
        "/MBSCPL-JULY-SAN PABLO2.json",
        "/MBSCPL-JULY-RIZAL1.json",
        "/MBSCPL-JULY-RIZAL2.json",
        "/MBSCPL-JULY-RIZAL3.json",
        "/MBSCPL-JULY-RIZAL4.json",
    ]

};


/*
|--------------------------------------------------------------------------
| GET PATROL FILES
|--------------------------------------------------------------------------
*/

function getPatrolFiles(): string[] {

    const files =
        PATROL_FILES[props.pa_id];


    if (
        !files ||
        files.length === 0
    ) {

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
| Patrol and protected-area geometry:
|
| EPSG:32651
| WGS 84 / UTM Zone 51N
|
| ArcGIS map:
|
| EPSG:4326
| WGS 84
|
|--------------------------------------------------------------------------
*/

const PATROL_WKID =
    32651;

const MAP_WKID =
    4326;


/*
|--------------------------------------------------------------------------
| UTM 51N → WGS84 CONVERSION
|--------------------------------------------------------------------------
*/

function utm51ToWgs84(
    easting: number,
    northing: number
): [number, number] {

    /*
    |--------------------------------------------------------------------------
    | WGS84 parameters
    |--------------------------------------------------------------------------
    */

    const a =
        6378137.0;

    const eccSquared =
        0.00669438;

    const k0 =
        0.9996;


    /*
    |--------------------------------------------------------------------------
    | UTM Zone 51
    |--------------------------------------------------------------------------
    */

    const zoneNumber =
        51;

    const longOrigin =
        (zoneNumber - 1) * 6 - 180 + 3;


    /*
    |--------------------------------------------------------------------------
    | Remove false easting
    |--------------------------------------------------------------------------
    */

    const x =
        easting - 500000.0;

    let y =
        northing;


    /*
    |--------------------------------------------------------------------------
    | Northern hemisphere
    |--------------------------------------------------------------------------
    */

    const eccPrimeSquared =
        eccSquared /
        (1 - eccSquared);


    const M =
        y / k0;


    const mu =
        M /
        (
            a *
            (
                1 -
                eccSquared / 4 -
                3 *
                eccSquared *
                eccSquared /
                64 -
                5 *
                eccSquared *
                eccSquared *
                eccSquared /
                256
            )
        );


    const e1 =
        (
            1 -
            Math.sqrt(
                1 - eccSquared
            )
        ) /
        (
            1 +
            Math.sqrt(
                1 - eccSquared
            )
        );


    const J1 =
        3 * e1 / 2 -
        27 *
        Math.pow(e1, 3) /
        32;


    const J2 =
        21 *
        Math.pow(e1, 2) /
        16 -
        55 *
        Math.pow(e1, 4) /
        32;


    const J3 =
        151 *
        Math.pow(e1, 3) /
        96;


    const J4 =
        1097 *
        Math.pow(e1, 4) /
        512;


    const fp =
        mu +
        J1 *
        Math.sin(2 * mu) +
        J2 *
        Math.sin(4 * mu) +
        J3 *
        Math.sin(6 * mu) +
        J4 *
        Math.sin(8 * mu);


    const sinFp =
        Math.sin(fp);


    const cosFp =
        Math.cos(fp);


    const tanFp =
        Math.tan(fp);


    const C1 =
        eccPrimeSquared *
        cosFp *
        cosFp;


    const T1 =
        tanFp *
        tanFp;


    const N1 =
        a /
        Math.sqrt(
            1 -
            eccSquared *
            sinFp *
            sinFp
        );


    const R1 =
        a *
        (
            1 -
            eccSquared
        ) /
        Math.pow(
            1 -
            eccSquared *
            sinFp *
            sinFp,
            1.5
        );


    const D =
        x /
        (N1 * k0);


    const lat =
        fp -
        (
            N1 *
            tanFp /
            R1
        ) *
        (
            D * D / 2 -
            (
                5 +
                3 * T1 +
                10 * C1 -
                4 * C1 * C1 -
                9 * eccPrimeSquared
            ) *
            Math.pow(D, 4) /
            24 +
            (
                61 +
                90 * T1 +
                298 * C1 +
                45 * T1 * T1 -
                252 * eccPrimeSquared -
                3 * C1 * C1
            ) *
            Math.pow(D, 6) /
            720
        );


    const lon =
        (
            D -
            (
                1 +
                2 * T1 +
                C1
            ) *
            Math.pow(D, 3) /
            6 +
            (
                5 -
                2 * C1 +
                28 * T1 -
                3 * C1 * C1 +
                8 * eccPrimeSquared +
                24 * T1 * T1
            ) *
            Math.pow(D, 5) /
            120
        ) /
        cosFp;


    const latitude =
        lat *
        180 /
        Math.PI;


    const longitude =
        longOrigin +
        lon *
        180 /
        Math.PI;


    return [
        longitude,
        latitude
    ];

}


/*
|--------------------------------------------------------------------------
| GENERIC COORDINATE CONVERTER
|--------------------------------------------------------------------------
|
| Detects:
|
| 1. UTM Zone 51N
| 2. WGS84 longitude / latitude
|
|--------------------------------------------------------------------------
*/

function convertCoordinate(
    coordinate: number[]
): number[] {

    if (
        !coordinate ||
        coordinate.length < 2
    ) {

        return coordinate;

    }


    const x =
        Number(coordinate[0]);

    const y =
        Number(coordinate[1]);


    /*
    |--------------------------------------------------------------------------
    | Validate numbers
    |--------------------------------------------------------------------------
    */

    if (
        !Number.isFinite(x) ||
        !Number.isFinite(y)
    ) {

        console.warn(
            "Invalid coordinate:",
            coordinate
        );

        return coordinate;

    }


    /*
    |--------------------------------------------------------------------------
    | Detect UTM coordinates
    |--------------------------------------------------------------------------
    |
    | Example HTPL:
    |
    | 302692.283
    | 1614317.621
    |
    |--------------------------------------------------------------------------
    */

    if (
        x > 100000 &&
        x < 900000 &&
        y > 1000000 &&
        y < 10000000
    ) {

        const converted =
            utm51ToWgs84(
                x,
                y
            );


        return converted;

    }


    /*
    |--------------------------------------------------------------------------
    | Already WGS84 longitude / latitude
    |--------------------------------------------------------------------------
    */

    if (
        Math.abs(x) <= 180 &&
        Math.abs(y) <= 90
    ) {

        return [
            x,
            y
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | Unknown coordinate
    |--------------------------------------------------------------------------
    */

    console.warn(
        "Unknown coordinate system:",
        coordinate
    );


    return [
        x,
        y
    ];

}


/*
|--------------------------------------------------------------------------
| BACKWARD COMPATIBILITY
|--------------------------------------------------------------------------
|
| Existing patrol code uses convertPatrolCoordinate().
|
|--------------------------------------------------------------------------
*/

function convertPatrolCoordinate(
    coordinate: number[]
): number[] {

    return convertCoordinate(
        coordinate
    );

}


/*
|--------------------------------------------------------------------------
| CONVERT ENTIRE PATH
|--------------------------------------------------------------------------
*/

function convertPatrolPath(
    path: number[][]
): number[][] {

    return path.map(
        (
            coordinate
        ) => {

            return convertPatrolCoordinate(
                coordinate
            );

        }
    );

}


/*
|--------------------------------------------------------------------------
| CONVERT POLYGON RINGS
|--------------------------------------------------------------------------
|
| THIS IS THE IMPORTANT HTPL FIX.
|
| HTPL polygon coordinates are UTM Zone 51N.
|
| Example:
|
| [302692, 1614317]
|
| must become:
|
| [longitude, latitude]
|
|--------------------------------------------------------------------------
*/

function convertPolygonCoordinates(
    coordinates: number[][][]
): number[][][] {

    return coordinates.map(
        (
            ring
        ) => {

            return ring.map(
                (
                    coordinate
                ) => {

                    return convertCoordinate(
                        coordinate
                    );

                }
            );

        }
    );

}


/*
|--------------------------------------------------------------------------
| CONVERT MULTIPOLYGON COORDINATES
|--------------------------------------------------------------------------
*/

function convertMultiPolygonCoordinates(
    coordinates: number[][][][]
): number[][][][] {

    return coordinates.map(
        (
            polygon
        ) => {

            return polygon.map(
                (
                    ring
                ) => {

                    return ring.map(
                        (
                            coordinate
                        ) => {

                            return convertCoordinate(
                                coordinate
                            );

                        }
                    );

                }
            );

        }
    );

}


/*
|--------------------------------------------------------------------------
| LOAD ALL PROTECTED AREAS - CALABARZON
|--------------------------------------------------------------------------
*/

async function loadAllProtectedAreas() {

    console.log(
        "================================"
    );

    console.log(
        "Loading ALL PA CALABARZON..."
    );

    console.log(
        "================================"
    );


    try {

        const response =
            await fetch(
                "/all_pas_calabarzon.json"
            );


        if (!response.ok) {

            throw new Error(
                `Failed to load ALL PA CALABARZON: ${response.status}`
            );

        }


        const data =
            await response.json();


        if (
            !data ||
            data.type !== "FeatureCollection" ||
            !Array.isArray(
                data.features
            )
        ) {

            console.error(
                "Invalid Protected Area GeoJSON:",
                data
            );

            return;

        }


        const paGraphics:
            Graphic[] = [];


        data.features.forEach(
            (
                feature: any,
                index: number
            ) => {

                if (
                    !feature ||
                    !feature.geometry
                ) {

                    console.warn(
                        `PA feature ${index} has no geometry.`
                    );

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

                    const convertedCoordinates =
                        convertPolygonCoordinates(
                            geometry.coordinates
                        );


                    const polygon =
                        new Polygon({

                            rings:
                                convertedCoordinates,

                            spatialReference: {
                                wkid:
                                    MAP_WKID
                            }

                        });


                    const graphic =
                        createProtectedAreaGraphic(
                            polygon,
                            properties
                        );


                    graphicsLayer.add(
                        graphic
                    );


                    paGraphics.push(
                        graphic
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

                    const convertedCoordinates =
                        convertMultiPolygonCoordinates(
                            geometry.coordinates
                        );


                    convertedCoordinates.forEach(
                        (
                            polygonCoordinates:
                                number[][][]
                        ) => {

                            const polygon =
                                new Polygon({

                                    rings:
                                        polygonCoordinates,

                                    spatialReference: {
                                        wkid:
                                            MAP_WKID
                                    }

                                });


                            const graphic =
                                createProtectedAreaGraphic(
                                    polygon,
                                    properties
                                );


                            graphicsLayer.add(
                                graphic
                            );


                            paGraphics.push(
                                graphic
                            );

                        }
                    );

                }

            }
        );


        console.log(
            "ALL PA LOADED:",
            paGraphics.length
        );


        /*
        |--------------------------------------------------------------------------
        | Zoom to all PAs
        |--------------------------------------------------------------------------
        */

        if (
            paGraphics.length
        ) {

            await view.goTo(
                paGraphics,
                {

                    padding: {

                        top:
                            100,

                        bottom:
                            100,

                        left:
                            100,

                        right:
                            100

                    },

                    duration:
                        1000

                }
            );

        }

    }

    catch (error) {

        console.error(
            "Error loading ALL PA:",
            error
        );

    }

}


/*
|--------------------------------------------------------------------------
| LOAD PROTECTED AREA
|--------------------------------------------------------------------------
*/

async function loadProtectedArea() {

    console.log(
        "================================"
    );

    console.log(
        "Loading protected area..."
    );

    console.log(
        "PA ID:",
        props.pa_id
    );

    console.log(
        "PA Code:",
        props.pa_code
    );

    console.log(
        "================================"
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
        (
            feature: any,
            index: number
        ) => {

            if (
                !feature ||
                !feature.geometry
            ) {

                console.warn(
                    `Protected area feature ${index} has no geometry.`
                );

                return;

            }


            const geometry =
                feature.geometry;


            const properties =
                feature.properties || {};


            console.log(
                `Protected area feature ${index}:`,
                geometry.type
            );


            /*
            |--------------------------------------------------------------------------
            | POLYGON
            |--------------------------------------------------------------------------
            */

            if (
                geometry.type ===
                "Polygon"
            ) {

                /*
                |--------------------------------------------------------------------------
                | IMPORTANT:
                |
                | Convert UTM 51N → WGS84
                |--------------------------------------------------------------------------
                */

                const convertedCoordinates =
                    convertPolygonCoordinates(
                        geometry.coordinates
                    );


                console.log(
                    "Original Polygon coordinates:",
                    geometry.coordinates
                );


                console.log(
                    "Converted Polygon coordinates:",
                    convertedCoordinates
                );


                /*
                |--------------------------------------------------------------------------
                | Create ArcGIS Polygon
                |--------------------------------------------------------------------------
                */

                const polygon =
                    new Polygon({

                        rings:
                            convertedCoordinates,

                        spatialReference: {
                            wkid:
                                MAP_WKID
                        }

                    });


                /*
                |--------------------------------------------------------------------------
                | Add to map
                |--------------------------------------------------------------------------
                */

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

                const convertedCoordinates =
                    convertMultiPolygonCoordinates(
                        geometry.coordinates
                    );


                convertedCoordinates.forEach(
                    (
                        polygonCoordinates:
                            number[][][]
                    ) => {

                        const polygon =
                            new Polygon({

                                rings:
                                    polygonCoordinates,

                                spatialReference: {
                                    wkid:
                                        MAP_WKID
                                }

                            });


                        addProtectedAreaGraphic(
                            polygon,
                            properties
                        );

                    }
                );

            }


            /*
            |--------------------------------------------------------------------------
            | UNSUPPORTED
            |--------------------------------------------------------------------------
            */

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

                    color: [
                        120,
                        200,
                        220,
                        0.12
                    ],

                    outline: {
                        color: [
                            178,
                            235,
                            242,
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
| CREATE PA GRAPHIC
|--------------------------------------------------------------------------
*/

function createProtectedAreaGraphic(
    polygon: Polygon,
    properties: any
): Graphic {

    return new Graphic({

        geometry:
            polygon,

        symbol:
            new SimpleFillSymbol({

                color: [
                    0,
                    150,
                    80,
                    0.08
                ],

                outline:
                    new SimpleLineSymbol({

                        color: [
                            178,
                            235,
                            242,
                            1
                        ],

                        width:
                            2,

                        style:
                            "solid"

                    })

            }),

        attributes:
            properties,

        popupTemplate: {

            title:
                properties.NAME ||
                properties.PA_NAME ||
                "Protected Area",

            content: [

                {

                    type:
                        "fields",

                    fieldInfos: [

                        {

                            fieldName:
                                "NAME",

                            label:
                                "Protected Area"

                        },

                        {

                            fieldName:
                                "DESIG",

                            label:
                                "Designation"

                        },

                        {

                            fieldName:
                                "STATUS",

                            label:
                                "Status"

                        },

                        {

                            fieldName:
                                "GIS_AREA",

                            label:
                                "Area"

                        }

                    ]

                }

            ]

        }

    });

}


/*
|--------------------------------------------------------------------------
| LOAD PATROL ROUTES
|--------------------------------------------------------------------------
*/

async function loadTransects() {

    const patrolFiles =
        getPatrolFiles();


    if (
        !patrolFiles.length
    ) {

        return;

    }


    let totalRoutes =
        0;


    for (
        const patrolFile of patrolFiles
    ) {

        try {

            console.log(
                "Loading patrol:",
                patrolFile
            );


            const response =
                await fetch(
                    patrolFile
                );


            if (
                !response.ok
            ) {

                console.error(
                    `Failed to load ${patrolFile}: ${response.status}`
                );

                continue;

            }


            const data =
                await response.json();


            if (
                !data ||
                !data.features ||
                !data.features.length
            ) {

                console.warn(
                    `No features found in ${patrolFile}`
                );

                continue;

            }


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


                    /*
                    |--------------------------------------------------------------------------
                    | LINESTRING
                    |--------------------------------------------------------------------------
                    */

                    if (
                        geometry.type ===
                        "LineString"
                    ) {

                        const path =
                            convertPatrolPath(
                                geometry.coordinates
                            );


                        addPatrolRoute(
                            path,
                            properties,
                            patrolFile
                        );


                        totalRoutes++;

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

                        const paths =
                            geometry.coordinates.map(
                                (
                                    path: number[][]
                                ) => {

                                    return convertPatrolPath(
                                        path
                                    );

                                }
                            );


                        addPatrolMultiRoute(
                            paths,
                            properties,
                            patrolFile
                        );


                        totalRoutes++;

                    }

                }
            );

        }

        catch (error) {

            console.error(
                `Error loading patrol ${patrolFile}:`,
                error
            );

        }

    }


    console.log(
        "TOTAL PATROL ROUTES:",
        totalRoutes
    );

}


/*
|--------------------------------------------------------------------------
| PATROL COLOR
|--------------------------------------------------------------------------
*/
function getPatrolColor(
    patrolFile: string
): number[] {

    const file =
        patrolFile.toLowerCase();

    /*
    |--------------------------------------------------------------------------
    | DAY 1
    |--------------------------------------------------------------------------
    | Example:
    | BRWFR-07072026.json
    | BRWFR-07112026.json
    |--------------------------------------------------------------------------
    */

    if (
        file.includes("0707") ||
        file.includes("0711") ||
        file.includes("0701") ||
        file.includes("0710")
    
    ) {

        return [
            255,    // Red
            140,    // Green
            0,      // Blue
            1       // Opacity
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | DAY 2
    |--------------------------------------------------------------------------
    | Example:
    | BRWFR-07162026.json
    |--------------------------------------------------------------------------
    */

    if (
        file.includes("0716") ||
        file.includes("0722") ||
        file.includes("0702") ||
        file.includes("0713")
    ) {

        return [
            0,
            90,
            255,
            1
        ];

    }


    /*
    |--------------------------------------------------------------------------
    | OTHER PATROLS
    |--------------------------------------------------------------------------
    */

    return [
        255,
        140,
        0,
        1
    ];

}





/*
|--------------------------------------------------------------------------
| ADD PATROL ROUTE
|--------------------------------------------------------------------------
*/

function addPatrolRoute(
    path: number[][],
    properties: any,
    patrolFile: string
) {

    const polyline =
        new Polyline({

            paths: [
                path
            ],

            spatialReference: {
                wkid:
                    MAP_WKID
            }

        });


    const graphic =
        new Graphic({

            geometry:
                polyline,

            symbol:
                new SimpleLineSymbol({

                    color:
                        getPatrolColor(
                            patrolFile
                        ),

                    width:
                        4,

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

}


/*
|--------------------------------------------------------------------------
| ADD MULTI PATROL ROUTE
|--------------------------------------------------------------------------
*/

function addPatrolMultiRoute(
    paths: number[][][],
    properties: any,
    patrolFile: string
) {

    const polyline =
        new Polyline({

            paths:
                paths,

            spatialReference: {
                wkid:
                    MAP_WKID
            }

        });


    const graphic =
        new Graphic({

            geometry:
                polyline,

            symbol:
                new SimpleLineSymbol({

                    color:
                        getPatrolColor(
                            patrolFile
                        ),

                    width:
                        4,

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

}


/*
|--------------------------------------------------------------------------
| LOAD PATROL START / END
|--------------------------------------------------------------------------
*/

async function loadPatrolStartEnd() {

    const patrolFiles =
        getPatrolFiles();


    for (
        const patrolFile of patrolFiles
    ) {

        try {

            const response =
                await fetch(
                    patrolFile
                );


            if (
                !response.ok
            ) {

                console.warn(
                    `Unable to load marker data: ${patrolFile}`
                );

                continue;

            }


            const data =
                await response.json();


            if (
                !data.features
            ) {

                continue;

            }


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
                        number[][][] = [];


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


                    /*
                    |--------------------------------------------------------------------------
                    | CONVERT PATHS
                    |--------------------------------------------------------------------------
                    */

                    const convertedPaths =
                        paths.map(
                            (
                                path
                            ) => {

                                return convertPatrolPath(
                                    path
                                );

                            }
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | START
                    |--------------------------------------------------------------------------
                    */

                    let startCoordinate:
                        number[] | null =
                        null;


                    for (
                        const path
                        of convertedPaths
                    ) {

                        if (
                            path.length
                        ) {

                            startCoordinate =
                                path[0];

                            break;

                        }

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | END
                    |--------------------------------------------------------------------------
                    */

                    let endCoordinate:
                        number[] | null =
                        null;


                    for (
                        let i =
                            convertedPaths.length - 1;

                        i >= 0;

                        i--
                    ) {

                        const path =
                            convertedPaths[i];


                        if (
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
                    | START MARKER
                    |--------------------------------------------------------------------------
                    */

                    if (
                        startCoordinate
                    ) {

                        addPatrolMarker(

                            startCoordinate,

                            properties,

                            patrolFile,

                            "Patrol Start"

                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | END MARKER
                    |--------------------------------------------------------------------------
                    */

                    if (
                        endCoordinate
                    ) {

                        addPatrolMarker(

                            endCoordinate,

                            properties,

                            patrolFile,

                            "Patrol End"

                        );

                    }

                }
            );

        }

        catch (error) {

            console.error(
                `Marker error: ${patrolFile}`,
                error
            );

        }

    }

}


/*
|--------------------------------------------------------------------------
| ADD PATROL MARKER
|--------------------------------------------------------------------------
*/

function addPatrolMarker(
    coordinate: number[],
    properties: any,
    patrolFile: string,
    markerType: string
) {

    const point =
        new Point({

            x:
                coordinate[0],

            y:
                coordinate[1],

            spatialReference: {
                wkid:
                    MAP_WKID
            }

        });


    /*
    |--------------------------------------------------------------------------
    | START / END
    |--------------------------------------------------------------------------
    */

    const isStart =
        markerType === "Patrol Start";


    /*
    |--------------------------------------------------------------------------
    | MARKER COLORS
    |--------------------------------------------------------------------------
    |
    | START = GREEN
    | END   = RED
    |
    | These colors are completely independent
    | from the patrol route line color.
    |--------------------------------------------------------------------------
    */

    const markerColor =
        isStart

            ? [
                0,
                200,
                80,
                1
            ]

            : [
                255,
                0,
                0,
                1
            ];


    const graphic =
        new Graphic({

            geometry:
                point,

            symbol:
                new SimpleMarkerSymbol({

                    style:
                        "circle",

                    color:
                        markerColor,

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
                    markerType,

                source_file:
                    patrolFile

            },

            popupTemplate: {

                title:
                    markerType,

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

                    <b>Date:</b>
                    ${
                        isStart
                            ? properties.Patrol_Sta ?? "-"
                            : properties.Patrol_End ?? "-"
                    }

                    <br>

                    <b>File:</b>
                    ${patrolFile}

                `

            }

        });


    graphicsLayer.add(
        graphic
    );

}


/*
|--------------------------------------------------------------------------
| ZOOM TO LOADED DATA
|--------------------------------------------------------------------------
*/

async function zoomToLoadedData() {

    try {

        if (
            !graphicsLayer ||
            graphicsLayer.graphics.length === 0
        ) {

            console.warn(
                "No graphics available for zoom."
            );

            return;

        }


        console.log(
            "================================"
        );

        console.log(
            "ZOOMING TO LOADED MAP DATA..."
        );

        console.log(
            "Graphics:",
            graphicsLayer.graphics.length
        );

        console.log(
            "================================"
        );


        /*
        |--------------------------------------------------------------------------
        | Get all graphics
        |--------------------------------------------------------------------------
        */

        const graphics =
            graphicsLayer.graphics.toArray();


        /*
        |--------------------------------------------------------------------------
        | Zoom to all graphics
        |--------------------------------------------------------------------------
        */

        await view.goTo(
            graphics,
            {

                padding: {

                    top:
                        120,

                    bottom:
                        120,

                    left:
                        120,

                    right:
                        120

                },

                duration:
                    1200

            }
        );


        console.log(
            "Map successfully zoomed to loaded data."
        );

    }

    catch (error) {

        console.error(
            "Error zooming to loaded data:",
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
                "INITIALIZING PATROL MAP..."
            );

            console.log(
                "PA ID:",
                props.pa_id
            );

            console.log(
                "PA CODE:",
                props.pa_code
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
                        "hybrid",

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

                    center: [

                        121.0,

                        13.0

                    ],

                    zoom:
                        6,

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
            |
            | HTPL polygon is converted from UTM 51N → WGS84
            |
            |--------------------------------------------------------------------------
            */

            await loadAllProtectedAreas();
            //  await loadProtectedArea();


            /*
            |--------------------------------------------------------------------------
            | LOAD PATROL ROUTES
            |--------------------------------------------------------------------------
            */

            await loadTransects();


            /*
            |--------------------------------------------------------------------------
            | LOAD START / END MARKERS
            |--------------------------------------------------------------------------
            */

            await loadPatrolStartEnd();


            /*
            |--------------------------------------------------------------------------
            | FINAL ZOOM
            |--------------------------------------------------------------------------
            */

            await zoomToLoadedData();


            console.log(
                "================================"
            );

            console.log(
                "PATROL MAP READY"
            );

            console.log(
                "================================"
            );

        }

        catch (error) {

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
| PATROL LEGEND
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
| DAY 1
|--------------------------------------------------------------------------
*/

.day1-line {

    background:
        #ff0000;

}


/*
|--------------------------------------------------------------------------
| DAY 2
|--------------------------------------------------------------------------
*/

.day2-line {

    background:
        #005aff;

}


/*
|--------------------------------------------------------------------------
| SINGLE DAY
|--------------------------------------------------------------------------
*/

.single-line {

    background:
        #ff8c00;

}


/*
|--------------------------------------------------------------------------
| PROTECTED AREA
|--------------------------------------------------------------------------
*/

.protected-line {

    background:
        #b2ebf2;

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
