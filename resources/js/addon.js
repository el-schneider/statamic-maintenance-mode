import MaintenanceMode from './pages/MaintenanceMode.vue';

Statamic.booting(() => {
    Statamic.$inertia.register('MaintenanceMode', MaintenanceMode);
});
