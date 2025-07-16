<template>

    <GoogleMap
        ref="map"
        :zoom="zoom"
        :mapId="mapId"
        :center="center"
        :api-key="googleMapsApiKey"
        :style="'width:'+width+'; height:'+height+';'">

        <AdvancedMarker
            :key="index"
            @dragend="markerDragEnd"
            :options="marker.options"
            :pin-options="marker.pinOptions"
            v-for="(marker, index) in localMarkers"
        />

    </GoogleMap>

</template>

<script>
    /**
     * Component Reference: https://www.npmjs.com/package/vue3-google-map
     *
     * Google Documentaion:
     * Maps Reference: https://developers.google.com/maps/documentation/javascript/reference/map
     * AdvancedMarker Reference: https://developers.google.com/maps/documentation/javascript/reference/advanced-markers
     */
    import { GoogleMap, AdvancedMarker } from 'vue3-google-map';

    export default {
        components: { GoogleMap, AdvancedMarker },
        props: {
            zoom: {
                type: Number,
                default: 15
            },
            width: {
                type: String,
                default: '100%'
            },
            height: {
                type: String,
                default: '500px'
            },
            markers: {
                type: Array,
                default: () => {
                    return [];
                }
            },
            gmpDraggable: {
                type: Boolean,
                default: true
            },
            pinOptions: {
                type: Object,
                default: () => {
                    return {
                        background: '#1f87e8',
                        borderColor: '#1f87e8',
                        glyphColor: '#ffffff'
                    }
                }
            },
            latitude: {
                type: [Number, null],
                default: null
            },
            longitude: {
                type: [Number, null],
                default: null
            },
            address: {
                type: [String, null],
                default: null
            },
            placeId: {
                type: [String, null],
                default: null
            }
        },
        emits: ['addedMarker', 'markerMoved', 'updatedMarkers'],
        data() {
            return {
                center: {
                    lat: 0,
                    lng: 0
                },
                geocoder: null,
                placesService: null,
                dragendListener: null,
                localMarkers: this.markers,
                mapId: import.meta.env.VITE_GOOGLE_MAPS_ID,
                googleMapsApiKey: import.meta.env.VITE_GOOGLE_MAPS_API_KEY
            };
        },
        watch: {
            localMarkers(newValue) {
                this.$emit('updatedMarkers', newValue);
            },
            placeId(newValue) {
                if(newValue) this.findByPlaceId();
            },
            coordinates(newValue) {
                if(newValue.latitude && newValue.longitude) {
                    this.findByCoordinates(newValue.latitude, newValue.longitude);
                }
            }
        },
        computed: {
            coordinates() {
                return { latitude: this.latitude, longitude: this.longitude };
            }
        },
        methods: {
            markerDragEnd(event) {
                const latLng = event.latLng;
                if (latLng) {

                    const latitude = latLng.lat();
                    const longitude = latLng.lng();

                    this.$emit('markerMoved', {
                        latitude: latitude,
                        longitude: longitude
                    });

                } else {
                    console.error('latLng not available in dragend event');
                }
            },
            initializeGeocoder() {
                if (!this.geocoder) {
                    this.geocoder = new this.$refs.map.api.Geocoder();
                }
            },
            initializePlacesService() {
                if (!this.placesService) {
                    const map = this.$refs.map.map;
                    this.placesService = new this.$refs.map.api.places.PlacesService(map);
                }
            },
            geocodeAddress() {
                this.initializeGeocoder();
                return new Promise((resolve, reject) => {
                    this.geocoder.geocode({ address: this.address }, (results, status) => {
                        if (status === 'OK' && results[0]) {
                            const location = results[0].geometry.location;
                            resolve({
                                latitude: location.lat(),
                                longitude: location.lng(),
                                placeId: results[0].place_id,
                                name: results[0].formatted_address
                            });
                        } else {
                            reject(status);
                        }
                    });
                });
            },
            getPlaceDetailsById() {
                this.initializePlacesService();
                return new Promise((resolve, reject) => {
                    this.placesService.getDetails({ placeId: this.placeId }, (place, status) => {
                        if (status === google.maps.places.PlacesServiceStatus.OK && place) {
                            resolve({
                                latitude: place.geometry.location.lat(),
                                longitude: place.geometry.location.lng(),
                                placeId: place.place_id,
                                name: place.name
                            });
                        } else {
                            reject(status);
                        }
                    });
                });
            },
            async findByAddress() {
                try {

                    const location = await this.geocodeAddress();
                    this.center = { lat: location.latitude, lng: location.longitude };

                    this.addMarker(location);
                    this.$emit('addedMarker', location);

                } catch (error) {
                    console.error('Geocoding failed:', error);
                }
            },
            async findByPlaceId() {
                try {

                    const location = await this.getPlaceDetailsById();
                    this.center = { lat: location.latitude, lng: location.longitude };

                    this.addMarker(location);
                    this.$emit('addedMarker', location);

                } catch (error) {
                    console.error('Place ID lookup failed:', error);
                }
            },
            findByCoordinates(latitude, longitude) {
                if(latitude && longitude) {
                    this.center.lat = latitude;
                    this.center.lng = longitude;

                    const map = this.$refs.map.map;

                    if (map) {
                        map.setCenter(new google.maps.LatLng(latitude, longitude));
                    }

                    const location = {
                        latitude: latitude,
                        longitude: longitude
                    }

                    this.addMarker(location);
                }
            },
            addMarker(location) {
                const newMarker = {
                    options: {
                        position: {
                            lat: location.latitude,
                            lng: location.longitude
                        },
                        title: location.name || '',
                        gmpDraggable: this.gmpDraggable
                    },
                    pinOptions: this.pinOptions
                };

                // Add the marker to local markers array
                this.localMarkers = [newMarker];
            }
        },
        mounted() {
            this.$watch(() => this.$refs.map?.ready, (isReady) => {

                if (!isReady) return;

                if(this.latitude && this.longitude) {
                    this.findByCoordinates(this.latitude, this.longitude);
                }else if (this.placeId) {
                    this.findByPlaceId();
                }else if (this.address) {
                    this.findByAddress();
                }

                /**
                 *  Add Map options
                 *  Reference: https://developers.google.com/maps/documentation/javascript/reference/map#MapOptions
                 */
                this.$refs.map.map.setOptions({
                    controlSize: 30     //  Change the size of the Map controls
                });

            });
        },
        unmounted() {
            if (this.dragendListener) {
                google.maps.event.removeListener(this.dragendListener);
            }
        },
        created() {
        }
    };
</script>
