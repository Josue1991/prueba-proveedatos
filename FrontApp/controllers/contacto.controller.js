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
        // ── Búsqueda ──────────────────────────────────────────────────────
        vm.filtroNombre = '';
        vm.filtroCodigo = '';
        vm.filtroActivo = {};

        vm.buscar = function () {
            vm.filtroActivo = {};
            if (vm.filtroNombre) vm.filtroActivo.nombre           = vm.filtroNombre;
            if (vm.filtroCodigo) vm.filtroActivo.codigo_empleado  = vm.filtroCodigo;
        };

        vm.limpiarFiltro = function () {
            vm.filtroNombre = '';
            vm.filtroCodigo = '';
            vm.filtroActivo = {};
        };
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
    .controller('FormCtrl', ['ContactoService', '$routeParams', '$location', '$scope',
        function (ContactoService, $routeParams, $location, $scope) {
            var vm    = this;
            vm.edit   = !!$routeParams.id;
            vm.form   = {};
            vm.error  = null;
            vm.saving = false;
            vm.fotoFile    = null;
            vm.fotoPreview = null;
            vm.activeTab   = 'personales';

            // Preview en vivo al seleccionar foto
            $scope.$watch('vm.fotoFile', function (file) {
                if (!file) { vm.fotoPreview = null; return; }
                var reader = new FileReader();
                reader.onload = function (e) {
                    $scope.$apply(function () { vm.fotoPreview = e.target.result; });
                };
                reader.readAsDataURL(file);
            });

            // Cargar todas las provincias de una sola vez (sin cascade de región)
            vm.provincias = [];
            ContactoService.getProvincias()
                .then(function (res) {
                    vm.provincias = res.data.data;
                });

            // Cambiar pestaña
            vm.setTab = function (tab) {
                vm.error = null;
                vm.activeTab = tab;
            };

            // Continuar: valida tab1 antes de avanzar
            vm.continuar = function () {
                var tab1Required = ['nombre', 'email'];
                var valid = true;
                tab1Required.forEach(function (f) {
                    if ($scope.contactoForm[f]) {
                        $scope.contactoForm[f].$setTouched();
                        if ($scope.contactoForm[f].$invalid) { valid = false; }
                    }
                });
                if (!valid) {
                    vm.error = 'Por favor complete los campos requeridos antes de continuar.';
                    return;
                }
                vm.error = null;
                vm.activeTab = 'laborales';
            };

            // Guardar: valida todos los campos antes de enviar
            vm.guardar = function () {
                $scope.contactoForm.$setSubmitted();

                // Verificar campos requeridos de tab1
                var tab1Invalid = ['nombre', 'email'].some(function (f) {
                    return $scope.contactoForm[f] && $scope.contactoForm[f].$invalid;
                });
                if (tab1Invalid) {
                    vm.activeTab = 'personales';
                    vm.error = 'Hay campos requeridos incompletos en Datos Personales.';
                    return;
                }

                // Verificar campos requeridos de tab2
                var tab2Invalid = ['telefono'].some(function (f) {
                    return $scope.contactoForm[f] && $scope.contactoForm[f].$invalid;
                });
                if (tab2Invalid) {
                    vm.error = 'Por favor complete el telefono en Datos Laborales.';
                    return;
                }

                vm.submit();
            };

            // Ir a reportes
            vm.irReporte = function () {
                $location.path('/reportes');
            };

            // Convierte 'YYYY-MM-DD' → Date local (sin desfase de zona horaria)
            function parseDate(str) {
                if (!str) return null;
                var parts = String(str).substring(0, 10).split('-');
                return new Date(+parts[0], +parts[1] - 1, +parts[2]);
            }

            // Formatea Date → 'YYYY-MM-DD' para enviar al API
            function formatDate(d) {
                if (!d) return null;
                if (typeof d === 'string') return d.substring(0, 10);
                var m = ('0' + (d.getMonth() + 1)).slice(-2);
                var day = ('0' + d.getDate()).slice(-2);
                return d.getFullYear() + '-' + m + '-' + day;
            }

            if (vm.edit) {
                ContactoService.getById($routeParams.id)
                    .then(function (res) {
                        vm.form = res.data.data;
                        vm.form.fecha_nacimiento = parseDate(vm.form.fecha_nacimiento);
                        vm.form.fecha_ingreso    = parseDate(vm.form.fecha_ingreso);
                        if (vm.form.sueldo !== null && vm.form.sueldo !== undefined) {
                            vm.form.sueldo = parseFloat(vm.form.sueldo);
                        }
                    })
                    .catch(function () {
                        vm.error = 'No se pudo cargar el contacto.';
                    });
            }

            vm.submit = function () {
                vm.saving = true;
                vm.error  = null;

                var payload = angular.copy(vm.form);
                payload.fecha_nacimiento = formatDate(payload.fecha_nacimiento);
                payload.fecha_ingreso    = formatDate(payload.fecha_ingreso);

                var promise = vm.edit
                    ? ContactoService.update($routeParams.id, payload)
                    : ContactoService.create(payload);

                promise
                    .then(function (res) {
                        var contactoId = res.data.data.id;
                        if (vm.fotoFile) {
                            return ContactoService.uploadFoto(contactoId, vm.fotoFile);
                        }
                    })
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
