<?php

namespace console\controllers\import;

use common\models\DiskColor;
use common\models\DiskModel;
use common\models\DiskType;
use common\models\DiskVariation;
use yii\base\Action;
use yii\console\ExitCode;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use function count;

class DisksAction extends Action
{

	/**
	 * Подготавливает файл дисков `disks`
	 * @param string $inputFileDisk
	 * @param string $inputFileBrand
	 * @return int
	 */
	public function run(string $inputFileDisk, string $inputFileBrand)
	{

		$columnsDisk = array_flip([
			'disk_id',
			'diam',
			'params',
			'pn',
			'pcd',
			'pcd2',
			'et',
			'dia',
			'type',
			'type_name',
			'color_name',
			'prod',
			'model',
			'color',
			'manuf_code',
			'origin_country',
			'descr',
			'brand_group',
			'extra_code',
		]);

		$columnsBrand = array_flip([
			'id',
			'code',
			'name',
			'logo',
			'pos',
			'code1c',
			'published',
			'image_version',
		]);

		$disksData = $this->controller->loadFile($inputFileDisk, $columnsDisk, false, 1);
		$brands = $this->controller->loadFile($inputFileBrand, $columnsBrand);

		$brands = array_map(static function ($v) {
			return StringHelper::explode($v, "\t", false);
		}, $brands);

		$brands = ArrayHelper::index($brands, static function ($row) use ($columnsBrand) {
			return mb_strtolower($row[$columnsBrand['code1c']]);
		});

		$models = [];
		$diskTypes = [];
		$diskColors = [];
		$diskVariations = [];

		$getDiskType = static function ($title) use (&$diskTypes) {

			$title = trim($title);
			$key = crc32(mb_strtolower($title));

			if (!isset($diskTypes[$key])) {

				$diskTypes[$key] = [
					'id' => $key,
					'title' => $title,
					'slug' => Inflector::slug($title),
					'status' => DiskType::STATUS_PUBLISHED,
					'sortorder' => 0,
					'created_at' => date('Y-m-d H:i:s'),
					'updated_at' => date('Y-m-d H:i:s'),
				];
			}

			return $diskTypes[$key];
		};

		$getDiskColor = static function ($title, $params = []) use (&$diskColors) {

			$title = trim($title);

			$key = crc32(mb_strtolower(implode('_', [$title, trim($params['code'] ?? '')])));

			if (!isset($diskColors[$key])) {

				$colorSlug = Inflector::slug(mb_strtolower(implode('-', [$title, trim($params['code'] ?? '')])));

				$diskColors[$key] = array_merge([
					'id' => $key,
					'code' => null,
					'title' => $title,
					'slug' => $colorSlug,
					'status' => DiskColor::STATUS_PUBLISHED,
					'sortorder' => 0,
					'created_at' => date('Y-m-d H:i:s'),
					'updated_at' => date('Y-m-d H:i:s'),
				], $params);
			}

			return $diskColors[$key];
		};

		$disksOut = [];
		$disksOutSkipped = [];
		foreach ($disksData as $diskKey => $diskRowStr) {

			$diskRow = array_combine(array_keys($columnsDisk), explode("\t", $diskRowStr));

			if (empty($diskRow['type_name'])) {
				$diskRow['type_name'] = 'Стальной';
			}

			$brandCode = mb_strtolower($diskRow['prod']);
			$brandId = $brands[$brandCode][0] ?? null;

			if ($brandId === null) {
				$disksOutSkipped[] = $diskRowStr;
				continue;
			}

			$diskModelKey = Inflector::slug(preg_replace('/[^а-яa-z\d]/ui', '', trim($diskRow['model'])));
			if (!isset($models[$brandId][$diskModelKey])) {

				$diskModel = [
					'id' => crc32("{$brandId} {$diskModelKey}"),
					'brand_id' => $brandId,
					'type_id' => $getDiskType($diskRow['type_name'])['id'],
					'title' => $diskRow['model'],
					'slug' => Inflector::slug($diskRow['model']),
					'status' => DiskModel::STATUS_PUBLISHED,
					'sortorder' => 0,
					'description_short' => '\N',
					'description' => '\N',
					'created_at' => date('Y-m-d H:i:s'),
					'updated_at' => date('Y-m-d H:i:s'),
				];

				$models[$brandId][$diskModelKey] = $diskModel;
			}

			$modelId = $models[$brandId][$diskModelKey]['id'];

			$colorId = $getDiskColor($diskRow['color_name'], ['code' => $diskRow['color']])['id'];

			if (!isset($diskVariations[$modelId][$colorId])) {

				$_title = trim("{$diskRow['type_name']} {$diskRow['color_name']}");
				$_slug = trim("{$diskRow['type_name']} {$diskRow['color_name']} {$diskRow['color']}");

				if (empty($_title)) {
					$_title = $diskRow['model'];
				}

				if (empty($_slug)) {
					$_slug = $diskRow['model'];
				}

				$variationSlug = Inflector::slug($_slug);

				$diskVariations[$modelId][$colorId] = [
					'id' => crc32(implode('_', [$modelId, $colorId, $_slug])),
					'model_id' => $modelId,
					'color_id' => $colorId,
					'title' => $_title,
					'slug' => $variationSlug,
					'status' => DiskVariation::STATUS_PUBLISHED,
					'sortorder' => 0,
					'created_at' => date('Y-m-d H:i:s'),
					'updated_at' => date('Y-m-d H:i:s'),
					'slug_img_old' => static::transliterate(str_replace(' ', '_', $diskRow['model'])),
				];
			}

			$variationId = $diskVariations[$modelId][$colorId]['id'];

			$parts = explode('/', $diskRow['diam']);

			$diameter = isset($parts[0]) ? (float)$parts[0] : 0.0;
			if ($diameter < 1.0) {
				continue;
			}

			$width = isset($parts[1]) ? (float)$parts[1] : 0.0;

			$disksOut[] = array_merge($diskRow, [
				'diameter' => $diameter,
				'width' => $width,
				'brand_id' => $brandId,
				'model_id' => $modelId,
				'variation_id' => $variationId,
			]);
		}

		$outDataPrepare = static function ($data) {

			$firstRow = reset($data);
			array_unshift($data, array_keys($firstRow));

			return array_map(static function ($row) {
				return implode("\t", $row);
			}, $data);
		};

		$variationsOut = [];

		foreach ($diskVariations as $modelVariations) {
			foreach ($modelVariations as $variation) {
				$variationsOut[] = $variation;
			}
		}

		$modelsOut = [];

		foreach ($models as $brandModel) {
			foreach ($brandModel as $model) {
				$modelsOut[] = $model;
			}
		}

		$this->controller->stdout('Skipped: ' . count($disksOutSkipped) . ', Ok: ' . count($disksOut) . "\n");

		$this->controller->saveFile($inputFileDisk . '.skipped.tsv', implode("\n", $disksOutSkipped));

		$this->controller->saveFile($inputFileDisk . '.tsv', implode("\n", $outDataPrepare($disksOut)));

		$this->controller->saveFile($inputFileDisk . '.types.tsv', implode("\n", $outDataPrepare($diskTypes)));
		$this->controller->saveFile($inputFileDisk . '.colors.tsv', implode("\n", $outDataPrepare($diskColors)));
		$this->controller->saveFile($inputFileDisk . '.models.tsv', implode("\n", $outDataPrepare($modelsOut)));
		$this->controller->saveFile($inputFileDisk . '.variations.tsv', implode("\n", $outDataPrepare($variationsOut)));

		return ExitCode::OK;
	}

	/**
	 * @param string $txt
	 * @return string
	 */
	protected static function transliterate($txt)
	{

		$map = [

			'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'E', 'Ж' => 'ZH', 'З' => 'Z',
			'И' => 'I', 'Й' => 'I', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R',
			'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'KH', 'Ц' => 'TS', 'Ч' => 'CH', 'Ш' => 'SH',
			'Щ' => 'SHCH', 'Ы' => 'Y', 'Э' => 'E', 'Ю' => 'YU', 'Я' => 'YA',
			'Ъ' => '', 'Ь' => '',

			'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z',
			'и' => 'i', 'й' => 'i', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r',
			'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh',
			'щ' => 'shch', 'ы' => 'y', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
			'ъ' => '', 'ь' => '',

		];

		return strtr($txt, $map);
	}

}
