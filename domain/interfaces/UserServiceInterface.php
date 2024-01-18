<?php

namespace domain\interfaces;

interface UserServiceInterface extends ServiceInterface
{

	public function getById($id);

	public function getByEmail($email);

	public function getByAccessToken($token);

}