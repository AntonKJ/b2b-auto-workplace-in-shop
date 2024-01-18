<?php

namespace common\components;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;
use yii\log\Logger;

class PsrLoggerAdapter implements LoggerInterface
{

	use LoggerTrait;

	/**
	 * @var Logger
	 */
	protected $logger;
	/**
	 * @var string
	 */
	protected $category;

	/**
	 * PsrLoggerAdapter constructor.
	 * @param Logger $logger
	 * @param string $category
	 */
	public function __construct(Logger $logger, string $category = 'application')
	{
		$this->logger = $logger;
		$this->category = $category;
	}

	protected static function logLevelOptions(): array
	{
		return [
			LogLevel::EMERGENCY => Logger::LEVEL_WARNING,
			LogLevel::ALERT => Logger::LEVEL_WARNING,
			LogLevel::CRITICAL => Logger::LEVEL_WARNING,
			LogLevel::ERROR => Logger::LEVEL_ERROR,
			LogLevel::WARNING => Logger::LEVEL_WARNING,
			LogLevel::NOTICE => Logger::LEVEL_INFO,
			LogLevel::INFO => Logger::LEVEL_INFO,
			LogLevel::DEBUG => Logger::LEVEL_TRACE,
		];
	}

	protected function logLevelMapper(string $level): string
	{
		return static::logLevelOptions()[$level] ?? $level;
	}

	public function log($level, $message, array $context = [])
	{
		$logMessage = $message;
		if ($context !== []) {
			$logMessage = [
				'message' => $message,
				'context' => $context,
			];
		}
		$this->logger->log($logMessage, $this->logLevelMapper($level), $this->category);
	}

}
