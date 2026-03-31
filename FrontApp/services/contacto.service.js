'use strict';

angular
    .module('contactoApp')
    .factory('ContactoService', ['$http', function ($http) {

        var API       = 'http://localhost:8080/api/contactos';
        var API_REP   = 'http://localhost:8080/api/reportes';

        return {
            // ── Contactos ────────────────────────────────────────────────
            getAll: function () {
                return $http.get(API);
            },

            getById: function (id) {
                return $http.get(API + '/' + id);
            },

            create: function (contacto) {
                return $http.post(API, contacto);
            },

            update: function (id, contacto) {
                return $http.put(API + '/' + id, contacto);
            },

            remove: function (id) {
                return $http.delete(API + '/' + id);
            },

            // ── Export URL builder (opened in new tab by controller) ─────
            exportUrl: function (format, orderBy, dir) {
                return API + '/export?format=' + format
                    + '&orderBy=' + orderBy
                    + '&dir=' + dir;
            },

            // ── Reportes ─────────────────────────────────────────────────
            getReportes: function () {
                return $http.get(API_REP);
            },

            updateReporte: function (id, data) {
                return $http.put(API_REP + '/' + id, data);
            }
        };
    }]);
