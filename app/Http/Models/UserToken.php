<?php

namespace Models;

if (!fileIsIncluded('Model.php'))
	require dirname(__DIR__, 2) . '/Base/Model.php';

use Base\Model;

class UserToken extends Model
{
}
