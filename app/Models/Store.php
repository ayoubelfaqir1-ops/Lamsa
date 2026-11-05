<?php

namespace App\Models;

use Modules\Store\Models\Store as ModularStore;

/**
 * Backwards compatibility proxy for Modules\Store\Models\Store.
 *
 * @property float|null $rating
 *
 * @deprecated Use Modules\Store\Models\Store instead.
 */
class Store extends ModularStore {}
