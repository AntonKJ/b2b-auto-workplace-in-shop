'use strict'

class DrawModalController

	@$inject: [
		'$uibModalInstance'
	]

	constructor: (@$uibModalInstance) ->

		@data = null
		return

	ok: () ->

		@$uibModalInstance.close @data
		return

	cancel: () ->

		@$uibModalInstance.dismiss false
		return


angular.module 'app'
	.controller 'app.draw.modal.ctrl', DrawModalController