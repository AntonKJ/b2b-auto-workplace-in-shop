<?php

namespace common\components;

use common\components\deliverySchedule\DeliveryScheduleInterface;
use common\interfaces\RegionEntityInterface;
use common\models\OrderType;
use DateTime;
use DateTimeImmutable;
use Exception;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;

class ExpressDelivery
{

	public const DAY_HOURS_OFFSET = 15;
	public const DAY_HOURS_OFFSET_SCHEDULE = 6;

	/**
	 * @param DateTimeImmutable $dt
	 * @return bool
	 * @throws Exception
	 */
	protected static function isDateIsTomorrow(DateTimeImmutable $dt): bool
	{
		$current = new DateTime();
		$nextDayDt = (clone $current)->modify('+ 1 days');
		return $nextDayDt->format('d.m.Y') === $dt->format('d.m.Y');
	}

	/**
	 * @param RegionEntityInterface|\common\models\Region $region
	 * @param string $orderTypeCategory
	 * @param string $minDeliveryDate
	 * @return bool
	 * @throws Exception
	 */
	public static function hasExpressDelivery(RegionEntityInterface $region, string $orderTypeCategory, string $minDeliveryDate): bool
	{
		if (!$region->getIsRegionInMoscowGroup()) {
			return false;
		}

		if ($orderTypeCategory !== OrderType::CATEGORY_CITY) {
			return false;
		}

		if (!static::isDateIsTomorrow(new DateTimeImmutable($minDeliveryDate))) {
			return false;
		}

		$current = new DateTime();
		$offsetDt = (clone $current)->setTime(static::DAY_HOURS_OFFSET, 0, 0);

		return $current < $offsetDt;
	}

	/**
	 * Воеменное решение, все переделать
	 * @param RegionEntityInterface|\common\models\Region $region
	 * @param string $orderTypeCategory
	 * @param string $minDeliveryDate
	 * @param array $schedules
	 * @return array|null
	 * @throws Exception
	 */
	public static function resolveDaysSchedules(RegionEntityInterface $region,
	                                            string $orderTypeCategory,
	                                            string $minDeliveryDate,
	                                            array $schedules): ?array
	{
		if (!$region->getIsRegionInMoscowGroup()) {
			return null;
		}

		if ($orderTypeCategory !== OrderType::CATEGORY_CITY) {
			return null;
		}

		$current = new DateTime();
		// Если ближайшая дата доставки сегодня
		if ($current->format('d.m.Y') === (new DateTimeImmutable($minDeliveryDate))->format('d.m.Y')) {
			$offsetDt = (clone $current)->setTime(static::DAY_HOURS_OFFSET_SCHEDULE, 0, 0);
			if ($current < $offsetDt) {
				return null;
			}
			if (count($schedules) <= 1) {
				return null;
			}
			return [$minDeliveryDate => array_slice($schedules, 1, null, true)];
		}

		return null;

		if (!static::isDateIsTomorrow(new DateTimeImmutable($minDeliveryDate))) {
			return null;
		}

		$current = new DateTime();
		$offsetDt = (clone $current)->setTime(static::DAY_HOURS_OFFSET_SCHEDULE, 0, 0);
		if ($current < $offsetDt) {
			return null;
		}

		if (count($schedules) <= 1) {
			return null;
		}

		return [$minDeliveryDate => array_slice($schedules, 1, null, true)];
	}

	/**
	 * @param $orderTypeData
	 * @param RegionEntityInterface|null $region
	 * @return mixed
	 * @throws Exception
	 */
	public static function addExpressDeliveryInfo(&$orderTypeData, RegionEntityInterface $region)
	{
		$orderTypeData['expressDelivery'] = null;
		$status = static::hasExpressDelivery(
			$region,
			$orderTypeData['category'],
			$orderTypeData['deliveryDate']['min']['dayDatetime']
		);

		if ($status === true) {
			$deliveryDate = new DateTime();
			/** @var DeliveryScheduleInterface $schedule */
			$schedule = isset($orderTypeData['deliverySchedule']) && [] !== $orderTypeData['deliverySchedule'] ? end($orderTypeData['deliverySchedule']) : null;
			$orderTypeData['expressDelivery'] = [
				'status' => $status,
				'date' => $deliveryDate->format('d.m.Y'),
				'title' => trim(sprintf(
					'Экспресс доставка сегодня (%s) %s',
					$deliveryDate->format('d.m.Y'),
					$schedule !== null ? $schedule['title'] : null
				)),
			];
		}

		return $orderTypeData;
	}

}
