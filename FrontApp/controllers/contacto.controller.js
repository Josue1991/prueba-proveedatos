'use strict';

// ── List ──────────────────────────────────────────────────────────────────
angular
    .module('contactoApp')
    .controller('ListCtrl', ['ContactoService', '$location', function (ContactoService, $location) {
        var vm = this;
        vm.contactos  = [];
        vm.error      = null;
        vm.loading    = true;
        vm.orderBy    = 'id';
        vm.orderDesc  = false;

        ContactoService.getAll()
            .then(function (res) {
                vm.contactos = res.data.data;
            })
            .catch(function () {
                vm.error = 'Error al cargar los contactos.';
            })
            .finally(function () {
                vm.loading = false;
            });

        vm.sortBy = function (field) {
            if (vm.orderBy === field) {
                vm.orderDesc = !vm.orderDesc;
            } else {
                vm.orderBy   = field;
                vm.orderDesc = false;
            }
        };

        vm.sortIcon = function (field) {
            if (vm.orderBy !== field) return 'bi bi-chevron-expand text-muted';
            return vm.orderDesc ? 'bi bi-chevron-down' : 'bi bi-chevron-up';
        };

        vm.thClass = function (field) {
            return vm.orderBy === field ? 'bg-primary text-white' : '';
        };

        vm.exportar = function (format) {
            var dir = vm.orderDesc ? 'desc' : 'asc';
            var url = ContactoService.exportUrl(format, vm.orderBy, dir);
            window.open(url, '_blank');
        };

        vm.ver = function (id) {
            $location.path('/ver/' + id);
        };

        vm.editar = function (id) {
            $location.path('/editar/' + id);
        };

        vm.eliminar = function (id) {
            if (!confirm('¿Eliminar este contacto?')) return;

            ContactoService.remove(id)
                .then(function () {
                    vm.contactos = vm.contactos.filter(function (c) {
                        return c.id !== id;
                    });
                })
                .catch(function () {
                    vm.error = 'Error al eliminar el contacto.';
                });
        };
    }]);

// ── Form (create / edit) ──────────────────────────────────────────────────
angular
    .module('contactoApp')
    .controller('FormCtrl', ['ContactoService', '$routeParams', '$location',
        function (ContactoService, $routeParams, $location) {
            var vm    = this;
            vm.edit   = !!$routeParams.id;
            vm.title  = vm.edit ? 'Editar contacto' : 'Nuevo contacto';
            vm.form   = {};
            vm.error  = null;
            vm.saving = false;

            if (vm.edit) {
                ContactoService.getById($routeParams.id)
                    .then(function (res) {
                        vm.form = res.data.data;
                    })
                    .catch(function () {
                        vm.error = 'No se pudo cargar el contacto.';
                    });
            }

            vm.submit = function () {
                vm.saving = true;
                vm.error  = null;

                var promise = vm.edit
                    ? ContactoService.update($routeParams.id, vm.form)
                    : ContactoService.create(vm.form);

                promise
                    .then(function () {
                        $location.path('/');
                    })
                    .catch(function (res) {
                        vm.error  = (res.data && res.data.error) || 'Error al guardar.';
                        vm.saving = false;
                    });
            };

            vm.cancelar = function () {
                $location.path('/');
            };
        }
    ]);

// ── Detail ────────────────────────────────────────────────────────────────
angular
    .module('contactoApp')
    .controller('DetailCtrl', ['ContactoService', '$routeParams', '$location',
        function (ContactoService, $routeParams, $location) {
            var vm    = this;
            vm.contacto = null;
            vm.error    = null;

            ContactoService.getById($routeParams.id)
                .then(function (res) {
                    vm.contacto = res.data.data;
                })
                .catch(function () {
                    vm.error = 'Contacto no encontrado.';
                });

            vm.volver = function () {
                $location.path('/');
            };

            vm.editar = function (id) {
                $location.path('/editar/' + id);
            };
        }
    ]);

// ── Reportes ──────────────────────────────────────────────────────────────
angular
    .module('contactoApp')
    .controller('ReportesCtrl', ['ContactoService', function (ContactoService) {
        var vm    = this;
        vm.reportes = [];
        vm.error    = null;
        vm.loading  = true;

        ContactoService.getReportes()
            .then(function (res) {
                vm.reportes = res.data.data;
            })
            .catch(function () {
                vm.error = 'Error al cargar el historial de reportes.';
            })
            .finally(function () {
                vm.loading = false;
            });

        vm.aprobar = function (id) {
            ContactoService.updateReporte(id, { estado: 'aprobado' })
                .then(function (res) {
                    var r = vm.reportes.find(function (x) { return x.id === id; });
                    if (r) r.estado = 'aprobado';
                })
                .catch(function () {
                    vm.error = 'Error al aprobar el reporte.';
                });
        };

        vm.rechazar = function (id) {
            ContactoService.updateReporte(id, { estado: 'rechazado' })
                .then(function (res) {
                    var r = vm.reportes.find(function (x) { return x.id === id; });
                    if (r) r.estado = 'rechazado';
                })
                .catch(function () {
                    vm.error = 'Error al rechazar el reporte.';
                });
        };
    }]);
