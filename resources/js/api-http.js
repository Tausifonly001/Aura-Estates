(function() {
    'use strict';

    function apiBase() {
        return (window.AURA_API_BASE || 'api/').replace(/\/?$/, '/');
    }

    var app = angular.module('apiHttp', []);

    app.factory('csrfToken', ['$q', '$injector', function($q, $injector) {
        var token = null;
        var loading = null;
        var $http = null;

        function getHttp() {
            if (!$http) {
                $http = $injector.get('$http');
            }
            return $http;
        }

        return {
            get: function() {
                if (token) {
                    return $q.when(token);
                }
                if (!loading) {
                    loading = getHttp().get(apiBase() + 'auth?action=me', { withCredentials: true })
                        .then(function(res) {
                            var data = res.data && res.data.data ? res.data.data : {};
                            token = data._csrf_token || null;
                            return token;
                        }, function() {
                            return null;
                        })
                        .finally(function() {
                            loading = null;
                        });
                }
                return loading;
            },
            set: function(value) {
                token = value || null;
            }
        };
    }]);

    app.config(['$httpProvider', function($httpProvider) {
        $httpProvider.interceptors.push(['$q', 'csrfToken', function($q, csrfToken) {
            return {
                request: function(config) {
                    var method = (config.method || 'GET').toUpperCase();
                    if (['POST', 'PUT', 'DELETE', 'PATCH'].indexOf(method) === -1) {
                        return config;
                    }

                    return csrfToken.get().then(function(token) {
                        if (token) {
                            config.headers = config.headers || {};
                            config.headers['X-CSRF-TOKEN'] = token;
                        }
                        return config;
                    });
                },
                response: function(response) {
                    var data = response.data && response.data.data;
                    if (data && data._csrf_token) {
                        csrfToken.set(data._csrf_token);
                    }
                    return response;
                }
            };
        }]);
    }]);
})();
