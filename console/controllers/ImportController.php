<?php

namespace console\controllers;

use console\controllers\import\DisksAction;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use function array_slice;
use function count;

class ImportController extends Controller
{

	public function actions()
	{
		$actions = parent::actions();

		$actions['disks'] = DisksAction::class;

		return $actions;
	}

	/**
	 * Подготавливает файл автомоделей `auto_models`
	 * @param string $inputFile входной файл
	 * @param null|string $outputFile выходной файл
	 * @return int
	 */
	public function actionAutoModels(string $inputFile, ?string $outputFile)
	{

		$columnMapper = array_flip([
			'code1c',
			'id',
			'brand',
			'model',
			'yStart',
			'yEnd',
			'engine',
		]);

		$data = $this->loadFile($inputFile, $columnMapper);

		$slugFunc = static function (string $slug) {
			return Inflector::slug(preg_replace('/[_]+/u', '-', $slug));
		};

		$yearsFunc = static function (string $year) {

			$year = trim(preg_replace('/[^\d]/ui', '', $year));
			return (int)$year == 0 ? 'x' : $year;
		};

		$data = array_map(static function ($v) use (&$slugFunc, &$yearsFunc, $columnMapper) {

			$row = explode("\t", $v);

			$row[] = $slugFunc($row[$columnMapper['brand']] ?? null);
			$row[] = $slugFunc($row[$columnMapper['model']] ?? null);

			$yearSlug = [
				'start' => $yearsFunc($row[$columnMapper['yStart']] ?? null),
				'end' => $yearsFunc($row[$columnMapper['yEnd']] ?? null),
			];

			$yearSlug = array_filter($yearSlug);

			if ($yearSlug !== [] && count($yearSlug) < 2) {
				if (isset($yearSlug['start'])) {
					$yearSlug[] = 'after';
				} elseif (isset($yearSlug['end'])) {
					$yearSlug[] = 'until';
				}
			}

			$id = $slugFunc(implode(' ', [
				$row[$columnMapper['brand']] ?? null,
				$row[$columnMapper['model']] ?? null,
				implode('-', array_values($yearSlug)),
				$row[$columnMapper['engine']] ?? null,
			]));

			$row[] = preg_replace('/-{2,}/ui', '-', $id);

			return implode("\t", $row);
		}, $data);

		if (empty($outputFile))
			$outputFile = $inputFile;

		$this->saveFile($outputFile, implode("\n", $data));

		return ExitCode::OK;
	}

	public function saveFile(string $file, string $data, $convertEncoding = false)
	{

		if ($convertEncoding)
			$data = mb_convert_encoding($data, 'cp1251');

		if (($result = file_put_contents($file, $data)) === false) {

			$this->stderr("Can't write file: {$file}\n");
			die();
		}

		return $result;
	}

	/**
	 * @param string $file
	 * @param array|null $validateColumns
	 * @return string[]
	 */
	public function loadFile(string $file, ?array $validateColumns = null, $convertEncoding = false, $skipRows = 0): array
	{

		if (!file_exists($file) || !is_file($file)) {
			$this->stderr("File not found: {$file}\n");
			die();
		}

		if (($data = file_get_contents($file)) === false) {
			$this->stderr("Can't read file: {$file}\n");
			die();
		}

		if ((bool)$convertEncoding) {
			$data = mb_convert_encoding($data, 'UTF-8', 'cp1251');
		}

		$data = StringHelper::explode($data, "\n", false, true);

		if ((int)$skipRows > 0) {
			$data = array_slice($data, $skipRows);
		}

		if (is_array($validateColumns)) {

			$firstRow = reset($data);
			if (($firstRow === false || ($hasColumn = substr_count($firstRow, "\t") + 1) !== ($cCount = count($validateColumns)))) {
				$this->stderr("Invalid column (need - {$cCount}, has - {$hasColumn}) count in file: {$file}\n");
				die();
			}
		}

		return $data;
	}

}
