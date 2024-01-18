<?php

namespace common\components\debug;

use Yii;
use yii\base\Event;
use yii\base\View;
use yii\base\ViewEvent;
use yii\caching\ApcCache;
use yii\caching\Cache;
use yii\caching\CacheInterface;
use yii\debug\Panel;


class CachePanel extends Panel
{
	private $_cacheComponents = [];

	public function init()
	{
		parent::init();

	}


	/**
	 * Returns array of caches in the system, keys are cache components names, values are class names.
	 * @param array $cachesNames caches to be found
	 * @return array
	 */
	private function findCaches(array $cachesNames = [])
	{
		$caches = [];
		$components = Yii::$app->getComponents();

		foreach ($components as $name => $component) {

			if ($component instanceof CacheInterface) {

				$caches[$name] = get_class($component);
			} elseif (is_array($component) && isset($component['class']) && $this->isCacheClass($component['class'])) {

				$caches[$name] = $component['class'];
			} elseif (is_string($component) && $this->isCacheClass($component)) {

				$caches[$name] = $component;
			} elseif ($component instanceof \Closure) {

				$cache = Yii::$app->get($name);
				if ($this->isCacheClass($cache)) {

					$cacheClass = \get_class($cache);
					$caches[$name] = $cacheClass;
				}
			}
		}

		return $caches;
	}

	private function isCacheClass($className)
	{
		return is_subclass_of($className, 'yii\caching\CacheInterface');
	}

	private function canBeFlushed($className)
	{
		return !is_a($className, ApcCache::class, true) || PHP_SAPI !== 'cli';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'Caches';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSummary()
	{
		$url = $this->getUrl();
		$count = count($this->data);
		return "<div class=\"yii-debug-toolbar__block\"><a href=\"$url\">Views <span class=\"yii-debug-toolbar__label yii-debug-toolbar__label_info\">$count</span></a></div>";
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDetail()
	{
		return '<ol><li>' . implode('</li><li>', $this->data) . '</li></ol>';
	}

	/**
	 * {@inheritdoc}
	 */
	public function save()
	{
		return $this->_viewFiles;
	}
}