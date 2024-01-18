"use strict"

angular.module('app').factory 'ZonesResource', [
	'$resource'
	'apiUrl'
	($resource, apiUrl) ->
		$resource "#{apiUrl}zones/:action/:id/.json", {action: '@action', id: '@id'}, {

			query:
				cache: false
				method: 'GET'
				isArray: true
				responseType: 'json'

		}
]

angular.module('app').service 'Zones', [
	'ZonesResource'
	(ZonesResource) ->
		{
			query: () ->
				ZonesResource.query {}
					.$promise
		}

]

