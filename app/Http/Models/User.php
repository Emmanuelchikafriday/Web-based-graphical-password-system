<?php

namespace Models;

if (!fileIsIncluded('Model.php'))
	require dirname(__DIR__, 2) . '/Base/Model.php';
/*require dirname(__DIR__) . '/Models/UserAccount.php';*/

if (!fileIsIncluded('Authenticatable.php'))
	require dirname(__DIR__) . '/interface/Authenticatable.php';

use Authenticatable;
use Base\Model;

/**
 * @property $all All rows
 * @property $id
 * @property $name
 * @property $phone
 * @property $email
 * @property $is_active
 * @property $email_verified_at
 * @property $created_at
 * @property $updated_at
 */
class User extends Model
{
	use Authenticatable;
	
	public function fusion():Fusion
	{
		$FUSION = new Fusion($this->db);
		return $this->relateOne($FUSION, 'user_id', $this->id);
	}
	
	public function user_account():UserAccount
	{
		$USERACCOUNT = new UserAccount($this->db);
		return $this->relateOne($USERACCOUNT, 'user_id', $this->id);
	}
	
	public function transactions($modifiers = NULL):UserTransaction
	{
		$USERTRANSACTION = new UserTransaction($this->db);
		return $this->relateMany($USERTRANSACTION, 'user_id', $this->id, $modifiers);
	}
}
