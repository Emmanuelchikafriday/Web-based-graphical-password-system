<?php

namespace Models;

if (!fileIsIncluded('Model.php'))
	require dirname(__DIR__, 2) . '/Base/Model.php';

use Base\Model;

/**
 * @property $all All rows
 * @property $id
 * @property $name
 * @property $phone
 * @property $email
 * @property $is_active
 * @property $email_verified_at
 */
class AccountTier extends Model
{
	
}
