'use strict';

angular
    .module('contactoApp', ['ngRoute'])
    .config(['$routeProvider', '$locationProvider', function ($routeProvider, $locationProvider) {

        $routeProvider
            .when('/', {
                templateUrl : 'views/list.html',
                controller  : 'ListCtrl',
                controllerAs: 'vm'
            })
            .when('/nuevo', {
                templateUrl : 'views/form.html',
                controller  : 'FormCtrl',
                controllerAs: 'vm'
            })
            .when('/editar/:id', {
                templateUrl : 'views/form.html',
                controller  : 'FormCtrl',
                controllerAs: 'vm'
            })
            .when('/ver/:id', {
                templateUrl : 'views/detail.html',
                controller  : 'DetailCtrl',
                controllerAs: 'vm'
            })
            .when('/reportes', {
                templateUrl : 'views/reportes.html',
                controller  : 'ReportesCtrl',
                controllerAs: 'vm'
            })
            .otherwise({ redirectTo: '/' });
    }])
    // Directiva para binding de input[type=file] con ng-model
    .directive('fileModel', ['$parse', function ($parse) {
        return {
            restrict: 'A',
            link: function (scope, element, attrs) {
                var model = $parse(attrs.fileModel);
                element.bind('change', function () {
                    scope.$apply(function () {
                        model.assign(scope, element[0].files[0]);
                    });
                });
            }
        };
    }])
    // Validador de cédula ecuatoriana (10 dígitos, algoritmo módulo 10)
    .directive('cedulaEc', function () {
        return {
            require: 'ngModel',
            link: function (scope, el, attrs, ctrl) {
                ctrl.$validators.cedulaEc = function (value) {
                    if (!value) return true; // campo opcional
                    if (!/^\d{10}$/.test(value)) return false;
                    var prov = parseInt(value.substring(0, 2), 10);
                    if (prov < 1 || prov > 24) return false;
                    var digits = value.split('').map(Number);
                    if (digits[2] > 5) return false; // solo personas naturales
                    var coef = [2, 1, 2, 1, 2, 1, 2, 1, 2];
                    var sum  = 0;
                    for (var i = 0; i < 9; i++) {
                        var v = digits[i] * coef[i];
                        sum += v > 9 ? v - 9 : v;
                    }
                    var check = sum % 10 === 0 ? 0 : 10 - (sum % 10);
                    return check === digits[9];
                };
            }
        };
    });
