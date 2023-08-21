<?php

namespace Models;

if (!fileIsIncluded('Model.php'))
	require dirname(__DIR__, 2) . '/Base/Model.php';

use Base\Model;

/**
 * @property $id
 * @property $user_id
 * @property $amount
 * @property $transaction_type
 * @property $created_at
 * @property $updated_at
 */
class UserTransaction extends Model
{
}
