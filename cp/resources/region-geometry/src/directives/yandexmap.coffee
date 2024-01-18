"use strict"

angular.module 'app'
.directive 'yandexMap', [
  () ->
    {
      restrict: 'EA'
      scope:
        initParams: '='
        onAfterInit: '&'

      controller: [

        '$scope'
        '$element'
        'YandexMapService'

        ($scope, $element, YandexMapService) ->

          $scope.map = null

          YandexMapService.ready()
          .then ( ym ) ->

            $scope.map = new ym.Map $element[0], $scope.initParams, {}
            $scope.onAfterInit { $target: $scope.map }
            return

          $scope.$on '$destroy', () ->

            if $scope.map
              $scope.map.destroy()

            return

          return

      ]
    }
]