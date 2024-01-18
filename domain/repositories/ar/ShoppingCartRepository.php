<?php

namespace domain\repositories\ar;

use domain\entities\shoppingCart\collections\ShoppingCartGoodCollection;
use domain\entities\shoppingCart\collections\ShoppingCartGoodCollectionInterface;
use domain\entities\shoppingCart\dto\ShoppingCartDto;
use domain\entities\shoppingCart\ShoppingCart;
use domain\entities\shoppingCart\ShoppingCartGood;
use domain\entities\shoppingCart\ShoppingCartToken;
use domain\interfaces\ShoppingCartRepositoryInterface;
use domain\repositories\Hydrator;
use domain\repositories\NotFoundException;
use yii\base\ErrorException;
use yii\db\Connection;
use yii\db\Query;
use yii\db\Transaction;

/**
 * Class ShoppingCartRepository
 * @package domain\repositories\ar
 * @deprecated
 */
class ShoppingCartRepository extends RepositoryBase implements ShoppingCartRepositoryInterface
{

	private $hydrator;

	public function __construct(Hydrator $hydrator)
	{
		$this->hydrator = $hydrator;
	}

	protected function _populate(array $data): ShoppingCart
	{
		$entityData = [
			'id' => (int)$data['id'],
			'userId' => $data['user_id'],
			'updatedAt' => new \DateTime($data['updated_at']),
			'token' => isset($data['token']) ? $this->hydrator->hydrate(ShoppingCartToken::class, [
				'token' => $data['token'],
			]) : null,
			'items' => new ShoppingCartGoodCollection((isset($data['items']) && is_array($data['items']) ? array_map(function ($v) {
				return $this->_populateGood($v);
			}, $data['items']) : [])),
		];

		return $this->hydrator->hydrate(ShoppingCart::class, $entityData);
	}

	protected function _populateGood(array $data): ShoppingCartGood
	{
		return $this->hydrator->hydrate(ShoppingCartGood::class, [
			'id' => (int)$data['id'],
			'cartId' => $data['cart_id'],
			'entityType' => $data['entity_type'],
			'entityId' => $data['entity_id'],
			'quantity' => $data['quantity'],
			'price' => $data['price'],
		]);
	}

	protected function getShoppingCartTableName()
	{
		return '{{%shopping_cart}}';
	}

	protected function getShoppingCartTokenTableName()
	{
		return '{{%shopping_cart_token}}';
	}

	protected function getShoppingCartItemsTableName()
	{
		return '{{%shopping_cart_token}}';
	}

	/**
	 * @param int $userId
	 * @return ShoppingCart
	 */
	public function findShoppingCartByUserId(int $userId): ShoppingCart
	{

		$cart = (new Query())
			->from($this->getShoppingCartTableName())
			->andWhere(['user_id' => $userId])
			->one();

		if ($cart === false)
			throw new NotFoundException();

		return $this->_populate($cart);
	}

	/**
	 * @param string $token
	 * @return ShoppingCart
	 */
	public function getShoppingCartByToken(string $token): ShoppingCart
	{

		$cart = (new Query())
			->select(['sc.*', 'sct.token'])
			->from(['sc' => $this->getShoppingCartTableName()])
			->innerJoin(['sct' => $this->getShoppingCartTokenTableName()], ['and', 'sct.cart_id = sc.id', ['sct.token' => $token]])
			->one();

		if ($cart === null)
			throw new NotFoundException();

		return $this->_populate($cart);
	}

	public function findGoodsByShoppingCartId(int $cartId): ShoppingCartGoodCollectionInterface
	{
		$reader = (new Query())
			->from($this->getShoppingCartItemsTableName())
			->andWhere(['cart_id' => $cartId]);

		$collection = new ShoppingCartGoodCollection();

		foreach ($reader->each(100) as $row)
			$collection[] = $this->_populateGood($row);

		return $collection;
	}

	/**
	 * @param int|int $userId
	 * @return ShoppingCart
	 */
	public function create(ShoppingCartDto $dto): ShoppingCart
	{

		$transaction = \Yii::$app->db->beginTransaction(Transaction::REPEATABLE_READ);
		try {

			if (null === $dto->updated_at)
				$dto->updated_at = new \DateTime();

			/**
			 * @var Connection
			 */
			$db = \Yii::$app->db;

			$db->createCommand()
				->insert($this->getShoppingCartTableName(), [
					'user_id' => $dto->user_id,
					'updated_at' => $dto->updated_at->format("Y-m-d H:i:s"),
				])
				->execute();

			$dto->id = $db->getLastInsertID();

			if (null === $dto->user_id && null !== $dto->token) {

				\Yii::$app->db->createCommand()
					->insert($this->getShoppingCartTokenTableName(), [
						'cart_id' => $dto->id,
						'token' => $dto->token,
					])
					->execute();

			} elseif (null === $dto->user_id)
				throw new ErrorException('Invalid DTO params');


			$transaction->commit();
		} catch (ErrorException $e) {

			$transaction->rollBack();
			throw $e;
		}

		return $this->_populate([
			'id' => $dto->id,
			'user_id' => $dto->user_id,
			'token' => $dto->token,
			'updated_at' => $dto->updated_at->format("Y-m-d H:i:s"),
		]);
	}

}