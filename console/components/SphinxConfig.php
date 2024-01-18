<?php

namespace console\components;

use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Class SphinxConfig
 * @package console\components
 */
class SphinxConfig extends Component
{

	/**
	 * @var string $indexPath
	 */
	public $indexPath;

	/**
	 * @var string $pidFilePath
	 */
	public $pidFilePath;

	/**
	 * @var string $logsPath
	 */
	public $logsPath;

	/**
	 * @var array $listen
	 */
	public $listen;

	/**
	 * @var string $listen
	 */
	public $dbConnection;

	/**
	 * @var string $tsvPipeCommand
	 */
	public $tsvPipeCommand;

	protected $_dbConnection;

	/**
	 * @throws \yii\base\InvalidConfigException
	 * @throws \yii\di\NotInstantiableException
	 */
	public function init()
	{
		parent::init();

		if ($this->indexPath === null)
			throw new InvalidConfigException(\get_class($this) . '::$indexPath must be set.');

		if ($this->pidFilePath === null)
			throw new InvalidConfigException(\get_class($this) . '::$pidFilePath must be set.');

		if ($this->logsPath === null)
			throw new InvalidConfigException(\get_class($this) . '::$logsPath must be set.');

		if ($this->listen === null)
			throw new InvalidConfigException(\get_class($this) . '::$listen must be set.');

		if ($this->tsvPipeCommand === null)
			throw new InvalidConfigException(\get_class($this) . '::$tsvPipeCommand must be set.');

		if (!is_array($this->listen))
			$this->listen = [$this->listen];

		$this->_dbConnection = empty($this->dbConnection) ? \Yii::$app->db : \Yii::$app->{$this->dbConnection};
	}

	protected function parseDsn($dsn)
	{

		$turnArray = function ($m) {

			$rt = [];
			foreach ($m as $key => $values)
				foreach ($values as $mCount => $value)
					$rt[$mCount][$key] = $value;

			return $rt;
		};

		preg_match_all('#(?:(?:(?P<driver>\w+):)?(?:(?P<param>\w+[^=])=(?P<value>[a-z0-9\/\.\-_]*)))#ui', $dsn, $matches);

		$parts = array_intersect_key($matches, array_fill_keys(['driver', 'param', 'value'], null));
		$parts = $turnArray($parts);

		$params = [];
		foreach ($parts as $itm) {

			$paramName = $itm['param'];
			$paramValue = $itm['value'];

			switch (true) {

				case (!empty($itm['driver']) && \in_array($itm['param'], ['host', 'unix_socket'])):

					$params['driver'] = $itm['driver'];
					break;
			}

			$params[$paramName] = $paramValue;
		}

		return $params;
	}

	public function getDbParams()
	{
		return array_merge($this->parseDsn($this->_dbConnection->dsn), [
			'username' => $this->_dbConnection->username,
			'password' => $this->_dbConnection->password,
		]);
	}

	/**
	 * @return string
	 */
	public function getIndexPath(): string
	{
		return $this->indexPath;
	}

	/**
	 * @param string $indexPath
	 * @return SphinxConfig
	 */
	public function setIndexPath(string $indexPath): SphinxConfig
	{
		$this->indexPath = $indexPath;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSocketFilePath(): string
	{
		return $this->socketFilePath;
	}

	/**
	 * @param string $socketFilePath
	 * @return SphinxConfig
	 */
	public function setSocketFilePath(string $socketFilePath): SphinxConfig
	{
		$this->socketFilePath = $socketFilePath;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPidFilePath(): string
	{
		return $this->pidFilePath;
	}

	/**
	 * @param string $pidFilePath
	 * @return SphinxConfig
	 */
	public function setPidFilePath(string $pidFilePath): SphinxConfig
	{
		$this->pidFilePath = $pidFilePath;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLogsPath(): string
	{
		return $this->logsPath;
	}

	/**
	 * @param string $logsPath
	 * @return SphinxConfig
	 */
	public function setLogsPath(string $logsPath): SphinxConfig
	{
		$this->logsPath = $logsPath;
		return $this;
	}

}