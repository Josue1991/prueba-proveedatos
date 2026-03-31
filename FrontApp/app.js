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
    }]);
