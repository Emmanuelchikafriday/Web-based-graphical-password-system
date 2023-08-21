<?php

namespace Models;

if (!fileIsIncluded('Model.php'))
	require dirname(__DIR__, 2) . '/Base/Model.php';

use Base\Model;

/**
 * @property $all All rows
 * @property $id
 * @property $user_id
 * @property $url
 * @property $fused
 * @property $collector
 * @property $created_at
 * @property $updated_at
 */
class Fusion extends Model
{
	protected string $table = 'fusion';
}
