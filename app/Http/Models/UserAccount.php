<?php

namespace Models;

if (!fileIsIncluded('Model.php'))
	require dirname(__DIR__, 2) . '/Base/Model.php';

use Base\Model;

/**
 * @property $all All rows
 * @property $id
 * @property $user_id
 * @property $account_tier_id
 * @property $account_number
 * @property $account_balance
 * @property $created_at
 * @property $updated_at
 */
class UserAccount extends Model
{
}
