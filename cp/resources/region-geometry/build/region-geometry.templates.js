//HEAD 
(function(app) {
try { app = angular.module("templates"); }
catch(err) { app = angular.module("templates", []); }
app.run(["$templateCache", function($templateCache) {
"use strict";

$templateCache.put("partials/draw.html","<a class=\"btn btn-default\" ui-sref=\"app.from-yandex-region\">Выбрать из регионов яндекса</a>\n" +
    "<a class=\"btn btn-success\" ng-click=\"ctrl.openModal()\">Добавить регион по точкам</a>\n" +
    "<hr>\n" +
    "\n" +
    "<div yandex-map\n" +
    "     style=\"height: 500px;\"\n" +
    "     init-params=\"ctrl.mapParams\"\n" +
    "     on-after-init=\"ctrl.onMapInit($target)\"></div>\n" +
    "\n" +
    "<div ng-if=\"ctrl.polygonData != null\">\n" +
    "\n" +
    "	<h3>Кол-во точек: {{ctrl.polygonData.polygon.length}}</h3>\n" +
    "	<textarea rows=\"10\" style=\"box-sizing: border-box; width: 100%;\">{{ctrl.polygonData.polygon}}</textarea>\n" +
    "\n" +
    "	<h3>Длина строки: {{ctrl.polygonData.polygonSlava.length}} &mdash; Слава</h3>\n" +
    "	<textarea rows=\"10\" style=\"box-sizing: border-box; width: 100%;\">{{ctrl.polygonData.polygonSlava}}</textarea>\n" +
    "\n" +
    "	<h3>Запрос для обновления</h3>\n" +
    "\n" +
    "	<div class=\"form-row\">\n" +
    "		<label for=\"delivery_zone_id\">ID для обновления `delivery_zone`</label>\n" +
    "		<input id=\"delivery_zone_id\"\n" +
    "		       class=\"form-control\"\n" +
    "		       ng-change=\"ctrl.doUpdateMysqlCommand()\"\n" +
    "		       ng-model=\"ctrl.deliveryZoneId\"/>\n" +
    "	</div>\n" +
    "\n" +
    "	<div style=\"margin-top: 1em;\" ng-if=\"ctrl.deliveryZoneId != null && ctrl.deliveryZoneId != ''\">\n" +
    "	<pre>\n" +
    "		{{ctrl.mysqlUpdateCommand}}\n" +
    "	</pre>\n" +
    "	</div>\n" +
    "\n" +
    "</div>\n" +
    "\n" +
    "<hr>\n" +
    "")

$templateCache.put("partials/draw.modal.html","<div class=\"modal-header\">\n" +
    "	<h3 class=\"modal-title\">Вставте список точек в поле</h3>\n" +
    "</div>\n" +
    "\n" +
    "<div class=\"modal-body\">\n" +
    "	<textarea class=\"form-control\" ng-model=\"ctrlModal.data\"></textarea>\n" +
    "</div>\n" +
    "\n" +
    "<div class=\"modal-footer\">\n" +
    "	<a class=\"btn btn-default pull-left\" href ng-click=\"ctrlModal.cancel()\">Отмена</a>\n" +
    "	<a class=\"btn btn-success\" href ng-click=\"ctrlModal.ok()\">Добавить на карту</a>\n" +
    "</div>")

$templateCache.put("partials/from-yandex-region.html","<a class=\"btn btn-default\" ui-sref=\"app.draw\">Нарисовать свой регион</a>\n" +
    "<hr>\n" +
    "\n" +
    "<div yandex-map\n" +
    "     style=\"height: 300px;\"\n" +
    "     init-params=\"mapParams\"\n" +
    "     on-after-init=\"onMapInit($target)\"\n" +
    "></div>\n" +
    "\n" +
    "<div ng-if=\"selected != null\">\n" +
    "\n" +
    "	<h3>{{selected.title}} [#{{selected.id}}]</h3>\n" +
    "	<textarea rows=\"10\" style=\"box-sizing: border-box; width: 100%;\">{{selected.polygon}}</textarea>\n" +
    "\n" +
    "	<h3>{{selected.title}} [#{{selected.id}}] &mdash; Слава</h3>\n" +
    "	<textarea rows=\"10\" style=\"box-sizing: border-box; width: 100%;\">{{selected.polygonSlava}}</textarea>\n" +
    "\n" +
    "</div>")

$templateCache.put("partials/index.html","<div class=\"index-page\">\n" +
    "	<div class=\"map-wrapper\">\n" +
    "		<div yandex-map\n" +
    "		     style=\"height: 500px;\"\n" +
    "		     init-params=\"ctrl.mapParams\"\n" +
    "		     on-after-init=\"ctrl.onMapInit($target)\"></div>\n" +
    "	</div>\n" +
    "	<div class=\"zones-list\">\n" +
    "		<div class=\"table-wrapper\">\n" +
    "			<table>\n" +
    "				<tbody>\n" +
    "				<tr ng-repeat=\"zone in ctrl.zones track by zone.id\"\n" +
    "				    ng-style=\"{true:{background: '#eaeaea'},false:{}}[zone.active]\">\n" +
    "					<td class=\"text-center\">{{zone.id}}</td>\n" +
    "					<td><a href ng-click=\"ctrl.setActiveZone(zone)\">{{zone.title}}</a></td>\n" +
    "					<td class=\"text-center\">{{zone.order_type_id}}</td>\n" +
    "				</tr>\n" +
    "				</tbody>\n" +
    "			</table>\n" +
    "		</div>\n" +
    "	</div>\n" +
    "</div>\n" +
    "")
}]);
})();